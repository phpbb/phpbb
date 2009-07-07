<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* @author      Johannes Schlueter <johannes@php.net>
* @copyright   (c) 2007-2008 Johannes Schlueter
*/
class phpbb_questionnaire_data_collector
{
	var $providers;
	var $data = null;

	/**
	* Constructor.
	*
	* @param   array
	* @param   string
	*/
	function phpbb_questionnaire_data_collector()
	{
		$this->providers = array();
	}

	function add_data_provider(&$provider)
	{
		$this->providers[] = &$provider;
	}

	/**
	* Get data as an array.
	*
	* @return  array All Data
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
	* @return  void
	*/
	function collect()
	{
		foreach (array_keys($this->providers) as $key)
		{
			$provider = &$this->providers[$key];
			$this->data[$provider->get_identifier()] = $provider->get_data();
		}
	}
}

/** interface: get_indentifier(), get_data() */

/**
* Questionnaire PHP data provider
* @package phpBB3
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
	* @return  array
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
* @package phpBB3
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
	* @return  array
	*/
	function get_data()
	{
		// Start discovering the IPV4 server address, if available
		$server_address = '0.0.0.0';

		if (!empty($_SERVER['SERVER_ADDR']))
		{
			$server_address = $_SERVER['SERVER_ADDR'];
		}

		// Running on IIS?
		if (!empty($_SERVER['LOCAL_ADDR']))
		{
			$server_address = $_SERVER['LOCAL_ADDR'];
		}

		$ip_address_ary = explode('.', $server_address);

		// build ip
		if (!isset($ip_address_ary[0]) || !isset($ip_address_ary[1]))
		{
			$ip_address_ary = explode('.', '0.0.0.0');
		}

		return array(
			'os'	=> PHP_OS,
			'httpd'	=> $_SERVER['SERVER_SOFTWARE'],
			// we don't want the real IP address (for privacy policy reasons) but only
			// a network address to see whether your installation is running on a private or public network.
			// IANA reserved addresses for private networks (RFC 1918) are:
			// - 10.0.0.0/8
			// - 172.16.0.0/12
			// - 192.168.0.0/16
			'ip'	=> $ip_address_ary[0] . '.' . $ip_address_ary[1] . '.XXX.YYY',
		);
	}
}

/**
* Questionnaire phpBB data provider
* @package phpBB3
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
	function phpbb_questionnaire_phpbb_data_provider($config)
	{
		// generate a unique id if necessary
		if (empty($config['questionnaire_unique_id']))
		{
			$this->unique_id = unique_id();
			set_config('questionnaire_unique_id', $this->unique_id);
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
		// Exclude certain config vars
		$exclude_config_vars = array(
			'avatar_gallery_path'	=> true,
			'avatar_path'			=> true,
			'avatar_salt'			=> true,
			'board_contact'			=> true,
			'board_disable_msg'		=> true,
			'board_email'			=> true,
			'board_email_sig'		=> true,
			'cookie_name'			=> true,
			'icons_path'			=> true,
			'icons_path'			=> true,
			'jab_host'				=> true,
			'jab_password'			=> true,
			'jab_port'				=> true,
			'jab_username'			=> true,
			'ldap_base_dn'			=> true,
			'ldap_email'			=> true,
			'ldap_password'			=> true,
			'ldap_port'				=> true,
			'ldap_server'			=> true,
			'ldap_uid'				=> true,
			'ldap_user'				=> true,
			'ldap_user_filter'		=> true,
			'ranks_path'			=> true,
			'script_path'			=> true,
			'server_name'			=> true,
			'server_port'			=> true,
			'server_protocol'		=> true,
			'site_desc'				=> true,
			'sitename'				=> true,
			'smilies_path'			=> true,
			'smtp_host'				=> true,
			'smtp_password'			=> true,
			'smtp_port'				=> true,
			'smtp_username'			=> true,
			'upload_icons_path'		=> true,
			'upload_path'			=> true,
			'newest_user_colour'	=> true,
			'newest_user_id'		=> true,
			'newest_username'		=> true,
			'rand_seed'				=> true,
		);

		$result = array();
		foreach ($this->config as $name => $value)
		{
			// Mods may add columns for storing passwords - we do not want to grab them
			if (isset($exclude_config_vars[$name]) || strpos($name, 'password') !== false)
			{
				continue;
			}

			$result['config.' . $name] = $value;
		}

		return $result;
	}
}

?>