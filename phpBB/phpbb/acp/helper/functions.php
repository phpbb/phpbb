<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\acp\helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

class functions
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var ContainerInterface */
	protected $container;

	/** @var \phpbb\cp\menu */
	protected $cp_menu;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpBB web root path */
	protected $web_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\config\config				$config			Config object
	 * @param ContainerInterface				$container		Container object
	 * @param \phpbb\cp\menu					$cp_menu		CP Menu object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\controller\helper			$helper			Controller helper object
	 * @param \phpbb\language\language			$language		Language object
	 * @param \phpbb\path_helper				$path_helper	Path helper object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$admin_path		phpBB admin path
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		ContainerInterface $container,
		\phpbb\cp\menu $cp_menu,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\path_helper $path_helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->container	= $container;
		$this->cp_menu		= $cp_menu;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->web_path		= $path_helper->update_web_root_path($root_path);
		$this->php_ext		= $php_ext;
	}

	/**
	 * Page header for ACP pages.
	 *
	 * @param string	$page_title		The page title
	 * @return void
	 */
	public function adm_page_header($page_title)
	{
		if (defined('HEADER_INC'))
		{
			return;
		}

		define('HEADER_INC', true);

		// A listener can set this variable to `true` when it overrides this function
		$adm_page_header_override = false;

		/**
		 * Execute code and/or overwrite adm_page_header()
		 *
		 * @event core.adm_page_header
		 * @var string	page_title					Page title
		 * @var bool	adm_page_header_override	Shall we return instead of running the rest of adm_page_header()
		 * @since 3.1.0-a1
		 */
		$vars = ['page_title', 'adm_page_header_override'];
		extract($this->dispatcher->trigger_event('core.adm_page_header', compact($vars)));

		if ($adm_page_header_override)
		{
			return;
		}

		$this->user->update_session_infos();

		$this->cp_menu->build('acp');

		// gzip_compression
		if ($this->config['gzip_compress'])
		{
			if (@extension_loaded('zlib') && !headers_sent())
			{
				ob_start('ob_gzhandler');
			}
		}

		$phpbb_version_parts = explode('.', PHPBB_VERSION, 3);
		$phpbb_major = $phpbb_version_parts[0] . '.' . $phpbb_version_parts[1];

		$this->template->assign_vars([
			'PAGE_TITLE'			=> $page_title,
			'USERNAME'				=> $this->user->data['username'],

			'ADMIN_ROOT_PATH'		=> $this->web_path . $this->admin_path,
			'ROOT_PATH'				=> $this->web_path,
			'PHPBB_MAJOR'			=> $phpbb_major,
			'PHPBB_VERSION'			=> PHPBB_VERSION,
			'SESSION_ID'			=> $this->user->session_id,

			'ICON_MOVE_UP'				=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_up.gif" alt="' . $this->language->lang('MOVE_UP') . '" title="' . $this->language->lang('MOVE_UP') . '" />',
			'ICON_MOVE_UP_DISABLED'		=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_up_disabled.gif" alt="' . $this->language->lang('MOVE_UP') . '" title="' . $this->language->lang('MOVE_UP') . '" />',
			'ICON_MOVE_DOWN'			=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_down.gif" alt="' . $this->language->lang('MOVE_DOWN') . '" title="' . $this->language->lang('MOVE_DOWN') . '" />',
			'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_down_disabled.gif" alt="' . $this->language->lang('MOVE_DOWN') . '" title="' . $this->language->lang('MOVE_DOWN') . '" />',
			'ICON_EDIT'					=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_edit.gif" alt="' . $this->language->lang('EDIT') . '" title="' . $this->language->lang('EDIT') . '" />',
			'ICON_EDIT_DISABLED'		=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_edit_disabled.gif" alt="' . $this->language->lang('EDIT') . '" title="' . $this->language->lang('EDIT') . '" />',
			'ICON_DELETE'				=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_delete.gif" alt="' . $this->language->lang('DELETE') . '" title="' . $this->language->lang('DELETE') . '" />',
			'ICON_DELETE_DISABLED'		=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_delete_disabled.gif" alt="' . $this->language->lang('DELETE') . '" title="' . $this->language->lang('DELETE') . '" />',
			'ICON_SYNC'					=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_sync.gif" alt="' . $this->language->lang('RESYNC') . '" title="' . $this->language->lang('RESYNC') . '" />',
			'ICON_SYNC_DISABLED'		=> '<img src="' . htmlspecialchars($this->web_path . $this->admin_path) . 'images/icon_sync_disabled.gif" alt="' . $this->language->lang('RESYNC') . '" title="' . $this->language->lang('RESYNC') . '" />',

			'S_USER_LANG'			=> $this->language->lang('USER_LANG'),
			'S_CONTENT_DIRECTION'	=> $this->language->lang('DIRECTION'),
			'S_CONTENT_ENCODING'	=> 'UTF-8',
			'S_CONTENT_FLOW_BEGIN'	=> $this->language->lang('DIRECTION') === 'ltr' ? 'left' : 'right',
			'S_CONTENT_FLOW_END'	=> $this->language->lang('DIRECTION') === 'ltr' ? 'right' : 'left',

			'T_ASSETS_VERSION'		=> $this->config['assets_version'],
			'T_IMAGES_PATH'			=> "{$this->web_path}images/",
			'T_AVATAR_GALLERY_PATH'	=> "{$this->web_path}{$this->config['avatar_gallery_path']}/",
			'T_ICONS_PATH'			=> "{$this->web_path}{$this->config['icons_path']}/",
			'T_RANKS_PATH'			=> "{$this->web_path}{$this->config['ranks_path']}/",
			'T_SMILIES_PATH'		=> "{$this->web_path}{$this->config['smilies_path']}/",
			'T_FONT_AWESOME_LINK'	=> !empty($this->config['allow_cdn']) && !empty($this->config['load_font_awesome_url']) ? $this->config['load_font_awesome_url'] : "{$this->web_path}assets/css/font-awesome.min.css?assets_version=" . $this->config['assets_version'],

			'U_LOGOUT'				=> $this->helper->route('ucp_account', ['mode' => 'logout']),
			'U_ADM_LOGOUT'			=> $this->helper->route('acp_index', ['action' => 'admlogout']),
			'U_ADM_INDEX'			=> $this->helper->route('acp_index'),
			'U_INDEX'				=> append_sid("{$this->web_path}index.$this->php_ext"),

			'CONTAINER_EXCEPTION'	=> $this->container->hasParameter('container_exception') ? $this->container->getParameter('container_exception') : false,
		]);

		// An array of http headers that phpBB will set. The following event may override these.
		$http_headers = [
			// application/xhtml+xml not used because of IE
			'Content-type'		=> 'text/html; charset=UTF-8',
			'Cache-Control'		=> 'private, no-cache="set-cookie"',
			'Expires'			=> gmdate('D, d M Y H:i:s', time()) . ' GMT',
			'Referrer-Policy'	=> 'strict-origin-when-cross-origin',
		];

		/**
		 * Execute code and/or overwrite _common_ template variables after they have been assigned.
		 *
		 * @event core.adm_page_header_after
		 * @var	string	page_title			Page title
		 * @var	array	http_headers		HTTP headers that should be set by phpbb
		 * @since 3.1.0-RC3
		 */
		$vars = ['page_title', 'http_headers'];
		extract($this->dispatcher->trigger_event('core.adm_page_header_after', compact($vars)));

		foreach ($http_headers as $header_name => $header_value)
		{
			header((string) $header_name . ': ' . (string) $header_value);
		}

		return;
	}

	/**
	 * Page footer for ACP pages.
	 *
	 * @param bool		$copyright_html		Whether or not the copyright should be included
	 * @return void
	 */
	public function adm_page_footer($copyright_html = true)
	{
		// A listener can set this variable to `true` when it overrides this function
		$adm_page_footer_override = false;

		/**
		 * Execute code and/or overwrite adm_page_footer()
		 *
		 * @event core.adm_page_footer
		 * @var bool	copyright_html				Shall we display the copyright?
		 * @var bool	adm_page_footer_override	Shall we return instead of running the rest of adm_page_footer()
		 * @since 3.1.0-a1
		 */
		$vars = ['copyright_html', 'adm_page_footer_override'];
		extract($this->dispatcher->trigger_event('core.adm_page_footer', compact($vars)));

		if ($adm_page_footer_override)
		{
			return;
		}

		phpbb_check_and_display_sql_report($this->request, $this->auth, $this->db);

		$this->template->assign_vars([
			'CREDIT_LINE'		=> $this->language->lang('POWERED_BY', '<a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited'),
			'DEBUG_OUTPUT'		=> phpbb_generate_debug_output($this->db, $this->config, $this->auth, $this->user, $this->dispatcher),
			'TRANSLATION_INFO'	=> $this->language->lang('TRANSLATION_INFO'),
			'VERSION'			=> $this->config['version'],

			'S_COPYRIGHT_HTML'	=> $copyright_html,
			'S_ALLOW_CDN'		=> !empty($this->config['allow_cdn']),

			'T_JQUERY_LINK'		=> !empty($this->config['allow_cdn']) && !empty($this->config['load_jquery_url']) ? $this->config['load_jquery_url'] : "{$this->web_path}assets/javascript/jquery-3.4.1.min.js",
		]);

		$this->template->display('body');

		garbage_collection();
		exit_handler();
	}

	/**
	 * Generate a back link to be appended to a message.
	 *
	 * @param string	$link		The link back to the previous page
	 * @return string
	 */
	public function adm_back_link($link)
	{
		return '<br /><br /><a href="' . $link . '">&laquo; ' . $this->language->lang('BACK_TO_PREV') . '</a>';
	}
}
