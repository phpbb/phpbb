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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';

class phpbb_functions_content_get_username_string_test extends phpbb_test_case
{
	public function setUp()
	{
		parent::setUp();

		global $auth, $phpbb_dispatcher, $user;
		$auth = $this->getMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValueMap(array(
				array('u_viewprofile', true),
			)));
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$user->data['user_id'] = ANONYMOUS;
		$user->lang['GUEST'] = 'Guest';
	}

	public function get_username_string_profile_data()
	{
		global $phpbb_root_path, $phpEx;

		return array(
			array(ANONYMOUS, 'Anonymous', '', false, false, ''),
			array(2, 'Administrator', 'FF0000', false, false, "{$phpbb_root_path}memberlist.$phpEx?mode=viewprofile&amp;u=2"),
			array(42, 'User42', '', false, 'http://www.example.org/user.php?mode=show', 'http://www.example.org/user.php?mode=show&amp;u=42'),
		);
	}

	/**
	* @dataProvider get_username_string_profile_data
	*/
	public function test_get_username_string_profile($user_id, $username, $user_colour, $guest_username, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, get_username_string('profile', $user_id, $username, $user_colour, $guest_username, $custom_profile_url));
	}

	public function get_username_string_username_data()
	{
		return array(
			array(ANONYMOUS, '', '', false, false, 'Guest'),
			array(ANONYMOUS, '', '', 'CustomName', false, 'CustomName'),
			array(2, 'User2', '', false, false, 'User2'),
			array(5, 'User5', '', 'Anonymous', false, 'User5'),
			array(128, 'User128', '', false, false, 'User128'),
		);
	}

	/**
	* @dataProvider get_username_string_username_data
	*/
	public function test_get_username_string_username($user_id, $username, $user_colour, $guest_username, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, get_username_string('username', $user_id, $username, $user_colour, $guest_username, $custom_profile_url));
	}

	public function get_username_string_colour_data()
	{
		return array(
			array(0, '', '', false, false, ''),
			array(0, '', 'F0F0F0', false, false, '#F0F0F0'),
			array(ANONYMOUS, 'Anonymous', '000000', false, false, '#000000'),
			array(2, 'Administrator', '', false, false, ''),
		);
	}

	/**
	* @dataProvider get_username_string_colour_data
	*/
	public function test_get_username_string_colour($user_id, $username, $user_colour, $guest_username, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, get_username_string('colour', $user_id, $username, $user_colour, $guest_username, $custom_profile_url));
	}

	public function get_username_string_full_data()
	{
		global $phpbb_root_path, $phpEx;

		return array(
			array(0, '', '', false, false, '<span class="username">Guest</span>'),
			array(ANONYMOUS, 'Anonymous', '', false, false, '<span class="username">Anonymous</span>'),
			array(2, 'Administrator', 'FF0000', false, false, '<a href="' . $phpbb_root_path . 'memberlist.' . $phpEx . '?mode=viewprofile&amp;u=2" style="color: #FF0000;" class="username-coloured">Administrator</a>'),
			array(5, 'User5', '', false, 'http://www.example.org/user.php?mode=show', '<a href="http://www.example.org/user.php?mode=show&amp;u=5" class="username">User5</a>'),
			array(8, 'Eight', '', false, false, '<a href="' . $phpbb_root_path . 'memberlist.php?mode=viewprofile&amp;u=8" class="username">Eight</a>'),
		);
	}

	/**
	* @dataProvider get_username_string_full_data
	*/
	public function test_get_username_string_full($user_id, $username, $user_colour, $guest_username, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, get_username_string('full', $user_id, $username, $user_colour, $guest_username, $custom_profile_url));
	}

	public function get_username_string_no_profile_data()
	{
		return array(
			array(ANONYMOUS, 'Anonymous', '', false, false, '<span class="username">Anonymous</span>'),
			array(ANONYMOUS, 'Anonymous', '', '', false, '<span class="username">Guest</span>'),
			array(2, 'Administrator', 'FF0000', false, false, '<span style="color: #FF0000;" class="username-coloured">Administrator</span>'),
			array(8, 'Eight', '', false, false, '<span class="username">Eight</span>'),
		);
	}

	/**
	* @dataProvider get_username_string_no_profile_data
	*/
	public function test_get_username_string_no_profile($user_id, $username, $user_colour, $guest_username, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, get_username_string('no_profile', $user_id, $username, $user_colour, $guest_username, $custom_profile_url));
	}
}
