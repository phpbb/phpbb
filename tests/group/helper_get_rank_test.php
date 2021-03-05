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

class phpbb_group_helper_get_rank_test extends phpbb_group_helper_test_case
{
	public function get_rank_data()
	{
		global $phpbb_root_path;

		return array(
			array(
				array('group_id' => 0, 'group_rank' => 1),
				array(
					'title' => 'Site admin',
					'img' => '<img src="' . $phpbb_root_path . 'images/ranks/siteadmin.png' . '" alt="Site admin" title="Site admin" />',
					'img_src' => $phpbb_root_path . 'images/ranks/siteadmin.png',
				)
			),
			array(array('group_id' => 1, 'group_rank' => 0), array('title' => null, 'img' => null, 'img_src' => null)),
			array(array('group_id' => 2, 'group_rank' => 2), array('title' => 'Test member', 'img' => '', 'img_src' => '')),
		);
	}

	/**
	 * @dataProvider get_rank_data
	 */
	public function test_get_rank($group_data, $expected)
	{
		$this->assertEquals($expected, $this->group_helper->get_rank($group_data));
	}
}
