<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/testable_factory.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/session.php';

/**
 * This class exists to expose session.php's functions in a more testable way.
 *
 * Since many functions in session.php have global variables inside the function,
 * this exposes those functions through a testable facade that uses testable_factory's
 * mock global variables to modify global variables used in the functions.
 *
 * This is using the facade pattern to provide a testable "front" to the functions in sessions.php.
 *
 */
class phpbb_session_testable_facade
{
	public static function extract_current_page($db, $session_factory, $root_path, $php_self, $query_string, $request_uri) {
		$session_factory->get_session($db);
		global $request;
		$request->overwrite('PHP_SELF', $php_self, phpbb_request_interface::SERVER);
		$request->overwrite('QUERY_STRING', $query_string, phpbb_request_interface::SERVER);
		$request->overwrite('REQUEST_URI', $request_uri, phpbb_request_interface::SERVER);
		return phpbb_session::extract_current_page($root_path);
	}

	public static function extract_current_hostname($db, $session_factory, $host, $server_name_config, $cookie_domain_config) {
		$session = $session_factory->get_session($db);
		global $config, $request;
		$config['server_name'] = $server_name_config;
		$config['cookie_domain'] = $cookie_domain_config;
		$request->overwrite('SERVER_NAME', $host, phpbb_request_interface::SERVER);
		$request->overwrite('Host', $host, phpbb_request_interface::SERVER);
		// Note: There is a php_uname fallthrough in this method that this function doesn't override
		return $session->extract_current_hostname();
	}
	// [To be completed]
	// public static function session_begin($update_session_page = true) {}
	// public static function session_create($user_id = false, $set_admin = false, $persist_login = false, $viewonline = true) {}
	// public static function session_kill($new_session = true) {}
	// public static function session_gc() {}
	// public static function set_cookie($name, $cookiedata, $cookietime) {}
	// public static function check_ban($user_id = false, $user_ips = false, $user_email = false, $return = false) {}
	// public static function check_dnsbl($mode, $ip = false) {}
	// public static function set_login_key($user_id = false, $key = false, $user_ip = false) {}
	// public static function reset_login_keys($user_id = false) {}
	// public static function validate_referer($check_script_path = false) {}
	// public static function update_session($session_data, $session_id = null) {}
	// public static function unset_admin() {}
}

