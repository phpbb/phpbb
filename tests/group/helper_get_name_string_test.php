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

require_once __DIR__ . '/helper_test_case.php';

class phpbb_group_helper_get_name_string_test extends phpbb_group_helper_test_case
{

	public function get_name_string_profile_data()
	{
		global $phpbb_root_path, $phpEx;

		return array(
			array(0, 'Non existing group', '', false, ''),
			array(2, 'Administrators', 'AA0000', false, "{$phpbb_root_path}memberlist.$phpEx?mode=group&amp;g=2"),
			array(42, 'Example Group', '', 'http://www.example.org/group.php?mode=show', 'http://www.example.org/group.php?mode=show&amp;g=42'),
		);
	}

	/**
	* @dataProvider get_name_string_profile_data
	*/
	public function test_get_name_string_profile($group_id, $group_name, $group_colour, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, $this->group_helper->get_name_string('profile', $group_id, $group_name, $group_colour, $custom_profile_url));
	}

	public function get_name_string_group_name_data()
	{
		return array(
			// Should be fine
			array(0, 'BOTS', 'AA0000', false, 'Bots'),
			array(1, 'new_group', '', false, 'Some new group'),
			array(2, 'group_with_ümlauts', '', 'http://www.example.org/group.php?mode=show', 'Should work'),

			// Should fail and thus return the same
			array(3, 'not_uppercase', 'FFFFFF', false, 'not_uppercase'),
			array(4, 'Awesome group', '', false, 'Awesome group'),
		);
	}

	/**
	 * @dataProvider get_name_string_group_name_data
	 */
	public function test_get_name_string_group_name($group_id, $group_name, $group_colour, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, $this->group_helper->get_name_string('group_name', $group_id, $group_name, $group_colour, $custom_profile_url));
	}

	public function get_name_string_colour_data()
	{
		return array(
			array(0, '', '', false, ''),
			array(0, '', 'F0F0F0', false, '#F0F0F0'),
			array(1, 'Guests', '000000', false, '#000000'),
			array(2, 'Administrators', '', false, ''),
		);
	}

	/**
	 * @dataProvider get_name_string_colour_data
	 */
	public function test_get_name_string_colour($group_id, $group_name, $group_colour, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, $this->group_helper->get_name_string('colour', $group_id, $group_name, $group_colour, $custom_profile_url));
	}

	public function get_name_string_full_data()
	{
		global $phpbb_root_path, $phpEx;

		return array(
			array(0, 'BOTS', '000000', false, '<span class="username-coloured" style="color: #000000;">Bots</span>'),
			array(1, 'BOTS', '111111', false, '<span class="username-coloured" style="color: #111111;">Bots</span>'),
			array(7, 'new_group', 'FFA500', false, '<a class="username-coloured" href="' . $phpbb_root_path . 'memberlist.' . $phpEx . '?mode=group&amp;g=7" style="color: #FFA500;">Some new group</a>'),
			array(14, 'Awesome group', '', 'http://www.example.org/group.php?mode=show', '<a class="username" href="http://www.example.org/group.php?mode=show&amp;g=14">Awesome group</a>'),
		);
	}

	/**
	 * @dataProvider get_name_string_full_data
	 */
	public function test_get_name_string_full($group_id, $group_name, $group_colour, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, $this->group_helper->get_name_string('full', $group_id, $group_name, $group_colour, $custom_profile_url));
	}

	public function get_name_string_no_profile_data()
	{
		return array(
			array(0, 'BOTS', '000000', false, '<span class="username-coloured" style="color: #000000;">Bots</span>'),
			array(1, 'new_group', '', false, '<span class="username">Some new group</span>'),
			array(2, 'not_uppercase', 'FF0000', false, '<span class="username-coloured" style="color: #FF0000;">not_uppercase</span>'),
			array(5, 'Awesome group', '', 'http://www.example.org/group.php?mode=show', '<span class="username">Awesome group</span>'),
		);
	}

	/**
	 * @dataProvider get_name_string_no_profile_data
	 */
	public function test_get_name_string_no_profile($group_id, $group_name, $group_colour, $custom_profile_url, $expected)
	{
		$this->assertEquals($expected, $this->group_helper->get_name_string('no_profile', $group_id, $group_name, $group_colour, $custom_profile_url));
	}
}
