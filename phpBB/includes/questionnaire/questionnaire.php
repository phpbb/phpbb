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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* This class collects data which is used to create some usage statistics.
*
* The collected data is - after authorization of the administrator - submitted
* to a central server. For privacy reasons we try to collect only data which aren't private
* or don't give any information which might help to identify the user.
*
* @author		Johannes Schlueter <johannes@php.net>
* @copyright	(c) 2007-2008 Johannes Schlueter
*/
class phpbb_questionnaire_data_collector
{
	var $providers;
	var $data = null;
	var $install_id = '';

	/**
	* Constructor.
	*
	* @param	string
	*/
	function __construct($install_id)
	{
		$this->install_id = $install_id;
		$this->providers = array();
	}

	function add_data_provider($provider)
	{
		$this->providers[] = $provider;
	}

	/**
	* Get data as an array.
	*
	* @return	array	All Data
	*/
	function get_data_raw()
	{
		if (!$this->data)
		{
			$this->collect();
		}

		return $this->data;
	}

	function get_data_for_form()
	{
		return base64_encode(serialize($this->get_data_raw()));
	}

	/**
	* Collect info into the data property.
	*
	* @return	null
	*/
	function collect()
	{
		foreach (array_keys($this->providers) as $key)
		{
			$provider = $this->providers[$key];
			$this->data[$provider->get_identifier()] = $provider->get_data();
		}
		$this->data['install_id'] = $this->install_id;
	}
}

/** interface: get_indentifier(), get_data() */

/**
* Questionnaire PHP data provider
*/
class phpbb_questionnaire_php_data_provider
{
	function get_identifier()
	{
		return 'PHP';
	}

	/**
	* Get data about the PHP runtime setup.
	*
	* @return	array
	*/
	function get_data()
	{
		return array(
			'version'						=> PHP_VERSION,
			'sapi'							=> PHP_SAPI,
			'int_size'						=> defined('PHP_INT_SIZE') ? PHP_INT_SIZE : '',
			'safe_mode'						=> (int) @ini_get('safe_mode'),
			'open_basedir'					=> (int) @ini_get('open_basedir'),
			'memory_limit'					=> @ini_get('memory_limit'),
			'allow_url_fopen'				=> (int) @ini_get('allow_url_fopen'),
			'allow_url_include'				=> (int) @ini_get('allow_url_include'),
			'file_uploads'					=> (int) @ini_get('file_uploads'),
			'upload_max_filesize'			=> @ini_get('upload_max_filesize'),
			'post_max_size'					=> @ini_get('post_max_size'),
			'disable_functions'				=> @ini_get('disable_functions'),
			'disable_classes'				=> @ini_get('disable_classes'),
			'enable_dl'						=> (int) @ini_get('enable_dl'),
			'magic_quotes_gpc'				=> (int) @ini_get('magic_quotes_gpc'),
			'register_globals'				=> (int) @ini_get('register_globals'),
			'filter.default'				=> @ini_get('filter.default'),
			'zend.ze1_compatibility_mode'	=> (int) @ini_get('zend.ze1_compatibility_mode'),
			'unicode.semantics'				=> (int) @ini_get('unicode.semantics'),
			'zend_thread_safty'				=> (int) function_exists('zend_thread_id'),
			'extensions'					=> get_loaded_extensions(),
		);
	}
}

/**
* Questionnaire System data provider
*/
class phpbb_questionnaire_system_data_provider
{
	function get_identifier()
	{
		return 'System';
	}

	/**
	* Get data about the general system information, like OS or IP (shortened).
	*
	* @return	array
	*/
	function get_data()
	{
		global $request;

		// Start discovering the IPV4 server address, if available
		// Try apache, IIS, fall back to 0.0.0.0
		$server_address = htmlspecialchars_decode($request->server('SERVER_ADDR', $request->server('LOCAL_ADDR', '0.0.0.0')));

		return array(
			'os'	=> PHP_OS,
			'httpd'	=> htmlspecialchars_decode($request->server('SERVER_SOFTWARE')),
			// we don't want the real IP address (for privacy policy reasons) but only
			// a network address to see whether your installation is running on a private or public network.
			'private_ip'	=> $this->is_private_ip($server_address),
			'ipv6'			=> strpos($server_address, ':') !== false,
		);
	}

	/**
	* Checks whether the given IP is in a private network.
	*
	* @param	string	$ip	IP in v4 dot-decimal or v6 hex format
	* @return	bool		true if the IP is from a private network, else false
	*/
	function is_private_ip($ip)
	{
		// IPv4
		if (strpos($ip, ':') === false)
		{
			$ip_address_ary = explode('.', $ip);

			// build ip
			if (!isset($ip_address_ary[0]) || !isset($ip_address_ary[1]))
			{
				$ip_address_ary = explode('.', '0.0.0.0');
			}

			// IANA reserved addresses for private networks (RFC 1918) are:
			// - 10.0.0.0/8
			// - 172.16.0.0/12
			// - 192.168.0.0/16
			if ($ip_address_ary[0] == '10' ||
				($ip_address_ary[0] == '172' && intval($ip_address_ary[1]) > 15 && intval($ip_address_ary[1]) < 32) ||
				($ip_address_ary[0] == '192' && $ip_address_ary[1] == '168'))
			{
				return true;
			}
		}
		// IPv6
		else
		{
			// unique local unicast
			$prefix = substr($ip, 0, 2);
			if ($prefix == 'fc' || $prefix == 'fd')
			{
				return true;
			}
		}

		return false;
	}
}

/**
* Questionnaire phpBB data provider
*/
class phpbb_questionnaire_phpbb_data_provider
{
	var $config;
	var $unique_id;

	/**
	* Constructor.
	*
	* @param	array	$config
	*/
	function __construct($config)
	{
		// generate a unique id if necessary
		if (empty($config['questionnaire_unique_id']))
		{
			$this->unique_id = unique_id();
			$config->set('questionnaire_unique_id', $this->unique_id);
		}
		else
		{
			$this->unique_id = $config['questionnaire_unique_id'];
		}

		$this->config = $config;
	}

	/**
	* Returns a string identifier for this data provider
	*
	* @return	string	"phpBB"
	*/
	function get_identifier()
	{
		return 'phpBB';
	}

	/**
	* Get data about this phpBB installation.
	*
	* @return	array	Relevant anonymous config options
	*/
	function get_data()
	{
		global $phpbb_config_php_file;

		extract($phpbb_config_php_file->get_all());
		unset($dbhost, $dbport, $dbname, $dbuser, $dbpasswd); // Just a precaution

		$dbms = $phpbb_config_php_file->convert_30_dbms_to_31($dbms);

		// Only send certain config vars
		$config_vars = array(
			'active_sessions' => true,
			'allow_attachments' => true,
			'allow_autologin' => true,
			'allow_avatar' => true,
			'allow_avatar_local' => true,
			'allow_avatar_remote' => true,
			'allow_avatar_upload' => true,
			'allow_bbcode' => true,
			'allow_birthdays' => true,
			'allow_bookmarks' => true,
			'allow_emailreuse' => true,
			'allow_forum_notify' => true,
			'allow_mass_pm' => true,
			'allow_name_chars' => true,
			'allow_namechange' => true,
			'allow_nocensors' => true,
			'allow_pm_attach' => true,
			'allow_pm_report' => true,
			'allow_post_flash' => true,
			'allow_post_links' => true,
			'allow_privmsg' => true,
			'allow_quick_reply' => true,
			'allow_sig' => true,
			'allow_sig_bbcode' => true,
			'allow_sig_flash' => true,
			'allow_sig_img' => true,
			'allow_sig_links' => true,
			'allow_sig_pm' => true,
			'allow_sig_smilies' => true,
			'allow_smilies' => true,
			'allow_topic_notify' => true,
			'attachment_quota' => true,
			'auth_bbcode_pm' => true,
			'auth_flash_pm' => true,
			'auth_img_pm' => true,
			'auth_method' => true,
			'auth_smilies_pm' => true,
			'avatar_filesize' => true,
			'avatar_max_height' => true,
			'avatar_max_width' => true,
			'avatar_min_height' => true,
			'avatar_min_width' => true,
			'board_email_form' => true,
			'board_hide_emails' => true,
			'board_timezone' => true,
			'browser_check' => true,
			'bump_interval' => true,
			'bump_type' => true,
			'cache_gc' => true,
			'captcha_plugin' => true,
			'captcha_gd' => true,
			'captcha_gd_foreground_noise' => true,
			'captcha_gd_x_grid' => true,
			'captcha_gd_y_grid' => true,
			'captcha_gd_wave' => true,
			'captcha_gd_3d_noise' => true,
			'captcha_gd_fonts' => true,
			'confirm_refresh' => true,
			'check_attachment_content' => true,
			'check_dnsbl' => true,
			'chg_passforce' => true,
			'cookie_secure' => true,
			'coppa_enable' => true,
			'database_gc' => true,
			'dbms_version' => true,
			'default_dateformat' => true,
			'default_lang' => true,
			'display_last_edited' => true,
			'display_order' => true,
			'edit_time' => true,
			'email_check_mx' => true,
			'email_enable' => true,
			'email_force_sender' => true,
			'email_package_size' => true,
			'enable_confirm' => true,
			'enable_pm_icons' => true,
			'enable_post_confirm' => true,
			'feed_enable' => true,
			'feed_http_auth' => true,
			'feed_limit_post' => true,
			'feed_limit_topic' => true,
			'feed_overall' => true,
			'feed_overall_forums' => true,
			'feed_forum' => true,
			'feed_topic' => true,
			'feed_topics_new' => true,
			'feed_topics_active' => true,
			'feed_item_statistics' => true,
			'flood_interval' => true,
			'force_server_vars' => true,
			'form_token_lifetime' => true,
			'form_token_mintime' => true,
			'form_token_sid_guests' => true,
			'forward_pm' => true,
			'forwarded_for_check' => true,
			'full_folder_action' => true,
			'fulltext_native_common_thres' => true,
			'fulltext_native_load_upd' => true,
			'fulltext_native_max_chars' => true,
			'fulltext_native_min_chars' => true,
			'gzip_compress' => true,
			'hot_threshold' => true,
			'img_create_thumbnail' => true,
			'img_display_inlined' => true,
			'img_link_height' => true,
			'img_link_width' => true,
			'img_max_height' => true,
			'img_max_thumb_width' => true,
			'img_max_width' => true,
			'img_min_thumb_filesize' => true,
			'ip_check' => true,
			'jab_enable' => true,
			'jab_package_size' => true,
			'jab_use_ssl' => true,
			'limit_load' => true,
			'limit_search_load' => true,
			'load_anon_lastread' => true,
			'load_birthdays' => true,
			'load_cpf_memberlist' => true,
			'load_cpf_viewprofile' => true,
			'load_cpf_viewtopic' => true,
			'load_db_lastread' => true,
			'load_db_track' => true,
			'load_jumpbox' => true,
			'load_moderators' => true,
			'load_online' => true,
			'load_online_guests' => true,
			'load_online_time' => true,
			'load_onlinetrack' => true,
			'load_search' => true,
			'load_tplcompile' => true,
			'load_user_activity' => true,
			'max_attachments' => true,
			'max_attachments_pm' => true,
			'max_autologin_time' => true,
			'max_filesize' => true,
			'max_filesize_pm' => true,
			'max_login_attempts' => true,
			'max_name_chars' => true,
			'max_num_search_keywords' => true,
			'max_pass_chars' => true,
			'max_poll_options' => true,
			'max_post_chars' => true,
			'max_post_font_size' => true,
			'max_post_img_height' => true,
			'max_post_img_width' => true,
			'max_post_smilies' => true,
			'max_post_urls' => true,
			'max_quote_depth' => true,
			'max_reg_attempts' => true,
			'max_sig_chars' => true,
			'max_sig_font_size' => true,
			'max_sig_img_height' => true,
			'max_sig_img_width' => true,
			'max_sig_smilies' => true,
			'max_sig_urls' => true,
			'min_name_chars' => true,
			'min_pass_chars' => true,
			'min_post_chars' => true,
			'min_search_author_chars' => true,
			'mime_triggers' => true,
			'new_member_post_limit' => true,
			'new_member_group_default' => true,
			'override_user_style' => true,
			'pass_complex' => true,
			'pm_edit_time' => true,
			'pm_max_boxes' => true,
			'pm_max_msgs' => true,
			'pm_max_recipients' => true,
			'posts_per_page' => true,
			'print_pm' => true,
			'queue_interval' => true,
			'require_activation' => true,
			'referer_validation' => true,
			'search_block_size' => true,
			'search_gc' => true,
			'search_interval' => true,
			'search_anonymous_interval' => true,
			'search_type' => true,
			'search_store_results' => true,
			'secure_allow_deny' => true,
			'secure_allow_empty_referer' => true,
			'secure_downloads' => true,
			'session_gc' => true,
			'session_length' => true,
			'smtp_auth_method' => true,
			'smtp_delivery' => true,
			'topics_per_page' => true,
			'tpl_allow_php' => true,
			'version' => true,
			'warnings_expire_days' => true,
			'warnings_gc' => true,

			'num_files' => true,
			'num_posts' => true,
			'num_topics' => true,
			'num_users' => true,
			'record_online_users' => true,
		);

		$result = array();
		foreach ($config_vars as $name => $void)
		{
			if (isset($this->config[$name]))
			{
				$result['config_' . $name] = $this->config[$name];
			}
		}

		global $db, $request;

		$result['dbms'] = $dbms;
		$result['acm_type'] = $acm_type;
		$result['user_agent'] = 'Unknown';
		$result['dbms_version'] = $db->sql_server_info(true);

		// Try to get user agent vendor and version
		$match = array();
		$user_agent = $request->header('User-Agent');
		$agents = array('firefox', 'msie', 'opera', 'chrome', 'safari', 'mozilla', 'seamonkey', 'konqueror', 'netscape', 'gecko', 'navigator', 'mosaic', 'lynx', 'amaya', 'omniweb', 'avant', 'camino', 'flock', 'aol');

		// We check here 1 by 1 because some strings occur after others (for example Mozilla [...] Firefox/)
		foreach ($agents as $agent)
		{
			if (preg_match('#(' . $agent . ')[/ ]?([0-9.]*)#i', $user_agent, $match))
			{
				$result['user_agent'] = $match[1] . ' ' . $match[2];
				break;
			}
		}

		return $result;
	}
}
