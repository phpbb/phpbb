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

		$this->viewonline_helper = new \phpbb\viewonline_helper();
	}

	public function session_pages_data()
	{
		return array(
			array('index.php', 'index.php'),
			array('foobar/test.php', 'foobar/test.php'),
			array('', ''),
			array('../index.php', '../index.php'),
		);
	}

	/**
	* @dataProvider session_pages_data
	*/
	public function test_get_user_page($expected, $session_page)
	{
		$on_page = $this->viewonline_helper->get_user_page($session_page);
		$this->assertArrayHasKey(1, $on_page);
		$this->assertSame($expected, $on_page[1]);
	}
}
