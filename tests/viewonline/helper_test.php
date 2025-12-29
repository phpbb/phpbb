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
	private $auth;

	protected function setUp(): void
	{
		global $phpbb_dispatcher;

		parent::setUp();

		$phpbb_root_path = __DIR__ . '/../../phpBB/';
		$phpEx = 'php';

		$db = $this->getMockBuilder('\phpbb\db\driver\driver_interface')
			->disableOriginalConstructor()
			->getMock();

		// Mock database to return forum data
		$forum_data = [
			'forum_id' => 1,
			'forum_name' => 'Test Forum',
			'parent_id' => 0,
			'forum_type' => FORUM_POST,
			'left_id' => 1,
			'right_id' => 2,
		];

		$db->method('sql_fetchrow')->willReturnOnConsecutiveCalls($forum_data, false);

		$config = new \phpbb\config\config([]);

		$dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_dispatcher = $dispatcher;


		$container = new phpbb_mock_container_builder();
		$container->setParameter('core.environment', PHPBB_ENVIRONMENT);

		$loader = new \Symfony\Component\Routing\Loader\YamlFileLoader(
			new \phpbb\routing\file_locator($phpbb_root_path)
		);

		$extension_manager = new phpbb_mock_extension_manager($phpbb_root_path);
		$resources_locator = new \phpbb\routing\resources_locator\default_resources_locator(
			$phpbb_root_path,
			PHPBB_ENVIRONMENT,
			$extension_manager
		);

		// Create router
		$router = new phpbb_mock_router($container, $resources_locator, $loader, $phpEx, './', '', '');

		// Mock controller helper to use the real router for generating route URLs
		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
		$controller_helper->method('route')
			->willReturnCallback(function($route) use ($router) {
				// Use the real router to generate the actual URL for the route
				return '.' . $router->generate($route);
			});

		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		$this->auth = $this->getMockBuilder('\phpbb\auth\auth')
			->disableOriginalConstructor()
			->getMock();

		$this->viewonline_helper = new \phpbb\members\viewonline_helper($db, $config, $dispatcher, $router, $controller_helper, $language, $this->auth, './', $phpEx, 'adm/', '%tables.users%', '%tables.sessions%', '%tables.topics%', '%tables.forums%');
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

	public static function get_location_data()
	{
		return array(
			// Route-based pages - routes have prefixes as defined in routing.yml
			// index
			array('', 0, true, 'Index page', './'),
			array('/', 0, true, 'Index page', './'),
			array('/app.php/', 0, true, 'Index page', './'),
			array('index.php', 0, true, 'Index page', './index.php'),
			array('./index.php', 0, true, 'Index page', './index.php'),
			// help.yml routes have prefix /help
			array('/help/bbcode', 0, true, 'Viewing FAQ', './help/faq'),
			array('/help/faq', 0, true, 'Viewing FAQ', './help/faq'),
			// members.yml routes have prefix /members
			array('/members/online', 0, true, 'Viewing who is online', './members/online'),
			array('/members/online/whois/abc123', 0, true, 'Viewing who is online', './members/online'),
			array('/members/team', 0, true, 'Viewing member details', './memberlist.php'),
			// report.yml routes have no prefix
			array('/pm/5/report', 0, true, 'Reporting post', './index.php'),
			array('/post/10/report', 0, true, 'Reporting post', './index.php'),

			// Legacy pages - admin
			array('adm/index.php', 0, true, 'Administration Control Panel', './index.php'),

			// Legacy pages - search
			array('search.php', 0, true, 'Searching forums', './search.php'),

			// Legacy pages - memberlist
			array('memberlist.php', 0, true, 'Viewing member details', './memberlist.php'),
			array('memberlist.php?mode=viewprofile&u=2', 0, true, 'Viewing member profile', './memberlist.php'),
			array('memberlist.php?mode=contactadmin', 0, true, 'Viewing contact page', './memberlist.php?mode=contactadmin'),

			// Legacy pages - MCP
			array('mcp.php', 0, true, 'Viewing moderator control panel', './index.php'),

			// Legacy pages - UCP
			array('ucp.php', 0, true, 'Viewing user control panel', './index.php'),
			array('ucp.php?mode=register', 0, true, 'Registering account', './index.php'),
			array('ucp.php?i=pm&mode=compose', 0, true, 'Composing private message', './index.php'),
			array('ucp.php?i=pm&mode=view', 0, true, 'Viewing private messages', './index.php'),
			array('ucp.php?i=prefs&mode=view', 0, true, 'Changing board preferences', './index.php'),
			array('ucp.php?i=profile&mode=edit', 0, true, 'Changing profile settings', './index.php'),

			// Forum-based pages with forum access
			array('posting.php?f=1&mode=reply', 1, true, 'Replying to message in Test Forum', './viewforum.php?f=1'),
			array('posting.php?f=1&mode=quote', 1, true, 'Replying to message in Test Forum', './viewforum.php?f=1'),
			array('posting.php?f=1&mode=post', 1, true, 'Posting message in Test Forum', './viewforum.php?f=1'),
			array('viewtopic.php?f=1&t=1', 1, true, 'Reading topic in Test Forum', './viewforum.php?f=1'),
			array('viewforum.php?f=1', 1, true, 'Viewing topics in Test Forum', './viewforum.php?f=1'),

			// Forum-based pages without forum access - should redirect to index
			array('posting.php?f=1&mode=reply', 1, false, 'Index page', './index.php'),
			array('viewtopic.php?f=1&t=1', 1, false, 'Index page', './index.php'),
			array('viewforum.php?f=1', 1, false, 'Index page', './index.php'),

			// Unknown route
			array('/unknown/route', 0, true, 'Index page', './index.php'),
		);
	}

	/**
	 * @dataProvider get_location_data
	 */
	public function test_get_location($session_page, $forum_id, $has_access, $expected_location, $expected_url)
	{
		$this->auth->method('acl_get')->willReturn($has_access);

		list($location, $location_url) = $this->viewonline_helper->get_location($session_page, $forum_id);

		$this->assertIsString($location);
		$this->assertIsString($location_url);

		$this->assertEquals($expected_location, $location, "Location mismatch for page: {$session_page}");
		$this->assertEquals($expected_url, $location_url, "URL mismatch for page: {$session_page}");
	}
}
