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

/**
 * @todo add cron intervals to server settings? (database_gc, queue_interval, session_gc, search_gc, cache_gc, warnings_gc)
 */

namespace phpbb\acp\controller;

use phpbb\cache\driver\driver_interface as cache;
use phpbb\config\config;
use Symfony\Component\DependencyInjection\ContainerInterface;
use phpbb\cp\manager as cp_manager;
use phpbb\db\driver\driver_interface as db;
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\path_helper;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

/**
 * ACP Controller: Settings
 */
class settings
{
	/** @var cache */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var array */
	protected $new_config = array();

	/** @var ContainerInterface */
	protected $container;

	/** @var cp_manager */
	protected $cp_manager;

	/** @var db */
	protected $db;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var helper */
	protected $helper;

	/** @var language */
	protected $lang;

	/** @var log */
	protected $log;

	/** @var path_helper */
	protected $path_helper;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param cache					$cache			Cache object
	 * @param config				$config			Config object
	 * @param ContainerInterface	$container		Service container object
	 * @param cp_manager			$cp_manager		Control panel manager object
	 * @param db					$db				Database object
	 * @param dispatcher			$dispatcher		Event dispatcher object
	 * @param helper				$helper			Controller helper object
	 * @param language				$lang			Language object
	 * @param log					$log			Log object
	 * @param path_helper			$path_helper	Path helper object
	 * @param request				$request		Request object
	 * @param template				$template		Template object
	 * @param user					$user			User object
	 */
	public function __construct(
		cache $cache,
		config $config,
		ContainerInterface $container,
		cp_manager $cp_manager,
		db $db,
		dispatcher $dispatcher,
		helper $helper,
		language $lang,
		log $log,
		path_helper $path_helper,
		request $request,
		template $template,
		user $user
	)
	{
		$this->cache		= $cache;
		$this->config		= $config;
		$this->container	= $container;
		$this->cp_manager	= $cp_manager;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->path_helper	= $path_helper;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $path_helper->update_web_root_path($path_helper->get_phpbb_root_path());
		$this->php_ext		= $path_helper->get_php_ext();
	}

	public function handle($mode)
	{
		switch ($mode)
		{
			case 'message':
				$active = 'pm';
			break;
			case 'settings':
				$active = 'board';
			break;
			default:
				$active = $mode;
			break;
		}

		$this->cp_manager->build('acp', 'phpbb.acp.info.settings.' . $active);

		$this->lang->add_lang('acp/board');

		$submit = (isset($_POST['submit']) || isset($_POST['allow_quick_reply_enable'])) ? true : false;

		$form_key = 'acp_board';
		add_form_key($form_key);

		$display_vars = array();

		/**
		 *	Validation types are:
		 *		string, int, bool,
		 *		script_path (absolute path in url - beginning with / and no trailing slash),
		 *		rpath (relative), rwpath (relative, writable), path (relative path, but able to escape the root), wpath (writable)
		 */
		switch ($mode)
		{
			case 'settings':
				$display_vars = array(
					'title'	=> 'ACP_BOARD_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_BOARD_SETTINGS',
						'sitename'				=> array('lang' => 'SITE_NAME',				'validate' => 'string',	'type' => 'text:40:255', 'explain' => false),
						'site_desc'				=> array('lang' => 'SITE_DESC',				'validate' => 'string',	'type' => 'text:40:255', 'explain' => false),
						'site_home_url'			=> array('lang' => 'SITE_HOME_URL',			'validate' => 'string',	'type' => 'url:40:255', 'explain' => true),
						'site_home_text'		=> array('lang' => 'SITE_HOME_TEXT',		'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),
						'board_index_text'		=> array('lang' => 'BOARD_INDEX_TEXT',		'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),
						'board_disable'			=> array('lang' => 'DISABLE_BOARD',			'validate' => 'bool',	'type' => 'custom', 'function' => array($this, 'board_disable'), 'explain' => true),
						'board_disable_msg'		=> false,
						'default_lang'			=> array('lang' => 'DEFAULT_LANGUAGE',		'validate' => 'lang',	'type' => 'select', 'function' => 'language_select', 'params' => array('{CONFIG_VALUE}'), 'explain' => false),
						'default_dateformat'	=> array('lang' => 'DEFAULT_DATE_FORMAT',	'validate' => 'string',	'type' => 'custom', 'function' => array($this, 'dateformat_select'), 'explain' => true),
						'board_timezone'		=> array('lang' => 'SYSTEM_TIMEZONE',		'validate' => 'timezone',	'type' => 'custom', 'function' => array($this, 'timezone_select'), 'explain' => true),

						'legend2'				=> 'BOARD_STYLE',
						'default_style'			=> array('lang' => 'DEFAULT_STYLE',			'validate' => 'int',	'type' => 'select', 'function' => 'style_select', 'params' => array('{CONFIG_VALUE}', false), 'explain' => true),
						'guest_style'			=> array('lang' => 'GUEST_STYLE',			'validate' => 'int',	'type' => 'select', 'function' => 'style_select', 'params' => array($this->guest_style_get(), false), 'explain' => true),
						'override_user_style'	=> array('lang' => 'OVERRIDE_STYLE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend3'				=> 'WARNINGS',
						'warnings_expire_days'	=> array('lang' => 'WARNINGS_EXPIRE',		'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true, 'append' => ' ' . $this->lang->lang('DAYS')),

						'legend4'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'features':
				$display_vars = array(
					'title'	=> 'ACP_BOARD_FEATURES',
					'vars'	=> array(
						'legend1'						=> 'ACP_BOARD_FEATURES',
						'allow_privmsg'					=> array('lang' => 'BOARD_PM',						'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_topic_notify'			=> array('lang' => 'ALLOW_TOPIC_NOTIFY',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_forum_notify'			=> array('lang' => 'ALLOW_FORUM_NOTIFY',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_namechange'				=> array('lang' => 'ALLOW_NAME_CHANGE',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_attachments'				=> array('lang' => 'ALLOW_ATTACHMENTS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_pm_attach'				=> array('lang' => 'ALLOW_PM_ATTACHMENTS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_pm_report'				=> array('lang' => 'ALLOW_PM_REPORT',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_bbcode'					=> array('lang' => 'ALLOW_BBCODE',					'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_smilies'					=> array('lang' => 'ALLOW_SMILIES',					'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig'						=> array('lang' => 'ALLOW_SIG',						'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_board_notifications'		=> array('lang' => 'ALLOW_BOARD_NOTIFICATIONS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_nocensors'				=> array('lang' => 'ALLOW_NO_CENSORS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_bookmarks'				=> array('lang' => 'ALLOW_BOOKMARKS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_birthdays'				=> array('lang' => 'ALLOW_BIRTHDAYS',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'display_last_subject'			=> array('lang' => 'DISPLAY_LAST_SUBJECT',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_quick_reply'				=> array('lang' => 'ALLOW_QUICK_REPLY',				'validate' => 'bool',	'type' => 'custom', 'function' => array($this, 'quick_reply'), 'explain' => true),

						'legend2'							=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'avatar':
				/* @var $phpbb_avatar_manager \phpbb\avatar\manager */
				$phpbb_avatar_manager = $this->container->get('avatar.manager');
				$avatar_drivers = $phpbb_avatar_manager->get_all_drivers();

				$avatar_vars = array();
				foreach ($avatar_drivers as $current_driver)
				{
					$driver = $phpbb_avatar_manager->get_driver($current_driver, false);

					/*
					* First grab the settings for enabling/disabling the avatar
					* driver and afterwards grab additional settings the driver
					* might have.
					*/
					$avatar_vars += $phpbb_avatar_manager->get_avatar_settings($driver);
					$avatar_vars += $driver->prepare_form_acp($this->user);
				}

				$display_vars = array(
					'title'	=> 'ACP_AVATAR_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_AVATAR_SETTINGS',

						'avatar_min_width'		=> array('lang' => 'MIN_AVATAR_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false),
						'avatar_min_height'		=> array('lang' => 'MIN_AVATAR_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false),
						'avatar_max_width'		=> array('lang' => 'MAX_AVATAR_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false),
						'avatar_max_height'		=> array('lang' => 'MAX_AVATAR_SIZE', 'validate' => 'int:0', 'type' => false, 'method' => false, 'explain' => false),

						'allow_avatar'			=> array('lang' => 'ALLOW_AVATARS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'avatar_min'			=> array('lang' => 'MIN_AVATAR_SIZE',		'validate' => 'int:0',	'type' => 'dimension:0', 'explain' => true, 'append' => ' ' . $this->lang->lang('PIXEL')),
						'avatar_max'			=> array('lang' => 'MAX_AVATAR_SIZE',		'validate' => 'int:0',	'type' => 'dimension:0', 'explain' => true, 'append' => ' ' . $this->lang->lang('PIXEL')),
					)
				);

				if (!empty($avatar_vars))
				{
					$display_vars['vars'] += $avatar_vars;
				}
			break;

			case 'message':
				$display_vars = array(
					'title'	=> 'ACP_MESSAGE_SETTINGS',
					'lang'	=> 'ucp',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'allow_privmsg'			=> array('lang' => 'BOARD_PM',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'pm_max_boxes'			=> array('lang' => 'BOXES_MAX',				'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true),
						'pm_max_msgs'			=> array('lang' => 'BOXES_LIMIT',			'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true),
						'full_folder_action'	=> array('lang' => 'FULL_FOLDER_ACTION',	'validate' => 'int',	'type' => 'select', 'function' => array($this, 'full_folder_select'), 'explain' => true),
						'pm_edit_time'			=> array('lang' => 'PM_EDIT_TIME',			'validate' => 'int:0:99999',	'type' => 'number:0:99999', 'explain' => true, 'append' => ' ' . $this->lang->lang('MINUTES')),
						'pm_max_recipients'		=> array('lang' => 'PM_MAX_RECIPIENTS',		'validate' => 'int:0:99999',	'type' => 'number:0:99999', 'explain' => true),

						'legend2'				=> 'GENERAL_OPTIONS',
						'allow_mass_pm'			=> array('lang' => 'ALLOW_MASS_PM',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_bbcode_pm'		=> array('lang' => 'ALLOW_BBCODE_PM',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_smilies_pm'		=> array('lang' => 'ALLOW_SMILIES_PM',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_pm_attach'		=> array('lang' => 'ALLOW_PM_ATTACHMENTS',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_pm'			=> array('lang' => 'ALLOW_SIG_PM',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'print_pm'				=> array('lang' => 'ALLOW_PRINT_PM',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'forward_pm'			=> array('lang' => 'ALLOW_FORWARD_PM',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_img_pm'			=> array('lang' => 'ALLOW_IMG_PM',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_flash_pm'			=> array('lang' => 'ALLOW_FLASH_PM',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'enable_pm_icons'		=> array('lang' => 'ENABLE_PM_ICONS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend3'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'post':
				$display_vars = array(
					'title'	=> 'ACP_POST_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_OPTIONS',
						'allow_topic_notify'	=> array('lang' => 'ALLOW_TOPIC_NOTIFY',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_forum_notify'	=> array('lang' => 'ALLOW_FORUM_NOTIFY',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_bbcode'			=> array('lang' => 'ALLOW_BBCODE',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_post_flash'		=> array('lang' => 'ALLOW_POST_FLASH',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_smilies'			=> array('lang' => 'ALLOW_SMILIES',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_post_links'		=> array('lang' => 'ALLOW_POST_LINKS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allowed_schemes_links'	=> array('lang' => 'ALLOWED_SCHEMES_LINKS',	'validate' => 'string',	'type' => 'text:0:255', 'explain' => true),
						'allow_nocensors'		=> array('lang' => 'ALLOW_NO_CENSORS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_bookmarks'		=> array('lang' => 'ALLOW_BOOKMARKS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'enable_post_confirm'	=> array('lang' => 'VISUAL_CONFIRM_POST',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_quick_reply'		=> array('lang' => 'ALLOW_QUICK_REPLY',		'validate' => 'bool',	'type' => 'custom', 'function' => array($this, 'quick_reply'), 'explain' => true),

						'legend2'				=> 'POSTING',
						'bump_type'				=> false,
						'edit_time'				=> array('lang' => 'EDIT_TIME',				'validate' => 'int:0:99999',		'type' => 'number:0:99999', 'explain' => true, 'append' => ' ' . $this->lang->lang('MINUTES')),
						'delete_time'			=> array('lang' => 'DELETE_TIME',			'validate' => 'int:0:99999',		'type' => 'number:0:99999', 'explain' => true, 'append' => ' ' . $this->lang->lang('MINUTES')),
						'display_last_edited'	=> array('lang' => 'DISPLAY_LAST_EDITED',	'validate' => 'bool',		'type' => 'radio:yes_no', 'explain' => true),
						'flood_interval'		=> array('lang' => 'FLOOD_INTERVAL',		'validate' => 'int:0:9999999999',		'type' => 'number:0:9999999999', 'explain' => true, 'append' => ' ' . $this->lang->lang('SECONDS')),
						'bump_interval'			=> array('lang' => 'BUMP_INTERVAL',			'validate' => 'int:0',		'type' => 'custom', 'function' => array($this, 'bump_interval'), 'explain' => true),
						'topics_per_page'		=> array('lang' => 'TOPICS_PER_PAGE',		'validate' => 'int:1:9999',		'type' => 'number:1:9999', 'explain' => false),
						'posts_per_page'		=> array('lang' => 'POSTS_PER_PAGE',		'validate' => 'int:1:9999',		'type' => 'number:1:9999', 'explain' => false),
						'smilies_per_page'		=> array('lang' => 'SMILIES_PER_PAGE',		'validate' => 'int:1:9999',		'type' => 'number:1:9999', 'explain' => false),
						'hot_threshold'			=> array('lang' => 'HOT_THRESHOLD',			'validate' => 'int:0:9999',		'type' => 'number:0:9999', 'explain' => true),
						'max_poll_options'		=> array('lang' => 'MAX_POLL_OPTIONS',		'validate' => 'int:2:127',	'type' => 'number:2:127', 'explain' => false),
						'max_post_chars'		=> array('lang' => 'CHAR_LIMIT',			'validate' => 'int:0:999999',		'type' => 'number:0:999999', 'explain' => true),
						'min_post_chars'		=> array('lang' => 'MIN_CHAR_LIMIT',		'validate' => 'int:1:999999',		'type' => 'number:1:999999', 'explain' => true),
						'max_post_smilies'		=> array('lang' => 'SMILIES_LIMIT',			'validate' => 'int:0:9999',		'type' => 'number:0:9999', 'explain' => true),
						'max_post_urls'			=> array('lang' => 'MAX_POST_URLS',			'validate' => 'int:0:9999',		'type' => 'number:0:9999', 'explain' => true),
						'max_post_font_size'	=> array('lang' => 'MAX_POST_FONT_SIZE',	'validate' => 'int:0:9999',		'type' => 'number:0:9999', 'explain' => true, 'append' => ' %'),
						'max_quote_depth'		=> array('lang' => 'QUOTE_DEPTH_LIMIT',		'validate' => 'int:0:9999',		'type' => 'number:0:9999', 'explain' => true),
						'max_post_img_width'	=> array('lang' => 'MAX_POST_IMG_WIDTH',	'validate' => 'int:0:9999',		'type' => 'number:0:9999', 'explain' => true, 'append' => ' ' . $this->lang->lang('PIXEL')),
						'max_post_img_height'	=> array('lang' => 'MAX_POST_IMG_HEIGHT',	'validate' => 'int:0:9999',		'type' => 'number:0:9999', 'explain' => true, 'append' => ' ' . $this->lang->lang('PIXEL')),

						'legend3'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'signature':
				$display_vars = array(
					'title'	=> 'ACP_SIGNATURE_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_OPTIONS',
						'allow_sig'				=> array('lang' => 'ALLOW_SIG',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_bbcode'		=> array('lang' => 'ALLOW_SIG_BBCODE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_img'			=> array('lang' => 'ALLOW_SIG_IMG',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_flash'		=> array('lang' => 'ALLOW_SIG_FLASH',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_smilies'		=> array('lang' => 'ALLOW_SIG_SMILIES',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_links'		=> array('lang' => 'ALLOW_SIG_LINKS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend2'				=> 'GENERAL_SETTINGS',
						'max_sig_chars'			=> array('lang' => 'MAX_SIG_LENGTH',		'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true),
						'max_sig_urls'			=> array('lang' => 'MAX_SIG_URLS',			'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true),
						'max_sig_font_size'		=> array('lang' => 'MAX_SIG_FONT_SIZE',		'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true, 'append' => ' %'),
						'max_sig_smilies'		=> array('lang' => 'MAX_SIG_SMILIES',		'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true),
						'max_sig_img_width'		=> array('lang' => 'MAX_SIG_IMG_WIDTH',		'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true, 'append' => ' ' . $this->lang->lang('PIXEL')),
						'max_sig_img_height'	=> array('lang' => 'MAX_SIG_IMG_HEIGHT',	'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true, 'append' => ' ' . $this->lang->lang('PIXEL')),

						'legend3'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'registration':
				$display_vars = array(
					'title'	=> 'ACP_REGISTER_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'max_name_chars'		=> array('lang' => 'USERNAME_LENGTH', 'validate' => 'int:8:180', 'type' => false, 'method' => false, 'explain' => false,),
						'max_pass_chars'		=> array('lang' => 'PASSWORD_LENGTH', 'validate' => 'int:8:255', 'type' => false, 'method' => false, 'explain' => false,),

						'require_activation'	=> array('lang' => 'ACC_ACTIVATION',	'validate' => 'int',	'type' => 'select', 'function' => array($this, 'select_acc_activation'), 'explain' => true),
						'new_member_post_limit'	=> array('lang' => 'NEW_MEMBER_POST_LIMIT', 'validate' => 'int:0:255', 'type' => 'number:0:255', 'explain' => true, 'append' => ' ' . $this->lang->lang('POSTS')),
						'new_member_group_default'=> array('lang' => 'NEW_MEMBER_GROUP_DEFAULT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'min_name_chars'		=> array('lang' => 'USERNAME_LENGTH',	'validate' => 'int:1',	'type' => 'custom:5:180', 'function' => array($this, 'username_length'), 'explain' => true),
						'min_pass_chars'		=> array('lang' => 'PASSWORD_LENGTH',	'validate' => 'int:1',	'type' => 'custom', 'function' => array($this, 'password_length'), 'explain' => true),
						'allow_name_chars'		=> array('lang' => 'USERNAME_CHARS',	'validate' => 'string',	'type' => 'select', 'function' => array($this, 'select_username_chars'), 'explain' => true),
						'pass_complex'			=> array('lang' => 'PASSWORD_TYPE',		'validate' => 'string',	'type' => 'select', 'function' => array($this, 'select_password_chars'), 'explain' => true),
						'chg_passforce'			=> array('lang' => 'FORCE_PASS_CHANGE',	'validate' => 'int:0:999',	'type' => 'number:0:999', 'explain' => true, 'append' => ' ' . $this->lang->lang('DAYS')),

						'legend2'				=> 'GENERAL_OPTIONS',
						'allow_namechange'		=> array('lang' => 'ALLOW_NAME_CHANGE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_emailreuse'		=> array('lang' => 'ALLOW_EMAIL_REUSE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'enable_confirm'		=> array('lang' => 'VISUAL_CONFIRM_REG',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'max_login_attempts'	=> array('lang' => 'MAX_LOGIN_ATTEMPTS',	'validate' => 'int:0:999',	'type' => 'number:0:999', 'explain' => true),
						'max_reg_attempts'		=> array('lang' => 'REG_LIMIT',				'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true),

						'legend3'			=> 'COPPA',
						'coppa_enable'		=> array('lang' => 'ENABLE_COPPA',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'coppa_mail'		=> array('lang' => 'COPPA_MAIL',		'validate' => 'string',	'type' => 'textarea:5:40', 'explain' => true),
						'coppa_fax'			=> array('lang' => 'COPPA_FAX',			'validate' => 'string',	'type' => 'text:25:100', 'explain' => false),

						'legend4'			=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'feed':
				$display_vars = array(
					'title'	=> 'ACP_FEED_MANAGEMENT',
					'vars'	=> array(
						'legend1'					=> 'ACP_FEED_GENERAL',
						'feed_enable'				=> array('lang' => 'ACP_FEED_ENABLE',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_item_statistics'		=> array('lang' => 'ACP_FEED_ITEM_STATISTICS',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true),
						'feed_http_auth'			=> array('lang' => 'ACP_FEED_HTTP_AUTH',			'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true),

						'legend2'					=> 'ACP_FEED_POST_BASED',
						'feed_limit_post'			=> array('lang' => 'ACP_FEED_LIMIT',				'validate' => 'int:5:9999',	'type' => 'number:5:9999',				'explain' => true),
						'feed_overall'				=> array('lang' => 'ACP_FEED_OVERALL',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_forum'				=> array('lang' => 'ACP_FEED_FORUM',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_topic'				=> array('lang' => 'ACP_FEED_TOPIC',				'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),

						'legend3'					=> 'ACP_FEED_TOPIC_BASED',
						'feed_limit_topic'			=> array('lang' => 'ACP_FEED_LIMIT',				'validate' => 'int:5:9999',	'type' => 'number:5:9999',				'explain' => true),
						'feed_topics_new'			=> array('lang' => 'ACP_FEED_TOPICS_NEW',			'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_topics_active'		=> array('lang' => 'ACP_FEED_TOPICS_ACTIVE',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_news_id'				=> array('lang' => 'ACP_FEED_NEWS',					'validate' => 'string',	'type' => 'custom', 'function' => array($this, 'select_news_forums'), 'explain' => true),

						'legend4'					=> 'ACP_FEED_SETTINGS_OTHER',
						'feed_overall_forums'		=> array('lang'	=> 'ACP_FEED_OVERALL_FORUMS',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => true ),
						'feed_exclude_id'			=> array('lang' => 'ACP_FEED_EXCLUDE_ID',			'validate' => 'string',	'type' => 'custom', 'function' => array($this, 'select_exclude_forums'), 'explain' => true),
					)
				);
			break;

			case 'cookie':
				$display_vars = array(
					'title'	=> 'ACP_COOKIE_SETTINGS',
					'vars'	=> array(
						'legend1'		=> 'ACP_COOKIE_SETTINGS',
						'cookie_domain'	=> array('lang' => 'COOKIE_DOMAIN',	'validate' => 'string',	'type' => 'text::255', 'explain' => true),
						'cookie_name'	=> array('lang' => 'COOKIE_NAME',	'validate' => 'string',	'type' => 'text::16', 'explain' => true),
						'cookie_path'	=> array('lang'	=> 'COOKIE_PATH',	'validate' => 'string',	'type' => 'text::255', 'explain' => true),
						'cookie_secure'	=> array('lang' => 'COOKIE_SECURE',	'validate' => 'bool',	'type' => 'radio:enabled_disabled', 'explain' => true),
						'cookie_notice'	=> array('lang' => 'COOKIE_NOTICE',	'validate' => 'bool',	'type' => 'radio:enabled_disabled', 'explain' => true),
					)
				);
			break;

			case 'load':
				$display_vars = array(
					'title'	=> 'ACP_LOAD_SETTINGS',
					'vars'	=> array(
						'legend1'			=> 'GENERAL_SETTINGS',
						'limit_load'		=> array('lang' => 'LIMIT_LOAD',		'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true),
						'session_length'	=> array('lang' => 'SESSION_LENGTH',	'validate' => 'int:60:9999999999',	'type' => 'number:60:9999999999', 'explain' => true, 'append' => ' ' . $this->lang->lang('SECONDS')),
						'active_sessions'	=> array('lang' => 'LIMIT_SESSIONS',	'validate' => 'int:0:9999',	'type' => 'number:0:9999', 'explain' => true),
						'load_online_time'	=> array('lang' => 'ONLINE_LENGTH',		'validate' => 'int:0:999',	'type' => 'number:0:999', 'explain' => true, 'append' => ' ' . $this->lang->lang('MINUTES')),
						'read_notification_expire_days'	=> array('lang' => 'READ_NOTIFICATION_EXPIRE_DAYS',	'validate' => 'int:0',	'type' => 'number:0', 'explain' => true, 'append' => ' ' . $this->lang->lang('DAYS')),

						'legend2'				=> 'GENERAL_OPTIONS',
						'load_notifications'	=> array('lang' => 'LOAD_NOTIFICATIONS',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_db_track'			=> array('lang' => 'YES_POST_MARKING',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_db_lastread'		=> array('lang' => 'YES_READ_MARKING',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_anon_lastread'	=> array('lang' => 'YES_ANON_READ_MARKING',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_online'			=> array('lang' => 'YES_ONLINE',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_online_guests'	=> array('lang' => 'YES_ONLINE_GUESTS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_onlinetrack'		=> array('lang' => 'YES_ONLINE_TRACK',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_birthdays'		=> array('lang' => 'YES_BIRTHDAYS',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_unreads_search'	=> array('lang' => 'YES_UNREAD_SEARCH',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_moderators'		=> array('lang' => 'YES_MODERATORS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_jumpbox'			=> array('lang' => 'YES_JUMPBOX',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_user_activity'	=> array('lang' => 'LOAD_USER_ACTIVITY',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'load_user_activity_limit'		=> array('lang' => 'LOAD_USER_ACTIVITY_LIMIT',		'validate' => 'int:0:99999999',	'type' => 'number:0:99999999', 'explain' => true),
						'load_tplcompile'		=> array('lang' => 'RECOMPILE_STYLES',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_cdn'				=> array('lang' => 'ALLOW_CDN',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'enable_accurate_pm_button'	=> array('lang' => 'YES_ACCURATE_PM_BUTTON',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_live_searches'	=> array('lang' => 'ALLOW_LIVE_SEARCHES',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend3'				=> 'CUSTOM_PROFILE_FIELDS',
						'load_cpf_memberlist'	=> array('lang' => 'LOAD_CPF_MEMBERLIST',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_cpf_pm'			=> array('lang' => 'LOAD_CPF_PM',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain'	=> false),
						'load_cpf_viewprofile'	=> array('lang' => 'LOAD_CPF_VIEWPROFILE',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),
						'load_cpf_viewtopic'	=> array('lang' => 'LOAD_CPF_VIEWTOPIC',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false),

						'legend4'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'auth':
				$display_vars = array(
					'title'	=> 'ACP_AUTH_SETTINGS',
					'vars'	=> array(
						'legend1'		=> 'ACP_AUTH_SETTINGS',
						'auth_method'	=> array('lang' => 'AUTH_METHOD',	'validate' => 'string',	'type' => 'select:1:toggable', 'function' => array($this, 'select_auth_method'), 'explain' => false),
					)
				);
			break;

			case 'server':
				$display_vars = array(
					'title'	=> 'ACP_SERVER_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_SERVER_SETTINGS',
						'gzip_compress'			=> array('lang' => 'ENABLE_GZIP',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'use_system_cron'		=> array('lang' => 'USE_SYSTEM_CRON',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend2'				=> 'PATH_SETTINGS',
						'enable_mod_rewrite'	=> array('lang' => 'MOD_REWRITE_ENABLE',	'validate' => 'bool',	'type' => 'custom', 'function' => array($this, 'enable_mod_rewrite'), 'explain' => true),
						'smilies_path'			=> array('lang' => 'SMILIES_PATH',		'validate' => 'rpath',	'type' => 'text:20:255', 'explain' => true),
						'icons_path'			=> array('lang' => 'ICONS_PATH',		'validate' => 'rpath',	'type' => 'text:20:255', 'explain' => true),
						'upload_icons_path'		=> array('lang' => 'UPLOAD_ICONS_PATH',	'validate' => 'rpath',	'type' => 'text:20:255', 'explain' => true),
						'ranks_path'			=> array('lang' => 'RANKS_PATH',		'validate' => 'rpath',	'type' => 'text:20:255', 'explain' => true),

						'legend3'				=> 'SERVER_URL_SETTINGS',
						'force_server_vars'		=> array('lang' => 'FORCE_SERVER_VARS',	'validate' => 'bool',			'type' => 'radio:yes_no', 'explain' => true),
						'server_protocol'		=> array('lang' => 'SERVER_PROTOCOL',	'validate' => 'string',			'type' => 'text:10:10', 'explain' => true),
						'server_name'			=> array('lang' => 'SERVER_NAME',		'validate' => 'string',			'type' => 'text:40:255', 'explain' => true),
						'server_port'			=> array('lang' => 'SERVER_PORT',		'validate' => 'int:0:99999',			'type' => 'number:0:99999', 'explain' => true),
						'script_path'			=> array('lang' => 'SCRIPT_PATH',		'validate' => 'script_path',	'type' => 'text::255', 'explain' => true),

						'legend4'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			case 'security':
				$display_vars = array(
					'title'	=> 'ACP_SECURITY_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_SECURITY_SETTINGS',
						'allow_autologin'		=> array('lang' => 'ALLOW_AUTOLOGIN',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_password_reset'	=> array('lang' => 'ALLOW_PASSWORD_RESET',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'max_autologin_time'	=> array('lang' => 'AUTOLOGIN_LENGTH',		'validate' => 'int:0:99999',	'type' => 'number:0:99999',	'explain' => true,	'append' => ' ' . $this->lang->lang('DAYS')),
						'ip_check'				=> array('lang' => 'IP_VALID',				'validate' => 'int',	'type' => 'custom', 'function' => array($this, 'select_ip_check'), 'explain' => true),
						'browser_check'			=> array('lang' => 'BROWSER_VALID',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'forwarded_for_check'	=> array('lang' => 'FORWARDED_FOR_VALID',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'referer_validation'	=> array('lang' => 'REFERRER_VALID',		'validate' => 'int:0:3','type' => 'custom', 'function' => array($this, 'select_ref_check'), 'explain' => true),
						'remote_upload_verify'	=> array('lang' => 'UPLOAD_CERT_VALID',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'check_dnsbl'			=> array('lang' => 'CHECK_DNSBL',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'email_check_mx'		=> array('lang' => 'EMAIL_CHECK_MX',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'max_pass_chars'		=> array('lang' => 'PASSWORD_LENGTH', 'validate' => 'int:8:255', 'type' => false, 'method' => false, 'explain' => false,),
						'min_pass_chars'		=> array('lang' => 'PASSWORD_LENGTH',	'validate' => 'int:1',	'type' => 'custom', 'function' => array($this, 'password_length'), 'explain' => true),
						'pass_complex'			=> array('lang' => 'PASSWORD_TYPE',			'validate' => 'string',	'type' => 'select', 'function' => array($this, 'select_password_chars'), 'explain' => true),
						'chg_passforce'			=> array('lang' => 'FORCE_PASS_CHANGE',		'validate' => 'int:0:999',	'type' => 'number:0:999', 'explain' => true, 'append' => ' ' . $this->lang->lang('DAYS')),
						'max_login_attempts'	=> array('lang' => 'MAX_LOGIN_ATTEMPTS',	'validate' => 'int:0:999',	'type' => 'number:0:999', 'explain' => true),
						'ip_login_limit_max'	=> array('lang' => 'IP_LOGIN_LIMIT_MAX',	'validate' => 'int:0:999',	'type' => 'number:0:999', 'explain' => true),
						'ip_login_limit_time'	=> array('lang' => 'IP_LOGIN_LIMIT_TIME',	'validate' => 'int:0:99999',	'type' => 'number:0:99999', 'explain' => true, 'append' => ' ' . $this->lang->lang('SECONDS')),
						'ip_login_limit_use_forwarded'	=> array('lang' => 'IP_LOGIN_LIMIT_USE_FORWARDED',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'tpl_allow_php'			=> array('lang' => 'TPL_ALLOW_PHP',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'form_token_lifetime'	=> array('lang' => 'FORM_TIME_MAX',			'validate' => 'int:-1:99999',	'type' => 'number:-1:99999', 'explain' => true, 'append' => ' ' . $this->lang->lang('SECONDS')),
						'form_token_sid_guests'	=> array('lang' => 'FORM_SID_GUESTS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

					)
				);
			break;

			case 'email':
				$display_vars = array(
					'title'	=> 'ACP_EMAIL_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'email_enable'			=> array('lang' => 'ENABLE_EMAIL',			'validate' => 'bool',	'type' => 'radio:enabled_disabled', 'explain' => true),
						'board_email_form'		=> array('lang' => 'BOARD_EMAIL_FORM',		'validate' => 'bool',	'type' => 'radio:enabled_disabled', 'explain' => true),
						'email_package_size'	=> array('lang' => 'EMAIL_PACKAGE_SIZE',	'validate' => 'int:0',	'type' => 'number:0:99999', 'explain' => true),
						'board_contact'			=> array('lang' => 'CONTACT_EMAIL',			'validate' => 'email',	'type' => 'email:25:100', 'explain' => true),
						'board_contact_name'	=> array('lang' => 'CONTACT_EMAIL_NAME',	'validate' => 'string',	'type' => 'text:25:50', 'explain' => true),
						'board_email'			=> array('lang' => 'ADMIN_EMAIL',			'validate' => 'email',	'type' => 'email:25:100', 'explain' => true),
						'email_force_sender'	=> array('lang' => 'EMAIL_FORCE_SENDER',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'board_email_sig'		=> array('lang' => 'EMAIL_SIG',				'validate' => 'string',	'type' => 'textarea:5:30', 'explain' => true),
						'board_hide_emails'		=> array('lang' => 'BOARD_HIDE_EMAILS',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'send_test_email'		=> array('lang' => 'SEND_TEST_EMAIL',		'validate' => 'bool',	'type' => 'custom', 'function' => array($this, 'send_test_email'), 'explain' => true),

						'legend2'				=> 'SMTP_SETTINGS',
						'smtp_delivery'			=> array('lang' => 'USE_SMTP',				'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'smtp_host'				=> array('lang' => 'SMTP_SERVER',			'validate' => 'string',	'type' => 'text:25:50', 'explain' => true),
						'smtp_port'				=> array('lang' => 'SMTP_PORT',				'validate' => 'int:0:99999',	'type' => 'number:0:99999', 'explain' => true),
						'smtp_auth_method'		=> array('lang' => 'SMTP_AUTH_METHOD',		'validate' => 'string',	'type' => 'select', 'function' => array($this, 'mail_auth_select'), 'explain' => true),
						'smtp_username'			=> array('lang' => 'SMTP_USERNAME',			'validate' => 'string',	'type' => 'text:25:255', 'explain' => true),
						'smtp_password'			=> array('lang' => 'SMTP_PASSWORD',			'validate' => 'string',	'type' => 'password:25:255', 'explain' => true),
						'smtp_verify_peer'		=> array('lang' => 'SMTP_VERIFY_PEER',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'smtp_verify_peer_name'	=> array('lang' => 'SMTP_VERIFY_PEER_NAME',	'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),
						'smtp_allow_self_signed'=> array('lang' => 'SMTP_ALLOW_SELF_SIGNED','validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),

						'legend3'					=> 'ACP_SUBMIT_CHANGES',
					)
				);
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		/**
		 * Event to add and/or modify acp_board configurations
		 *
		 * @event core.acp_board_config_edit_add
		 * @var	array	display_vars	Array of config values to display and process
		 * @var	string	mode			Mode of the config page we are displaying
		 * @var	boolean	submit			Do we display the form or process the submission
		 * @since 3.1.0-a4
		 */
		$vars = array('display_vars', 'mode', 'submit');
		extract($this->dispatcher->trigger_event('core.acp_board_config_edit_add', compact($vars)));

		if (isset($display_vars['lang']))
		{
			$this->lang->add_lang($display_vars['lang']);
		}

		$this->new_config = clone $this->config;
		$cfg_array = (isset($_REQUEST['config'])) ? $this->request->variable('config', array('' => ''), true) : $this->new_config;
		$error = array();

		// We validate the complete config if wished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $this->lang->lang('FORM_INVALID');
		}
		// Do not write values if there is an error
		if (count($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $data)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			if ($config_name == 'auth_method' || $config_name == 'feed_news_id' || $config_name == 'feed_exclude_id')
			{
				continue;
			}

			if ($config_name == 'guest_style')
			{
				if (isset($cfg_array[$config_name]))
				{
					$this->guest_style_set($cfg_array[$config_name]);
				}
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				if (strpos($data['type'], 'password') === 0 && $config_value === '********')
				{
					// Do not update password fields if the content is ********,
					// because that is the password replacement we use to not
					// send the password to the output
					continue;
				}
				$this->config->set($config_name, $config_value);

				if ($config_name == 'allow_quick_reply' && isset($_POST['allow_quick_reply_enable']))
				{
					enable_bitfield_column_flag(FORUMS_TABLE, 'forum_flags', round(log(FORUM_FLAG_QUICK_REPLY, 2)));
				}
			}
		}

		// Invalidate the text_formatter cache when posting options are changed
		if ($mode == 'post' && $submit)
		{
			$this->container->get('text_formatter.cache')->invalidate();
		}

		// Store news and exclude ids
		if ($mode == 'feed' && $submit)
		{
			$this->cache->destroy('_feed_news_forum_ids');
			$this->cache->destroy('_feed_excluded_forum_ids');

			$this->store_feed_forums(FORUM_OPTION_FEED_NEWS, 'feed_news_id');
			$this->store_feed_forums(FORUM_OPTION_FEED_EXCLUDE, 'feed_exclude_id');
		}

		if ($mode == 'auth')
		{
			// Retrieve a list of auth plugins and check their config values
			/* @var $auth_providers \phpbb\auth\provider_collection */
			$auth_providers = $this->container->get('auth.provider_collection');

			$updated_auth_settings = false;
			$old_auth_config = array();
			foreach ($auth_providers as $provider)
			{
				/** @var \phpbb\auth\provider\provider_interface $provider */
				if ($fields = $provider->acp())
				{
					// Check if we need to create config fields for this plugin and save config when submit was pressed
					foreach ($fields as $field)
					{
						if (!isset($this->config[$field]))
						{
							$this->config->set($field, '');
						}

						if (!isset($cfg_array[$field]) || strpos($field, 'legend') !== false)
						{
							continue;
						}

						if (substr($field, -9) === '_password' && $cfg_array[$field] === '********')
						{
							// Do not update password fields if the content is ********,
							// because that is the password replacement we use to not
							// send the password to the output
							continue;
						}

						$old_auth_config[$field] = $this->new_config[$field];
						$config_value = $cfg_array[$field];
						$this->new_config[$field] = $config_value;

						if ($submit)
						{
							$updated_auth_settings = true;
							$this->config->set($field, $config_value);
						}
					}
				}
				unset($fields);
			}

			if ($submit && (($cfg_array['auth_method'] != $this->new_config['auth_method']) || $updated_auth_settings))
			{
				$method = basename($cfg_array['auth_method']);
				if (array_key_exists('auth.provider.' . $method, $auth_providers))
				{
					$provider = $auth_providers['auth.provider.' . $method];
					if ($error = $provider->init())
					{
						foreach ($old_auth_config as $config_name => $config_value)
						{
							$this->config->set($config_name, $config_value);
						}
						trigger_error($error . adm_back_link($this->helper->get_current_url()), E_USER_WARNING);
					}
					$this->config->set('auth_method', basename($cfg_array['auth_method']));
				}
				else
				{
					trigger_error('NO_AUTH_PLUGIN', E_USER_ERROR);
				}
			}
		}

		if ($mode == 'email' && $this->request->is_set_post('send_test_email'))
		{
			if ($this->config['email_enable'])
			{
				include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

				$messenger = new \messenger(false);
				$messenger->template('test');
				$messenger->set_addresses($this->user->data);
				$messenger->anti_abuse_headers($this->config, $this->user);
				$messenger->assign_vars(array(
					'USERNAME'	=> htmlspecialchars_decode($this->user->data['username']),
				));
				$messenger->send(NOTIFY_EMAIL);

				trigger_error($this->lang->lang('TEST_EMAIL_SENT') . adm_back_link($this->helper->get_current_url()));
			}
			else
			{
				$this->user->add_lang('memberlist');
				trigger_error($this->lang->lang('EMAIL_DISABLED') . adm_back_link($this->helper->get_current_url()), E_USER_WARNING);
			}
		}

		if ($submit)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_' . strtoupper($mode));

			$message = $this->lang->lang('CONFIG_UPDATED');
			if (!$this->config['email_enable'] && in_array($mode, array('email', 'registration')) &&
				in_array($this->config['require_activation'], array(USER_ACTIVATION_SELF, USER_ACTIVATION_ADMIN)))
			{
				$message .= '<br /><br />' . $this->lang->lang('ACC_ACTIVATION_WARNING');

				// @todo a "warning" class?
			}

			return $this->helper->message_back($this->helper->get_current_url(), $message);
		}

		$this->template->assign_vars(array(
			'L_TITLE'			=> $this->lang->lang($display_vars['title']),
			'L_TITLE_EXPLAIN'	=> $this->lang->lang($display_vars['title'] . '_EXPLAIN'),

			'S_ERROR'			=> (count($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->helper->get_current_url()
		));

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$this->template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $this->lang->is_set($vars) ? $this->lang->lang($vars) : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = $this->lang->is_set($vars['lang_explain']) ? $this->lang->lang($vars['lang_explain']) : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = $this->lang->is_set([$vars['lang'] . '_EXPLAIN']) ? $this->lang->lang($vars['lang'] . '_EXPLAIN') : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$this->template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> $this->lang->is_set($vars['lang']) ? $this->lang->lang($vars['lang']) : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
			));

			unset($display_vars['vars'][$config_key]);
		}

		if ($mode == 'auth')
		{
			$this->template->assign_var('S_AUTH', true);

			foreach ($auth_providers as $provider)
			{
				$auth_tpl = $provider->get_acp_template($this->new_config);
				if ($auth_tpl)
				{
					if (array_key_exists('BLOCK_VAR_NAME', $auth_tpl))
					{
						foreach ($auth_tpl['BLOCK_VARS'] as $block_vars)
						{
							$this->template->assign_block_vars($auth_tpl['BLOCK_VAR_NAME'], $block_vars);
						}
					}
					$this->template->assign_vars($auth_tpl['TEMPLATE_VARS']);
					$this->template->assign_block_vars('auth_tpl', array(
						'TEMPLATE_FILE'	=> $auth_tpl['TEMPLATE_FILE'],
					));
				}
			}
		}

		return $this->helper->render('acp_board.html', $this->lang->lang($display_vars['title']));
	}

	/**
	 * Select auth method
	 */
	function select_auth_method($selected_method, $key = '')
	{
		/* @var $auth_providers \phpbb\auth\provider_collection */
		$auth_providers = $this->container->get('auth.provider_collection');
		$auth_plugins = array();

		foreach ($auth_providers as $key => $value)
		{
			if (!($value instanceof \phpbb\auth\provider\provider_interface))
			{
				continue;
			}
			$auth_plugins[] = str_replace('auth.provider.', '', $key);
		}

		sort($auth_plugins);

		$auth_select = '';
		foreach ($auth_plugins as $method)
		{
			$selected = ($selected_method == $method) ? ' selected="selected"' : '';
			$auth_select .= "<option value=\"$method\"$selected data-toggle-setting=\"#auth_{$method}_settings\">" . ucfirst($method) . '</option>';
		}

		return $auth_select;
	}

	/**
	 * Select mail authentication method
	 */
	function mail_auth_select($selected_method, $key = '')
	{
		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5', 'POP-BEFORE-SMTP');
		$s_smtp_auth_options = '';

		foreach ($auth_methods as $method)
		{
			$s_smtp_auth_options .= '<option value="' . $method . '"' . (($selected_method == $method) ? ' selected="selected"' : '') . '>' . $this->lang->lang('SMTP_' . str_replace('-', '_', $method)) . '</option>';
		}

		return $s_smtp_auth_options;
	}

	/**
	 * Select full folder action
	 */
	function full_folder_select($value, $key = '')
	{
		return '<option value="1"' . (($value == 1) ? ' selected="selected"' : '') . '>' . $this->lang->lang('DELETE_OLDEST_MESSAGES') . '</option><option value="2"' . (($value == 2) ? ' selected="selected"' : '') . '>' . $this->lang->lang('HOLD_NEW_MESSAGES_SHORT') . '</option>';
	}

	/**
	 * Select ip validation
	 */
	function select_ip_check($value, $key = '')
	{
		$radio_ary = array(4 => 'ALL', 3 => 'CLASS_C', 2 => 'CLASS_B', 0 => 'NO_IP_VALIDATION');

		return h_radio('config[ip_check]', $radio_ary, $value, $key);
	}

	/**
	 * Select referer validation
	 */
	function select_ref_check($value, $key = '')
	{
		$radio_ary = array(REFERER_VALIDATE_PATH => 'REF_PATH', REFERER_VALIDATE_HOST => 'REF_HOST', REFERER_VALIDATE_NONE => 'NO_REF_VALIDATION');

		return h_radio('config[referer_validation]', $radio_ary, $value, $key);
	}

	/**
	 * Select account activation method
	 */
	function select_acc_activation($selected_value, $value)
	{
		$act_ary = array(
			'ACC_DISABLE'	=> array(true, USER_ACTIVATION_DISABLE),
			'ACC_NONE'		=> array(true, USER_ACTIVATION_NONE),
			'ACC_USER'		=> array($this->config['email_enable'], USER_ACTIVATION_SELF),
			'ACC_ADMIN'		=> array($this->config['email_enable'], USER_ACTIVATION_ADMIN),
		);

		$act_options = '';
		foreach ($act_ary as $key => $data)
		{
			list($available, $value) = $data;
			$selected = ($selected_value == $value) ? ' selected="selected"' : '';
			$class = (!$available) ? ' class="disabled-option"' : '';
			$act_options .= '<option value="' . $value . '"' . $selected . $class . '>' . $this->lang->lang($key) . '</option>';
		}

		return $act_options;
	}

	/**
	 * Maximum/Minimum username length
	 */
	function username_length($value, $key = '')
	{
		return '<input id="' . $key . '" type="number" min="1" max="999" name="config[min_name_chars]" value="' . $value . '" /> ' . $this->lang->lang('MIN_CHARS') . '&nbsp;&nbsp;<input type="number" min="8" max="180" name="config[max_name_chars]" value="' . $this->new_config['max_name_chars'] . '" /> ' . $this->lang->lang('MAX_CHARS');
	}

	/**
	 * Allowed chars in usernames
	 */
	function select_username_chars($selected_value, $key)
	{
		$user_char_ary = array('USERNAME_CHARS_ANY', 'USERNAME_ALPHA_ONLY', 'USERNAME_ALPHA_SPACERS', 'USERNAME_LETTER_NUM', 'USERNAME_LETTER_NUM_SPACERS', 'USERNAME_ASCII');
		$user_char_options = '';
		foreach ($user_char_ary as $user_type)
		{
			$selected = ($selected_value == $user_type) ? ' selected="selected"' : '';
			$user_char_options .= '<option value="' . $user_type . '"' . $selected . '>' . $this->lang->lang($user_type) . '</option>';
		}

		return $user_char_options;
	}

	/**
	 * Maximum/Minimum password length
	 */
	function password_length($value, $key)
	{
		return '<input id="' . $key . '" type="number" min="1" max="999" name="config[min_pass_chars]" value="' . $value . '" /> ' . $this->lang->lang('MIN_CHARS') . '&nbsp;&nbsp;<input type="number" min="8" max="255" name="config[max_pass_chars]" value="' . $this->new_config['max_pass_chars'] . '" /> ' . $this->lang->lang('MAX_CHARS');
	}

	/**
	 * Required chars in passwords
	 */
	function select_password_chars($selected_value, $key)
	{
		$pass_type_ary = array('PASS_TYPE_ANY', 'PASS_TYPE_CASE', 'PASS_TYPE_ALPHA', 'PASS_TYPE_SYMBOL');
		$pass_char_options = '';
		foreach ($pass_type_ary as $pass_type)
		{
			$selected = ($selected_value == $pass_type) ? ' selected="selected"' : '';
			$pass_char_options .= '<option value="' . $pass_type . '"' . $selected . '>' . $this->lang->lang($pass_type) . '</option>';
		}

		return $pass_char_options;
	}

	/**
	 * Select bump interval
	 */
	function bump_interval($value, $key)
	{
		$s_bump_type = '';
		$types = array('m' => 'MINUTES', 'h' => 'HOURS', 'd' => 'DAYS');
		foreach ($types as $type => $lang)
		{
			$selected = ($this->new_config['bump_type'] == $type) ? ' selected="selected"' : '';
			$s_bump_type .= '<option value="' . $type . '"' . $selected . '>' . $this->lang->lang($lang) . '</option>';
		}

		return '<input id="' . $key . '" type="text" size="3" maxlength="4" name="config[bump_interval]" value="' . $value . '" />&nbsp;<select name="config[bump_type]">' . $s_bump_type . '</select>';
	}

	/**
	 * Board disable option and message
	 */
	function board_disable($value, $key)
	{
		$radio_ary = array(1 => 'YES', 0 => 'NO');

		return h_radio('config[board_disable]', $radio_ary, $value) . '<br /><input id="' . $key . '" type="text" name="config[board_disable_msg]" maxlength="255" size="40" value="' . $this->new_config['board_disable_msg'] . '" />';
	}

	/**
	 * Global quick reply enable/disable setting and button to enable in all forums
	 */
	function quick_reply($value, $key)
	{
		$radio_ary = array(1 => 'YES', 0 => 'NO');

		return h_radio('config[allow_quick_reply]', $radio_ary, $value) .
			'<br /><br /><input class="button2" type="submit" id="' . $key . '_enable" name="' . $key . '_enable" value="' . $this->user->lang['ALLOW_QUICK_REPLY_BUTTON'] . '" />';
	}

	/**
	 * Select guest timezone
	 */
	function timezone_select($value, $key)
	{
		$timezone_select = phpbb_timezone_select($this->template, $this->user, $value, true);

		return '<select name="config[' . $key . ']" id="' . $key . '">' . $timezone_select . '</select>';
	}

	/**
	 * Get guest style
	 */
	public function guest_style_get()
	{
		$sql = 'SELECT user_style
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . ANONYMOUS;
		$result = $this->db->sql_query($sql);

		$style = (int) $this->db->sql_fetchfield('user_style');
		$this->db->sql_freeresult($result);

		return $style;
	}

	/**
	 * Set guest style
	 *
	 * @param	int		$style_id	The style ID
	 */
	public function guest_style_set($style_id)
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_style = ' . (int) $style_id . '
			WHERE user_id = ' . ANONYMOUS;
		$this->db->sql_query($sql);
	}

	/**
	 * Select default dateformat
	 */
	function dateformat_select($value, $key)
	{
		// Let the format_date function operate with the acp values
		$old_tz = $this->user->timezone;
		try
		{
			$this->user->timezone = new \DateTimeZone($this->config['board_timezone']);
		}
		catch (\Exception $e)
		{
			// If the board timezone is invalid, we just use the users timezone.
		}

		$dateformat_options = '';
		foreach ($this->lang->lang_raw('dateformats') as $format => $null)
		{
			$dateformat_options .= '<option value="' . $format . '"' . (($format == $value) ? ' selected="selected"' : '') . '>';
			$dateformat_options .= $this->user->format_date(time(), $format, false) . ((strpos($format, '|') !== false) ? $this->lang->lang('VARIANT_DATE_SEPARATOR') . $this->user->format_date(time(), $format, true) : '');
			$dateformat_options .= '</option>';
		}

		$dateformat_options .= '<option value="custom"';
		if (!isset($this->lang->lang('dateformats')[$value]))
		{
			$dateformat_options .= ' selected="selected"';
		}
		$dateformat_options .= '>' . $this->lang->lang('CUSTOM_DATEFORMAT') . '</option>';

		// Reset users date options
		$this->user->timezone = $old_tz;

		return "<select name=\"dateoptions\" id=\"dateoptions\" onchange=\"if (this.value === 'custom') { document.getElementById('" . addslashes($key) . "').value = '" . addslashes($value) . "'; } else { document.getElementById('" . addslashes($key) . "').value = this.value; }\">$dateformat_options</select>
		<input type=\"text\" name=\"config[$key]\" id=\"$key\" value=\"$value\" maxlength=\"64\" />";
	}

	/**
	 * Select multiple forums
	 */
	function select_news_forums($value, $key)
	{
		$forum_list = make_forum_select(false, false, true, true, true, false, true);

		// Build forum options
		$s_forum_options = '<select id="' . $key . '" name="' . $key . '[]" multiple="multiple">';
		foreach ($forum_list as $f_id => $f_row)
		{
			$f_row['selected'] = phpbb_optionget(FORUM_OPTION_FEED_NEWS, $f_row['forum_options']);

			$s_forum_options .= '<option value="' . $f_id . '"' . (($f_row['selected']) ? ' selected="selected"' : '') . (($f_row['disabled']) ? ' disabled="disabled" class="disabled-option"' : '') . '>' . $f_row['padding'] . $f_row['forum_name'] . '</option>';
		}
		$s_forum_options .= '</select>';

		return $s_forum_options;
	}

	function select_exclude_forums($value, $key)
	{
		$forum_list = make_forum_select(false, false, true, true, true, false, true);

		// Build forum options
		$s_forum_options = '<select id="' . $key . '" name="' . $key . '[]" multiple="multiple">';
		foreach ($forum_list as $f_id => $f_row)
		{
			$f_row['selected'] = phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $f_row['forum_options']);

			$s_forum_options .= '<option value="' . $f_id . '"' . (($f_row['selected']) ? ' selected="selected"' : '') . (($f_row['disabled']) ? ' disabled="disabled" class="disabled-option"' : '') . '>' . $f_row['padding'] . $f_row['forum_name'] . '</option>';
		}
		$s_forum_options .= '</select>';

		return $s_forum_options;
	}

	function store_feed_forums($option, $key)
	{
		// Get key
		$values = $this->request->variable($key, array(0 => 0));

		// Empty option bit for all forums
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET forum_options = forum_options - ' . (1 << $option) . '
			WHERE ' . $this->db->sql_bit_and('forum_options', $option, '<> 0');
		$this->db->sql_query($sql);

		// Already emptied for all...
		if (count($values))
		{
			// Set for selected forums
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET forum_options = forum_options + ' . (1 << $option) . '
				WHERE ' . $this->db->sql_in_set('forum_id', $values);
			$this->db->sql_query($sql);
		}

		// Empty sql cache for forums table because options changed
		$this->cache->destroy('sql', FORUMS_TABLE);
	}

	/**
	 * Option to enable/disable removal of 'app.php' from URLs
	 *
	 * Note that if mod_rewrite is on, URLs without app.php will still work,
	 * but any paths generated by the controller helper url() method will not
	 * contain app.php.
	 *
	 * @param int $value The current config value
	 * @param string $key The config key
	 * @return string The HTML for the form field
	 */
	function enable_mod_rewrite($value, $key)
	{
		// Determine whether mod_rewrite is enabled on the server
		// NOTE: This only works on Apache servers on which PHP is NOT
		// installed as CGI. In that case, there is no way for PHP to
		// determine whether or not the Apache module is enabled.
		//
		// To be clear on the value of $mod_rewite:
		// null = Cannot determine whether or not the server has mod_rewrite
		//        enabled
		// false = Can determine that the server does NOT have mod_rewrite
		//         enabled
		// true = Can determine that the server DOES have mod_rewrite_enabled
		$mod_rewrite = null;
		if (function_exists('apache_get_modules'))
		{
			$mod_rewrite = (bool) in_array('mod_rewrite', apache_get_modules());
		}

		// If $message is false, mod_rewrite is enabled.
		// Otherwise, it is not and we need to:
		// 1) disable the form field
		// 2) make sure the config value is set to 0
		// 3) append the message to the return
		$value = ($mod_rewrite === false) ? 0 : $value;
		$message = $mod_rewrite === null ? 'MOD_REWRITE_INFORMATION_UNAVAILABLE' : ($mod_rewrite === false ? 'MOD_REWRITE_DISABLED' : false);

		// Let's do some friendly HTML injection if we want to disable the
		// form field because h_radio() has no pretty way of doing so
		$field_name = 'config[enable_mod_rewrite]' . ($message === 'MOD_REWRITE_DISABLED' ? '" disabled="disabled' : '');

		return h_radio($field_name, array(1 => 'YES', 0 => 'NO'), $value) .
			($message !== false ? '<br /><span>' . $this->lang->lang($message) . '</span>' : '');
	}

	function send_test_email($value, $key)
	{
		return '<input class="button2" type="submit" id="' . $key . '" name="' . $key . '" value="' . $this->lang->lang('SEND_TEST_EMAIL') . '" />';
	}
}
