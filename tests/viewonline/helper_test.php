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

class phpbb_viewonline_helper_test extends phpbb_test_case
{
	public function setUp()
	{
		parent::setUp();

		$this->viewonline_helper = new \phpbb\viewonline_helper(new \phpbb\filesystem\filesystem());
	}

	public function session_pages_data()
	{
		return array(
			array('index.php', 'index'),
			array('foobar/test.php', 'foobar/test'),
			array('', ''),
			array('./../../index.php', '../../index'),
			array('../subdir/index.php', '../subdir/index'),
			array('../index.php', '../index'),
			array('././index.php', 'index'),
			array('./index.php', 'index'),
		);
	}

	/**
	* @dataProvider session_pages_data
	*/
	public function test_get_user_page($session_page, $expected)
	{
		$on_page = $this->viewonline_helper->get_user_page($session_page);
		$this->assertArrayHasKey(1, $on_page);
		$this->assertSame($expected, $on_page[1]);
	}
}
