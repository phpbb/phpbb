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

namespace phpbb\acp\functions;

use Symfony\Component\DependencyInjection\ContainerInterface;

class acp
{
	protected $auth;
	protected $config;
	protected $container;
	protected $db;
	protected $dispatcher;
	protected $helper;
	protected $lang;
	protected $request;
	protected $template;
	protected $user;

	protected $admin_path;
	protected $root_path;
	protected $php_ext;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		ContainerInterface $container,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\path_helper $path_helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->container	= $container;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $path_helper->update_web_root_path($path_helper->get_phpbb_root_path());
		$this->admin_path	= $this->root_path . $path_helper->get_adm_relative_path();
		$this->php_ext		= $path_helper->get_php_ext();
	}

	public function adm_back_link($link)
	{
		return '<br /><br /><a href="' . $link . '">&laquo; ' . $this->lang->lang('BACK_TO_PREV') . '</a>';
	}

	public function adm_page_header($page_title)
	{
		global $SID, $_SID;

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
		 * @var	string	page_title					Page title
		 * @var	bool	adm_page_header_override	Shall we return instead of
		 *											running the rest of adm_page_header()
		 * @since 3.1.0-a1
		 */
		$vars = array('page_title', 'adm_page_header_override');
		extract($this->dispatcher->trigger_event('core.adm_page_header', compact($vars)));

		if ($adm_page_header_override)
		{
			return;
		}

		$this->user->update_session_infos();

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

		$this->template->assign_vars(array(
			'PAGE_TITLE'			=> $page_title,
			'USERNAME'				=> $this->user->data['username'],

			'SID'					=> $SID,
			'_SID'					=> $_SID,
			'SESSION_ID'			=> $this->user->session_id,
			'ROOT_PATH'				=> $this->root_path,
			'ADMIN_ROOT_PATH'		=> $this->admin_path,
			'PHPBB_VERSION'			=> PHPBB_VERSION,
			'PHPBB_MAJOR'			=> $phpbb_major,

			'U_LOGOUT'				=> append_sid("{$this->root_path}ucp.{$this->php_ext}", 'mode=logout'),
			'U_ADM_LOGOUT'			=> $this->helper->route('phpbb_acp_index', array('action' => 'admlogout')),
			'U_ADM_INDEX'			=> $this->helper->route('phpbb_acp_index'),
			'U_INDEX'				=> append_sid("{$this->root_path}index.{$this->php_ext}"),

			'T_IMAGES_PATH'			=> "{$this->root_path}images/",
			'T_SMILIES_PATH'		=> "{$this->root_path}{$this->config['smilies_path']}/",
			'T_AVATAR_GALLERY_PATH'	=> "{$this->root_path}{$this->config['avatar_gallery_path']}/",
			'T_ICONS_PATH'			=> "{$this->root_path}{$this->config['icons_path']}/",
			'T_RANKS_PATH'			=> "{$this->root_path}{$this->config['ranks_path']}/",
			'T_FONT_AWESOME_LINK'	=> !empty($this->config['allow_cdn']) && !empty($this->config['load_font_awesome_url']) ? $this->config['load_font_awesome_url'] : "{$this->root_path}assets/css/font-awesome.min.css?assets_version=" . $this->config['assets_version'],

			'T_ASSETS_VERSION'		=> $this->config['assets_version'],

			'ICON_MOVE_UP'				=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_up.gif" alt="' . $this->lang->lang('MOVE_UP') . '" title="' . $this->lang->lang('MOVE_UP') . '" />',
			'ICON_MOVE_UP_DISABLED'		=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_up_disabled.gif" alt="' . $this->lang->lang('MOVE_UP') . '" title="' . $this->lang->lang('MOVE_UP') . '" />',
			'ICON_MOVE_DOWN'			=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_down.gif" alt="' . $this->lang->lang('MOVE_DOWN') . '" title="' . $this->lang->lang('MOVE_DOWN') . '" />',
			'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_down_disabled.gif" alt="' . $this->lang->lang('MOVE_DOWN') . '" title="' . $this->lang->lang('MOVE_DOWN') . '" />',
			'ICON_EDIT'					=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_edit.gif" alt="' . $this->lang->lang('EDIT') . '" title="' . $this->lang->lang('EDIT') . '" />',
			'ICON_EDIT_DISABLED'		=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_edit_disabled.gif" alt="' . $this->lang->lang('EDIT') . '" title="' . $this->lang->lang('EDIT') . '" />',
			'ICON_DELETE'				=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_delete.gif" alt="' . $this->lang->lang('DELETE') . '" title="' . $this->lang->lang('DELETE') . '" />',
			'ICON_DELETE_DISABLED'		=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_delete_disabled.gif" alt="' . $this->lang->lang('DELETE') . '" title="' . $this->lang->lang('DELETE') . '" />',
			'ICON_SYNC'					=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_sync.gif" alt="' . $this->lang->lang('RESYNC') . '" title="' . $this->lang->lang('RESYNC') . '" />',
			'ICON_SYNC_DISABLED'		=> '<img src="' . htmlspecialchars($this->admin_path) . 'images/icon_sync_disabled.gif" alt="' . $this->lang->lang('RESYNC') . '" title="' . $this->lang->lang('RESYNC') . '" />',

			'S_USER_LANG'			=> $this->lang->lang('USER_LANG'),
			'S_CONTENT_DIRECTION'	=> $this->lang->lang('DIRECTION'),
			'S_CONTENT_ENCODING'	=> 'UTF-8',
			'S_CONTENT_FLOW_BEGIN'	=> ($this->lang->lang('DIRECTION') == 'ltr') ? 'left' : 'right',
			'S_CONTENT_FLOW_END'	=> ($this->lang->lang('DIRECTION') == 'ltr') ? 'right' : 'left',

			'CONTAINER_EXCEPTION'	=> $this->container->hasParameter('container_exception') ? $this->container->getParameter('container_exception') : false,
		));

		// An array of http headers that phpbb will set. The following event may override these.
		$http_headers = array(
			// application/xhtml+xml not used because of IE
			'Content-type' => 'text/html; charset=UTF-8',
			'Cache-Control' => 'private, no-cache="set-cookie"',
			'Expires' => gmdate('D, d M Y H:i:s', time()) . ' GMT',
		);

		/**
		 * Execute code and/or overwrite _common_ template variables after they have been assigned.
		 *
		 * @event core.adm_page_header_after
		 * @var	string	page_title			Page title
		 * @var	array	http_headers			HTTP headers that should be set by phpbb
		 *
		 * @since 3.1.0-RC3
		 */
		$vars = array('page_title', 'http_headers');
		extract($this->dispatcher->trigger_event('core.adm_page_header_after', compact($vars)));

		foreach ($http_headers as $header_name => $header_value)
		{
			header((string) $header_name . ': ' . (string) $header_value);
		}

		return;
	}

	public function adm_page_footer($copyright_html = true)
	{
		// A listener can set this variable to `true` when it overrides this function
		$adm_page_footer_override = false;

		/**
		 * Execute code and/or overwrite adm_page_footer()
		 *
		 * @event core.adm_page_footer
		 * @var	bool	copyright_html				Shall we display the copyright?
		 * @var	bool	adm_page_footer_override	Shall we return instead of
		 *											running the rest of adm_page_footer()
		 * @since 3.1.0-a1
		 */
		$vars = array('copyright_html', 'adm_page_footer_override');
		extract($this->dispatcher->trigger_event('core.adm_page_footer', compact($vars)));

		if ($adm_page_footer_override)
		{
			return;
		}

		phpbb_check_and_display_sql_report($this->request, $this->auth, $this->db);

		$this->template->assign_vars(array(
				'DEBUG_OUTPUT'		=> phpbb_generate_debug_output($this->db, $this->config, $this->auth, $this->user, $this->dispatcher),
				'TRANSLATION_INFO'	=> $this->lang->is_set('TRANSLATION_INFO') ? $this->lang->lang('TRANSLATION_INFO') : '',
				'S_COPYRIGHT_HTML'	=> $copyright_html,
				'CREDIT_LINE'		=> $this->lang->lang('POWERED_BY', '<a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited'),
				'T_JQUERY_LINK'		=> !empty($this->config['allow_cdn']) && !empty($this->config['load_jquery_url']) ? $this->config['load_jquery_url'] : "{$this->root_path}assets/javascript/jquery.min.js",
				'S_ALLOW_CDN'		=> !empty($this->config['allow_cdn']),
				'VERSION'			=> $this->config['version'])
		);

		$this->template->display('body');

		garbage_collection();
		exit_handler();
	}
}
