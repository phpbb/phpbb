<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_board
{
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/board');

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		switch ($mode)
		{
			case 'cookie':
				$display_vars = array(
					'title'	=> 'ACP_COOKIE_SETTINGS',
					'vars'	=> array(
						'legend1'		=> 'ACP_COOKIE_SETTINGS',
						'cookie_domain'	=> array('lang' => 'COOKIE_DOMAIN',	'type' => 'text::255', 'explain' => false),
						'cookie_name'	=> array('lang' => 'COOKIE_NAME',	'type' => 'text::16', 'explain' => false),
						'cookie_path'	=> array('lang'	=> 'COOKIE_PATH',	'type' => 'text::255', 'explain' => false),
						'cookie_secure'	=> array('lang' => 'COOKIE_SECURE',	'type' => 'radio:disabled_enabled', 'explain' => true)
					)
				);
			break;

			case 'avatar':
				$display_vars = array(
					'title'	=> 'ACP_AVATAR_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_AVATAR_SETTINGS',
						'avatar_min_height'		=> false, 'avatar_min_width' => false, 'avatar_max_height' => false, 'avatar_max_width' => false,

						'allow_avatar_local'	=> array('lang' => 'ALLOW_LOCAL',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_avatar_remote'	=> array('lang' => 'ALLOW_REMOTE',	'type' => 'radio:yes_no', 'explain' => true),
						'allow_avatar_upload'	=> array('lang' => 'ALLOW_UPLOAD',	'type' => 'radio:yes_no', 'explain' => false),
						'avatar_filesize'		=> array('lang' => 'MAX_FILESIZE',	'type' => 'text:4:10', 'explain' => true, 'append' => ' ' . $user->lang['BYTES']),
						'avatar_min'			=> array('lang' => 'MIN_AVATAR_SIZE',	'type' => 'dimension:3:4', 'explain' => true),
						'avatar_max'			=> array('lang' => 'MAX_AVATAR_SIZE',	'type' => 'dimension:3:4', 'explain' => true),
						'avatar_path'			=> array('lang' => 'AVATAR_STORAGE_PATH',	'type' => 'text:20:255', 'explain' => true),
						'avatar_gallery_path'	=> array('lang' => 'AVATAR_GALLERY_PATH',	'type' => 'text:20:255', 'explain' => true)
					)
				);
			break;

			case 'settings':
				$display_vars = array(
					'title'	=> 'ACP_BOARD_SETTINGS',
					'vars'	=> array(
						'legend1'			=> 'GENERAL_SETTINGS',
						'sitename'			=> array('lang' => 'SITE_NAME',			'type' => 'text:40:255', 'explain' => false),
						'site_desc'			=> array('lang' => 'SITE_DESC',			'type' => 'text:40:255', 'explain' => false),
						'board_disable'		=> array('lang' => 'DISABLE_BOARD',		'type' => 'custom', 'method' => 'board_disable', 'explain' => true),
						'board_disable_msg'	=> false, 'max_name_chars' => false, 'max_pass_chars' => false, 'bump_type' => false,

						'legend2'			=> 'COPPA',
						'coppa_enable'		=> array('lang' => 'ENABLE_COPPA',		'type' => 'radio:yes_no', 'explain' => true),
						'coppa_mail'		=> array('lang' => 'COPPA_MAIL',		'type' => 'textarea:5:40', 'explain' => true),
						'coppa_fax'			=> array('lang' => 'COPPA_FAX',			'type' => 'text:25:100', 'explain' => false),

						'legend3'			=> 'REGISTRATION',
						'require_activation'=> array('lang' => 'ACC_ACTIVATION',	'type' => 'custom', 'method' => 'select_acc_activation', 'explain' => true),
						'enable_confirm'	=> array('lang' => 'VISUAL_CONFIRM',	'type' => 'radio:yes_no', 'explain' => true),
						'max_reg_attempts'	=> array('lang' => 'REG_LIMIT',			'type' => 'text:4:4', 'explain' => true),
						'min_name_chars'	=> array('lang' => 'USERNAME_LENGTH',	'type' => 'custom', 'method' => 'username_length', 'explain' => true),
						'min_pass_chars'	=> array('lang' => 'PASSWORD_LENGTH',	'type' => 'custom', 'method' => 'password_length', 'explain' => true),
						'allow_name_chars'	=> array('lang' => 'USERNAME_CHARS',	'type' => 'select', 'method' => 'select_username_chars', 'explain' => true),
						'pass_complex'		=> array('lang' => 'PASSWORD_TYPE',		'type' => 'select', 'method' => 'select_password_chars', 'explain' => true),
						'chg_passforce'		=> array('lang' => 'FORCE_PASS_CHANGE',	'type' => 'text:3:3', 'explain' => true),
						'allow_emailreuse'	=> array('lang' => 'ALLOW_EMAIL_REUSE',	'type' => 'radio:yes_no', 'explain' => true),

						'legend4'			=> 'POSTING',
						'edit_time'			=> array('lang' => 'EDIT_TIME',			'type' => 'text:3:3', 'explain' => true),
						'display_last_edited' => array('lang' => 'DISPLAY_LAST_EDITED', 'type' => 'radio:yes_no', 'explain' => true),
						'flood_interval'	=> array('lang' => 'FLOOD_INTERVAL',	'type' => 'text:3:4', 'explain' => true),
						'bump_interval'		=> array('lang' => 'BUMP_INTERVAL',		'type' => 'custom', 'method' => 'bump_interval', 'explain' => true),
						'topics_per_page'	=> array('lang' => 'TOPICS_PER_PAGE',	'type' => 'text:3:4', 'explain' => false),
						'posts_per_page'	=> array('lang' => 'POSTS_PER_PAGE',	'type' => 'text:3:4', 'explain' => false),
						'hot_threshold'		=> array('lang' => 'HOT_THRESHOLD',		'type' => 'text:3:4', 'explain' => false),
						'max_poll_options'	=> array('lang' => 'MAX_POLL_OPTIONS',	'type' => 'text:4:4', 'explain' => false),
						'max_post_chars'	=> array('lang' => 'CHAR_LIMIT',		'type' => 'text:4:6', 'explain' => true),
						'max_post_smilies'	=> array('lang' => 'SMILIES_LIMIT',		'type' => 'text:4:4', 'explain' => true),
						'max_quote_depth'	=> array('lang' => 'QUOTE_DEPTH_LIMIT',	'type' => 'text:4:4', 'explain' => true)
					)
				);
			break;

			case 'default':
				$display_vars = array(
					'title'	=> 'ACP_BOARD_DEFAULTS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'default_style'			=> array('lang' => 'DEFAULT_STYLE',			'type' => 'select', 'function' => 'style_select', 'params' => array('{CONFIG_VALUE}', true), 'explain' => false),
						'override_user_style'	=> array('lang' => 'OVERRIDE_STYLE',		'type' => 'radio:yes_no', 'explain' => true),
						'default_lang'			=> array('lang' => 'DEFAULT_LANGUAGE',		'type' => 'select', 'function' => 'language_select', 'params' => array('{CONFIG_VALUE}'), 'explain' => false),
						'default_dateformat'	=> array('lang' => 'DEFAULT_DATE_FORMAT',	'type' => 'text::255', 'explain' => true),
						'board_timezone'		=> array('lang' => 'SYSTEM_TIMEZONE',		'type' => 'select', 'function' => 'tz_select', 'params' => array('{CONFIG_VALUE}'), 'explain' => false),
						'board_dst'				=> array('lang' => 'SYSTEM_DST',			'type' => 'radio:yes_no', 'explain' => false),
						'allow_html_tags'		=> array('lang' => 'ALLOWED_TAGS',			'type' => 'text:30:255', 'explain' => true),
						'max_sig_chars'			=> array('lang' => 'MAX_SIG_LENGTH',		'type' => 'text:5:4', 'explain' => true),

						'legend2'				=> 'GENERAL_OPTIONS',
						'allow_privmsg'			=> array('lang' => 'BOARD_PM',				'type' => 'radio:yes_no', 'explain' => true),
						'allow_topic_notify'	=> array('lang' => 'ALLOW_TOPIC_NOTIFY',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_forum_notify'	=> array('lang' => 'ALLOW_FORUM_NOTIFY',	'type' => 'radio:yes_no', 'explain' => false),
						'allow_namechange'		=> array('lang' => 'ALLOW_NAME_CHANGE',		'type' => 'radio:yes_no', 'explain' => false),
						'allow_attachments'		=> array('lang' => 'ALLOW_ATTACHMENTS',		'type' => 'radio:yes_no', 'explain' => false),
						'allow_html'			=> array('lang' => 'ALLOW_HTML',			'type' => 'radio:yes_no', 'explain' => false),
						'allow_bbcode'			=> array('lang' => 'ALLOW_BBCODE',			'type' => 'radio:yes_no', 'explain' => false),
						'allow_smilies'			=> array('lang' => 'ALLOW_SMILIES',			'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig'				=> array('lang' => 'ALLOW_SIG',				'type' => 'radio:yes_no', 'explain' => false),
						'allow_nocensors'		=> array('lang' => 'ALLOW_NO_CENSORS',		'type' => 'radio:yes_no', 'explain' => true),
						'allow_bookmarks'		=> array('lang' => 'ALLOW_BOOKMARKS',		'type' => 'radio:yes_no', 'explain' => true)
					)
				);
			break;

			case 'load':
				$display_vars = array(
					'title'	=> 'ACP_LOAD_SETTINGS',
					'vars'	=> array(
						'legend1'			=> 'GENERAL_SETTINGS',
						'limit_load'		=> array('lang' => 'LIMIT_LOAD',		'type' => 'text:4:4', 'explain' => true),
						'session_length'	=> array('lang' => 'SESSION_LENGTH',	'type' => 'text:5:5', 'explain' => true),
						'active_sessions'	=> array('lang' => 'LIMIT_SESSIONS',	'type' => 'text:4:4', 'explain' => true),
						'load_online_time'	=> array('lang' => 'ONLINE_LENGTH',		'type' => 'text:4:3', 'explain' => true),

						'legend2'			=> 'GENERAL_OPTIONS',
						'load_db_track'		=> array('lang' => 'YES_POST_MARKING',	'type' => 'radio:yes_no', 'explain' => true),
						'load_db_lastread'	=> array('lang' => 'YES_READ_MARKING',	'type' => 'radio:yes_no', 'explain' => true),
						'load_online'		=> array('lang' => 'YES_ONLINE',		'type' => 'radio:yes_no', 'explain' => true),
						'load_onlinetrack'	=> array('lang' => 'YES_ONLINE_TRACK',	'type' => 'radio:yes_no', 'explain' => true),
						'load_birthdays'	=> array('lang' => 'YES_BIRTHDAYS',		'type' => 'radio:yes_no', 'explain' => false),
						'load_moderators'	=> array('lang' => 'YES_MODERATORS',	'type' => 'radio:yes_no', 'explain' => false),
						'load_jumpbox'		=> array('lang' => 'YES_JUMPBOX',		'type' => 'radio:yes_no', 'explain' => false),
						'load_tplcompile'	=> array('lang' => 'RECOMPILE_TEMPLATES', 'type' => 'radio:yes_no', 'explain' => true),

						'legend3'			=> 'SEARCH_SETTINGS',
						'load_search'		=> array('lang' => 'YES_SEARCH',		'type' => 'radio:yes_no', 'explain' => true),
						'search_interval'	=> array('lang' => 'SEARCH_INTERVAL',	'type' => 'text:3:4', 'explain' => true),
						'search_type'		=> array('lang' => 'SEARCH_TYPE',		'type' => 'select', 'method' => 'select_search_type', 'explain' => true),
						'load_search_upd'	=> array('lang' => 'YES_SEARCH_UPDATE',	'type' => 'radio:yes_no', 'explain' => true),
						'min_search_chars'	=> array('lang' => 'MIN_SEARCH_CHARS',	'type' => 'text:3:3', 'explain' => true),
						'max_search_chars'	=> array('lang' => 'MAX_SEARCH_CHARS',	'type' => 'text:3:3', 'explain' => true)
					)
				);
			break;

			case 'auth':
				$display_vars = array(
					'title'	=> 'ACP_AUTH_SETTINGS',
					'vars'	=> array(
						'legend1'		=> 'ACP_AUTH_SETTINGS',
						'auth_method'	=> array('lang' => 'AUTH_METHOD',	'type' => 'select', 'method' => 'select_auth_method', 'explain' => false)
					)
				);
			break;

			case 'server':
				$display_vars = array(
					'title'	=> 'ACP_SERVER_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'ACP_SERVER_SETTINGS',
						'server_name'			=> array('lang' => 'SERVER_NAME',		'type' => 'text:40:255', 'explain' => true),
						'server_port'			=> array('lang' => 'SERVER_PORT',		'type' => 'text:5:5', 'explain' => true),
						'script_path'			=> array('lang' => 'SCRIPT_PATH',		'type' => 'text::255', 'explain' => true),
						'allow_autologin'		=> array('lang' => 'ALLOW_AUTOLOGIN',	'type' => 'radio:yes_no', 'explain' => true),
						'max_autologin_time'	=> array('lang' => 'AUTOLOGIN_LENGTH',	'type' => 'text:5:5', 'explain' => true),
						'ip_check'				=> array('lang' => 'IP_VALID',			'type' => 'custom', 'method' => 'select_ip_check', 'explain' => true),
						'browser_check'			=> array('lang' => 'BROWSER_VALID',		'type' => 'radio:yes_no', 'explain' => true),
						'gzip_compress'			=> array('lang' => 'ENABLE_GZIP',		'type' => 'radio:yes_no', 'explain' => false),

						'legend2'				=> 'PATH_SETTINGS',
						'smilies_path'			=> array('lang' => 'SMILIES_PATH',		'type' => 'text:20:255', 'explain' => true),
						'icons_path'			=> array('lang' => 'ICONS_PATH',		'type' => 'text:20:255', 'explain' => true),
						'upload_icons_path'		=> array('lang' => 'UPLOAD_ICONS_PATH',	'type' => 'text:20:255', 'explain' => true),
						'ranks_path'			=> array('lang' => 'RANKS_PATH',		'type' => 'text:20:255', 'explain' => true)
					)
				);
			break;

			case 'email':
				$display_vars = array(
					'title'	=> 'ACP_EMAIL_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'email_enable'			=> array('lang' => 'ENABLE_EMAIL',			'type' => 'radio:enabled_disabled', 'explain' => true),
						'board_email_form'		=> array('lang' => 'BOARD_EMAIL_FORM',		'type' => 'radio:enabled_disabled', 'explain' => true),
						'email_function_name'	=> array('lang' => 'EMAIL_FUNCTION_NAME',	'type' => 'text:20:50', 'explain' => true),
						'email_package_size'	=> array('lang' => 'EMAIL_PACKAGE_SIZE',	'type' => 'text:5:5', 'explain' => true),
						'board_contact'			=> array('lang' => 'CONTACT_EMAIL',			'type' => 'text:25:100', 'explain' => true),
						'board_email'			=> array('lang' => 'ADMIN_EMAIL',			'type' => 'text:25:100', 'explain' => true),
						'board_email_sig'		=> array('lang' => 'EMAIL_SIG',				'type' => 'textarea:5:30', 'explain' => true),
						
						'legend2'				=> 'SMTP_SETTINGS',
						'smtp_delivery'			=> array('lang' => 'USE_SMTP',				'type' => 'radio:yes_no', 'explain' => true),
						'smtp_host'				=> array('lang' => 'SMTP_SERVER',			'type' => 'text:25:50', 'explain' => false),
						'smtp_port'				=> array('lang' => 'SMTP_PORT',				'type' => 'text:4:5', 'explain' => true),
						'smtp_auth_method'		=> array('lang' => 'SMTP_AUTH_METHOD',		'type' => 'select', 'method' => 'mail_auth_select', 'explain' => true),
						'smtp_username'			=> array('lang' => 'SMTP_USERNAME',			'type' => 'text:25:255', 'explain' => true),
						'smtp_password'			=> array('lang' => 'SMTP_PASSWORD',			'type' => 'password:25:255', 'explain' => true)
					)
				);
			break;

			case 'message':
				$display_vars = array(
					'title'	=> 'ACP_MESSAGE_SETTINGS',
					'lang'	=> 'ucp',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'pm_max_boxes'			=> array('lang' => 'BOXES_MAX',				'type' => 'text:4:4', 'explain' => true),
						'pm_max_msgs'			=> array('lang' => 'BOXES_LIMIT',			'type' => 'text:4:4', 'explain' => true),
						'full_folder_action'	=> array('lang' => 'FULL_FOLDER_ACTION',	'type' => 'select', 'method' => 'full_folder_select', 'explain' => true),
						'pm_edit_time'			=> array('lang' => 'PM_EDIT_TIME',			'type' => 'text:3:3', 'explain' => true),
						
						'legend2'				=> 'GENERAL_OPTIONS',
						'allow_mass_pm'			=> array('lang' => 'ALLOW_MASS_PM',			'type' => 'radio:yes_no', 'explain' => false),
						'auth_html_pm'			=> array('lang' => 'ALLOW_HTML_PM',			'type' => 'radio:yes_no', 'explain' => false),
						'auth_bbcode_pm'		=> array('lang' => 'ALLOW_BBCODE_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'auth_smilies_pm'		=> array('lang' => 'ALLOW_SMILIES_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'allow_pm_attach'		=> array('lang' => 'ALLOW_PM_ATTACHMENTS',	'type' => 'radio:yes_no', 'explain' => false),
						'auth_download_pm'		=> array('lang' => 'ALLOW_DOWNLOAD_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'allow_sig_pm'			=> array('lang' => 'ALLOW_SIG_PM',			'type' => 'radio:yes_no', 'explain' => false),
						'auth_report_pm'		=> array('lang' => 'ALLOW_REPORT_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'auth_quote_pm'			=> array('lang' => 'ALLOW_QUOTE_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'print_pm'				=> array('lang' => 'ALLOW_PRINT_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'email_pm'				=> array('lang' => 'ALLOW_EMAIL_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'forward_pm'			=> array('lang' => 'ALLOW_FORWARD_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'auth_img_pm'			=> array('lang' => 'ALLOW_IMG_PM',			'type' => 'radio:yes_no', 'explain' => false),
						'auth_flash_pm'			=> array('lang' => 'ALLOW_FLASH_PM',		'type' => 'radio:yes_no', 'explain' => false),
						'enable_pm_icons'		=> array('lang' => 'ENABLE_PM_ICONS',		'type' => 'radio:yes_no', 'explain' => false)
					)
				);
			break;
			
			default:
				trigger_error('NO_MODE');
		}

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";
		
		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? request_var('config', array('' => '')) : $this->new_config;

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$config_value = $cfg_array[$config_name];
			$this->new_config[$config_name] = $config_value;

			if ($config_name == 'email_function_name')
			{
				$this->new_config['email_function_name'] = (empty($this->new_config['email_function_name']) || !function_exists($this->new_config['email_function_name'])) ? 'mail' : str_replace(array('(', ')'), array('', ''), trim($this->new_config['email_function_name']));
			}

			if ($submit)
			{
				set_config($config_name, $config_value);
			}
		}

		if ($mode == 'auth')
		{
			// Retrieve a list of auth plugins and check their config values
			$auth_plugins = array();

			$dp = opendir($phpbb_root_path . 'includes/auth');
			while ($file = readdir($dp))
			{
				if (preg_match('#^auth_(.*?)\.' . $phpEx . '$#', $file))
				{
					$auth_plugins[] = preg_replace('#^auth_(.*?)\.' . $phpEx . '$#', '\1', $file);
				}
			}

			sort($auth_plugins);

			foreach ($auth_plugins as $method)
			{
				if ($method && file_exists($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx))
				{
					include_once($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx);

					$method = 'admin_' . $method;
					if (function_exists($method))
					{
						if ($fields = $method($this->new_config))
						{
							// Check if we need to create config fields for this plugin and save config when submit was pressed
							foreach ($fields['config'] as $field)
							{
								if (!isset($config[$field]))
								{
									set_config($field, '');
								}

								if (!isset($cfg_array[$field]) || strpos($field, 'legend') !== false)
								{
									continue;
								}

								$config_value = $cfg_array[$field];
								$this->new_config[$field] = $config_value;

								if ($submit)
								{
									set_config($field, $config_value);
								}
							}
						}
						unset($fields);
					}
				}
			}
		}

		if ($submit)
		{
			add_log('admin', 'LOG_CONFIG_' . strtoupper($mode));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($u_action));
		}

		$this->tpl_name = 'acp_board';
		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],
			'U_ACTION'			=> $u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{

			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> $user->lang[$vars])
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> $user->lang[$vars['lang']],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> ($vars['explain']) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '',
				'CONTENT'		=> build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars),
				)
			);
		
			unset($display_vars['vars'][$config_key]);
		}

		if ($mode == 'auth')
		{
			$template->assign_var('S_AUTH', true);

			foreach ($auth_plugins as $method)
			{
				if ($method && file_exists($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx))
				{
					$method = 'admin_' . $method;
					if (function_exists($method))
					{
						$fields = $method($this->new_config);

						if ($fields['tpl'])
						{
							$template->assign_block_vars('auth_tpl', array(
								'TPL'	=> $fields['tpl'])
							);
						}
						unset($fields);
					}
				}
			}
		}
	}

	function select_auth_method($selected_method, $key = '')
	{
		global $phpbb_root_path, $phpEx;

		$auth_plugins = array();

		$dp = opendir($phpbb_root_path . 'includes/auth');
		while ($file = readdir($dp))
		{
			if (preg_match('#^auth_(.*?)\.' . $phpEx . '$#', $file))
			{
				$auth_plugins[] = preg_replace('#^auth_(.*?)\.' . $phpEx . '$#', '\1', $file);
			}
		}

		sort($auth_plugins);

		$auth_select = '';
		foreach ($auth_plugins as $method)
		{
			$selected = ($selected_method == $method) ? ' selected="selected"' : '';
			$auth_select .= '<option value="' . $method . '"' . $selected . '>' . ucfirst($method) . '</option>';
		}

		return $auth_select;
	}

	function mail_auth_select($selected_method, $key = '')
	{
		global $user;

		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5', 'POP-BEFORE-SMTP');
		$s_smtp_auth_options = '';

		foreach ($auth_methods as $method)
		{
			$s_smtp_auth_options .= '<option value="' . $method . '"' . (($selected_method == $method) ? ' selected="selected"' : '') . '>' . $user->lang['SMTP_' . str_replace('-', '_', $method)] . '</option>';
		}

		return $s_smtp_auth_options;
	}

	function full_folder_select($value, $key = '')
	{
		global $user;

		return '<option value="1"' . (($value == 1) ? ' selected="selected"' : '') . '>' . $user->lang['DELETE_OLDEST_MESSAGES'] . '</option><option value="2"' . (($value == 2) ? ' selected="selected"' : '') . '>' . $user->lang['HOLD_NEW_MESSAGES'] . '</option>';
	}

	function select_ip_check($value, $key = '')
	{
		$radio_ary = array(4 => 'ALL', 3 => 'CLASS_C', 2 => 'CLASS_B', 0 => 'NONE');

		return h_radio('config[ip_check]', $radio_ary, $value, $key);
	}

	function select_acc_activation($value, $key = '')
	{
		global $user, $config;

		$radio_ary = array(USER_ACTIVATION_DISABLE => 'ACC_DISABLE', USER_ACTIVATION_NONE => 'ACC_NONE');
		if ($config['email_enable'])
		{
			$radio_ary += array(USER_ACTIVATION_SELF => 'ACC_USER', USER_ACTIVATION_ADMIN => 'ACC_ADMIN');
		}

		return h_radio('config[require_activation]', $radio_ary, $value, $key);
	}

	function username_length($value, $key = '')
	{
		global $user;

		return '<input id="' . $key . '" type="text" size="3" maxlength="3" name="config[min_name_chars]" value="' . $value . '" /> ' . $user->lang['MIN_CHARS'] . '&nbsp;&nbsp;<input type="text" size="3" maxlength="3" name="config[max_name_chars]" value="' . $this->new_config['max_name_chars'] . '" /> ' . $user->lang['MAX_CHARS'];
	}

	function select_username_chars($selected_value, $key)
	{
		global $user;

		$user_char_ary = array('USERNAME_CHARS_ANY' => '.*', 'USERNAME_ALPHA_ONLY' => '[\w]+', 'USERNAME_ALPHA_SPACERS' => '[\w_\+\. \-\[\]]+');
		$user_char_options = '';
		foreach ($user_char_ary as $lang => $value)
		{
			$selected = ($selected_value == $value) ? ' selected="selected"' : '';
			$user_char_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		return $user_char_options;
	}

	function password_length($value, $key)
	{
		global $user;

		return '<input id="' . $key . '" type="text" size="3" maxlength="3" name="config[min_pass_chars]" value="' . $value . '" /> ' . $user->lang['MIN_CHARS'] . '&nbsp;&nbsp;<input type="text" size="3" maxlength="3" name="config[max_pass_chars]" value="' . $this->new_config['max_pass_chars'] . '" /> ' . $user->lang['MAX_CHARS'];
	}

	function select_password_chars($selected_value, $key)
	{
		global $user;

		$pass_type_ary = array('PASS_TYPE_ANY' => '.*', 'PASS_TYPE_CASE' => '[a-zA-Z]', 'PASS_TYPE_ALPHA' => '[a-zA-Z0-9]', 'PASS_TYPE_SYMBOL' => '[a-zA-Z\W]');
		$pass_char_options = '';
		foreach ($pass_type_ary as $lang => $value)
		{
			$selected = ($selected_value == $value) ? ' selected="selected"' : '';
			$pass_char_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		return $pass_char_options;
	}

	function bump_interval($value, $key)
	{
		global $user;

		$s_bump_type = '';
		$types = array('m' => 'MINUTES', 'h' => 'HOURS', 'd' => 'DAYS');
		foreach ($types as $type => $lang)
		{
			$selected = ($this->new_config['bump_type'] == $type) ? ' selected="selected"' : '';
			$s_bump_type .= '<option value="' . $type . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		return '<input id="' . $key . '" type="text" size="3" maxlength="4" name="config[bump_interval]" value="' . $value . '" />&nbsp;<select name="config[bump_type]">' . $s_bump_type . '</select>';
	}

	function board_disable($value, $key)
	{
		global $user;

		$radio_ary = array(1 => 'YES', 0 => 'NO');

		return h_radio('config[board_disable]', $radio_ary, $value) . '<br /><input id="' . $key . '" type="text" name="config[board_disable_msg]" maxlength="255" size="40" value="' . $this->new_config['board_disable_msg'] . '" />';
	}

	function select_search_type($selected_type, $key = '')
	{
		global $phpbb_root_path, $phpEx;

		$search_plugins = array();

		$dp = opendir($phpbb_root_path . 'includes/search');
		while ($file = readdir($dp))
		{
			if (preg_match('#\.' . $phpEx . '$#', $file))
			{
				$search_plugins[] = preg_replace('#^(.*?)\.' . $phpEx . '$#', '\1', $file);
			}
		}

		sort($search_plugins);

		$search_select = '';
		foreach ($search_plugins as $type)
		{
			$selected = ($selected_type == $type) ? ' selected="selected"' : '';
			$search_select .= '<option value="' . $type . '"' . $selected . '>' . ucfirst(strtolower(str_replace('_', ' ', $type))) . '</option>';
		}

		return $search_select;
	}
}

/**
* @package module_install
*/
class acp_board_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_board',
			'title'		=> 'ACP_BOARD_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'auth'		=> array('title' => 'ACP_AUTH_SETTINGS', 'auth' => 'acl_a_server'),
				'avatar'	=> array('title' => 'ACP_AVATAR_SETTINGS', 'auth' => 'acl_a_board'),
				'default'	=> array('title' => 'ACP_BOARD_DEFAULTS', 'auth' => 'acl_a_defaults'),
				'settings'	=> array('title' => 'ACP_BOARD_SETTINGS', 'auth' => 'acl_a_board'),
				'cookie'	=> array('title' => 'ACP_COOKIE_SETTINGS', 'auth' => 'acl_a_cookies'),
				'email'		=> array('title' => 'ACP_EMAIL_SETTINGS', 'auth' => 'acl_a_server'),
				'load'		=> array('title' => 'ACP_LOAD_SETTINGS', 'auth' => 'acl_a_server'),
				'server'	=> array('title' => 'ACP_SERVER_SETTINGS', 'auth' => 'acl_a_server'),
				'message'	=> array('title' => 'ACP_MESSAGE_SETTINGS', 'auth' => 'acl_a_defaults'),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>