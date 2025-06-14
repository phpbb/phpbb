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

	protected function setUp(): void
	{
		parent::setUp();

		$this->purge_cache();
	}

	protected function tearDown(): void
	{
		$this->uninstall_ext('foo/bar');
		$this->uninstall_ext('foo/foo');

		parent::tearDown();
	}

	protected static function setup_extensions()
	{
		return ['foo/bar', 'foo/foo'];
	}

	/**
	 * Check extensions template event listener prioritizing
	 */
	public function test_different_template_event_priority()
	{
		global $phpbb_root_path;

		$crawler = self::request('GET', 'index.php');
		$quick_links_menu = $crawler->filter('ul[role="menu"]')->eq(0);
		$quick_links_menu_nodes_count = (int) $quick_links_menu->filter('li')->count();
		// Ensure foo/foo template event goes before foo/bar one
		$this->assertStringContainsString('FOO_FOO_QUICK_LINK', $quick_links_menu->filter('li')->eq($quick_links_menu_nodes_count - 4)->filter('span')->text());
		$this->assertStringContainsString('FOO_BAR_QUICK_LINK', $quick_links_menu->filter('li')->eq($quick_links_menu_nodes_count - 3)->filter('span')->text());

		// Change template events order to default, put foo/bar event before foo/foo one
		$this->disable_ext('foo/bar');
		$this->disable_ext('foo/foo');

		$this->assertTrue(copy(__DIR__ . '/fixtures/ext/foo/bar/event/template_event_order_higher.php', $phpbb_root_path . 'ext/foo/bar/event/template_event_order.php'));
		$this->assertTrue(copy(__DIR__ . '/fixtures/ext/foo/foo/event/template_event_order_lower.php', $phpbb_root_path . 'ext/foo/foo/event/template_event_order.php'));

		$this->install_ext('foo/bar');
		$this->install_ext('foo/foo');

		$crawler = self::request('GET', 'index.php');
		$quick_links_menu = $crawler->filter('ul[role="menu"]')->eq(0);
		$quick_links_menu_nodes_count = (int) $quick_links_menu->filter('li')->count();
		// Ensure foo/foo template event goes before foo/bar one
		$this->assertStringContainsString('FOO_BAR_QUICK_LINK', $quick_links_menu->filter('li')->eq($quick_links_menu_nodes_count - 4)->filter('span')->text());
		$this->assertStringContainsString('FOO_FOO_QUICK_LINK', $quick_links_menu->filter('li')->eq($quick_links_menu_nodes_count - 3)->filter('span')->text());
	}

	/**
	 * Check extensions template event listener equal (default - 0) priority rendering
	 * Should render in the order of reading listener files from the filesystem
	 */
	public function test_same_template_event_priority()
	{
		global $phpbb_root_path;

		$crawler = self::request('GET', 'index.php');
		// Ensure foo/bar template event goes before foo/foo one (assuming they have been read from the filesystem in alphabetical order)
		$this->assertStringContainsString('FOO_BAR_FORUMLIST_BODY_BEFORE', $crawler->filter('p[id*="forumlist_body_before"]')->eq(0)->text());
		$this->assertStringContainsString('FOO_FOO_FORUMLIST_BODY_BEFORE', $crawler->filter('p[id*="forumlist_body_before"]')->eq(1)->text());
	}
}
