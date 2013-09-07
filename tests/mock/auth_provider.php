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
class phpbb_mock_auth_provider extends phpbb_auth_provider_base
{
	public function login($username, $password)
	{
		return array(
			'status' => "",
			'error_msg' => "",
			'user_row' => "",
		);
	}
}
