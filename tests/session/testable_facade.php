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

require_once dirname(__FILE__) . '/testable_factory.php';
require_once dirname(__FILE__) . '/../../phpBB/phpbb/session.php';

/**
 * This class exists to expose session.php's functions in a more testable way.
 *
 * Since many functions in session.php have global variables inside the function,
 * this exposes those functions through a testable facade that uses
 * testable_factory's mock global variables to modify global variables used in
 * the functions.
 *
 * This is using the facade pattern to provide a testable "front" to the
 * functions in sessions.php.
 *
 */
class phpbb_session_testable_facade
{
	protected $db;
	protected $session_factory;

	function __construct($db, $session_factory)
	{
		$this->db = $db;
		$this->session_factory = $session_factory;
	}

	function extract_current_hostname(
		$host,
		$server_name_config,
		$cookie_domain_config
	)
	{
		$session = $this->session_factory->get_session($this->db);
		global $config, $request;
		$config['server_name'] = $server_name_config;
		$config['cookie_domain'] = $cookie_domain_config;
		$request->overwrite('SERVER_NAME', $host, \phpbb\request\request_interface::SERVER);
		$request->overwrite('Host', $host, \phpbb\request\request_interface::SERVER);
		// Note: There is a php_uname function used as a fallthrough
		//       that this function doesn't override
		return $session->extract_current_hostname();
	}

	/**
	 *
	 * This function has a lot of dependencies, so instead of naming them all,
	 * just ask for overrides
	 *
	 * @param update_session_page Boolean of whether to set page of the session
	 * @param config_overrides An array of overrides for the global config object
	 * @param request_overrides An array of overrides for the global request object
	 * @return boolean False if the user is identified, otherwise true.
	 */
	function session_begin(
		$update_session_page = true,
		$config_overrides = array(),
		$request_overrides = array(),
		$cookies_overrides = array()
	)
	{
		$this->session_factory->merge_config_data($config_overrides);
		$this->session_factory->merge_server_data($request_overrides);
		$this->session_factory->set_cookies($cookies_overrides);
		$session = $this->session_factory->get_session($this->db);
		$session->session_begin($update_session_page);
		return $session;
	}

	function session_create(
		$user_id = false,
		$set_admin = false,
		$persist_login = false,
		$viewonline = true,
		array $config_overrides = array(),
		$user_agent = 'user agent',
		$ip_address = '127.0.0.1',
		array $bot_overrides = array(),
		$uri_sid = ""
	)
	{
		$this->session_factory->merge_config_data($config_overrides);
		// Bots
		$this->session_factory->merge_cache_data(array('_bots' => $bot_overrides));
		global $request;
		$session = $this->session_factory->get_session($this->db);
		$session->browser = $user_agent;
		$session->ip = $ip_address;
		// Uri sid
		if ($uri_sid)
		{
			$_GET['sid'] = $uri_sid;
		}
		$session->session_create($user_id, $set_admin, $persist_login, $viewonline);
		return $session;
	}

	function validate_referer(
		$check_script_path,
		$referer,
		$host,
		$force_server_vars,
		$server_port,
		$server_name,
		$root_script_path
	)
	{
		$session = $this->session_factory->get_session($this->db);
		global $config, $request;
		$session->referer = $referer;
		$session->page['root_script_path'] = $root_script_path;
		$session->host = $host;
		$config['force_server_vars'] = $force_server_vars;
		$config['server_name'] = $server_name;
		$request->overwrite('SERVER_PORT', $server_port, \phpbb\request\request_interface::SERVER);
		return $session->validate_referer($check_script_path);
	}
}
