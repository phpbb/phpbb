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
* @group functional
*/
class phpbb_functional_extension_template_event_order_test extends phpbb_functional_test_case
{
	protected $phpbb_extension_manager;

	static private $helper;

	static protected $fixtures = [
		'./',
	];

	static public function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(__CLASS__);
		self::$helper->copy_ext_fixtures(__DIR__ . '/fixtures/ext/', self::$fixtures);
	}

	static public function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();

		self::$helper->restore_original_ext_dir();
	}

	protected function tearDown(): void
	{
		$this->purge_cache();

		parent::tearDown();
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->phpbb_extension_manager = $this->get_extension_manager();

		$this->purge_cache();
	}

	/**
	* Check a controller for extension foo/bar.
	*/
	public function test_template_event_order()
	{
		global $phpbb_root_path;

		$this->phpbb_extension_manager->enable('foo/bar');
		$this->phpbb_extension_manager->enable('foo/foo');
		$crawler = self::request('GET', 'index.php');
		$quick_links_menu = $crawler->filter('ul[role="menu"]')->eq(0);
		$quick_links_menu_nodes_count = (int) $quick_links_menu->filter('li')->count();
		// Ensure foo/foo template event goes before foo/bar one
		$this->assertStringContainsString('FOO_FOO_QUICK_LINK', $quick_links_menu->filter('li')->eq($quick_links_menu_nodes_count - 2)->filter('span')->text());
		$this->assertStringContainsString('FOO_BAR_QUICK_LINK', $quick_links_menu->filter('li')->eq($quick_links_menu_nodes_count - 1)->filter('span')->text());

		// Change template events order to default, put foo/bar event before foo/foo one
		$this->phpbb_extension_manager->disable('foo/bar');
		$this->phpbb_extension_manager->disable('foo/foo');
		$this->assertTrue(copy(__DIR__ . '/fixtures/ext/foo/bar/event/template_event_order_higher.php', $phpbb_root_path . 'ext/foo/bar/event/template_event_order.php'));
		$this->assertTrue(copy(__DIR__ . '/fixtures/ext/foo/foo/event/template_event_order_lower.php', $phpbb_root_path . 'ext/foo/foo/event/template_event_order.php'));
		$this->phpbb_extension_manager->enable('foo/bar');
		$this->phpbb_extension_manager->enable('foo/foo');
		$this->purge_cache();
		sleep(3);
		$crawler = self::request('GET', 'index.php');
		$quick_links_menu = $crawler->filter('ul[role="menu"]')->eq(0);
		$quick_links_menu_nodes_count = (int) $quick_links_menu->filter('li')->count();
		// Ensure foo/foo template event goes before foo/bar one
		$this->assertStringContainsString('FOO_BAR_QUICK_LINK', $quick_links_menu->filter('li')->eq($quick_links_menu_nodes_count - 2)->filter('span')->text());
		$this->assertStringContainsString('FOO_FOO_QUICK_LINK', $quick_links_menu->filter('li')->eq($quick_links_menu_nodes_count - 1)->filter('span')->text());

		$this->phpbb_extension_manager->purge('foo/bar');
		$this->phpbb_extension_manager->purge('foo/foo');
	}
}
