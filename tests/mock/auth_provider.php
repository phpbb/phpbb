<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * Mock auth provider class with basic functions to help test sessions.
 */
class phpbb_mock_auth_provider implements phpbb_auth_provider_interface
{
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

	function acp()
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

	public function get_acp_template($new_config)
	{
		return null;
	}
}
