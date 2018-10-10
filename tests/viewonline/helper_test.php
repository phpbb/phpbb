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
	protected $viewonline_helper;

	protected function setUp(): void
	{
		parent::setUp();

		$db = $this->getMockBuilder('\phpbb\db\driver\mysqli')
			->disableOriginalConstructor()
			->getMock();

		$config = $this->getMockBuilder('\phpbb\config\config')
			->disableOriginalConstructor()
			->getMock();

		$dispatcher = $this->getMockBuilder('phpbb_mock_event_dispatcher')
			->getMock();

		$router = $this->getMockBuilder('\phpbb\routing\router')
			->disableOriginalConstructor()
			->getMock();

		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();

		$language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();

		$auth = $this->getMockBuilder('\phpbb\auth\auth')
			->disableOriginalConstructor()
			->getMock();

		$phpbb_root_path = './';
		$phpEx = 'php';

		$this->viewonline_helper = new \phpbb\members\viewonline_helper($db, $config, $dispatcher, $router, $controller_helper, $language, $auth, $phpbb_root_path, $phpEx, 'adm/', '%tables.users%', '%tables.sessions%', '%tables.topics%', '%tables.forums%');
	}

	public static function session_pages_data()
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
