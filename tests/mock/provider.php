<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * Mock provider class with basic functions to help test
 * sessions.
 *
 * See interface here:
 *    includes/auth/provider/interface.php
 */
class phpbb_provider {

	function init()
	{
		return null;
	}

	function login($username, $password)
	{
		return array(
			 'status' => "",
			 'error_msg' => "",
			 'user_row' => "",
			);
	}

	function autologin()
	{
		return array();
	}

	function acp($new)
	{
		return array();
	}

	function logout($data, $new_session)
	{
		return null;
	}

	function validate_session($user)
	{
		return null;
	}
}
