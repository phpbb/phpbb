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
 * Mock auth provider class with basic functions to help test sessions.
 */
class phpbb_mock_auth_provider extends \phpbb\auth\provider\base
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
