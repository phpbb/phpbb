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

require_once dirname(__FILE__) . '/ext/vendor2/bar/ext.php';
require_once dirname(__FILE__) . '/ext/vendor2/foo/ext.php';
require_once dirname(__FILE__) . '/ext/vendor3/foo/ext.php';
require_once dirname(__FILE__) . '/ext/vendor/moo/ext.php';

class phpbb_extension_manager_test extends phpbb_database_test_case
{
	protected $extension_manager;
	protected $class_loader;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/extensions.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->extension_manager = $this->create_extension_manager();
	}

	public function test_all_available()
	{
		// barfoo and vendor3/bar should not listed due to missing composer.json. barfoo also has incorrect dir structure.
		$this->assertEquals(array('vendor/moo', 'vendor2/bar', 'vendor2/foo', 'vendor3/foo', 'vendor4/bar'), array_keys($this->extension_manager->all_available()));
	}

	public function test_all_enabled()
	{
		$this->assertEquals(array('vendor2/foo'), array_keys($this->extension_manager->all_enabled()));
	}

	public function test_all_configured()
	{
		$this->assertEquals(array('vendor/moo', 'vendor2/foo'), array_keys($this->extension_manager->all_configured()));
	}

	public function test_is_enabled()
	{
		$this->assertSame(true, $this->extension_manager->is_enabled('vendor2/foo'));
		$this->assertSame(false, $this->extension_manager->is_enabled('vendor/moo'));
		$this->assertSame(false, $this->extension_manager->is_enabled('vendor2/bar'));
		$this->assertSame(false, $this->extension_manager->is_enabled('bertie/worlddominationplan'));
	}

	public function test_is_disabled()
	{
		$this->assertSame(false, $this->extension_manager->is_disabled('vendor2/foo'));
		$this->assertSame(true, $this->extension_manager->is_disabled('vendor/moo'));
		$this->assertSame(false, $this->extension_manager->is_disabled('vendor2/bar'));
		$this->assertSame(false, $this->extension_manager->is_disabled('bertie/worlddominationplan'));
	}

	public function test_is_purged()
	{
		$this->assertSame(false, $this->extension_manager->is_purged('vendor2/foo'));
		$this->assertSame(false, $this->extension_manager->is_purged('vendor/moo'));
		$this->assertSame(true, $this->extension_manager->is_purged('vendor2/bar'));
		$this->assertSame(false, $this->extension_manager->is_purged('bertie/worlddominationplan'));
	}

	public function test_is_configured()
	{
		$this->assertSame(true, $this->extension_manager->is_configured('vendor2/foo'));
		$this->assertSame(true, $this->extension_manager->is_configured('vendor/moo'));
		$this->assertSame(false, $this->extension_manager->is_configured('vendor2/bar'));
		$this->assertSame(false, $this->extension_manager->is_configured('bertie/worlddominationplan'));
	}

	public function test_is_available()
	{
		$this->assertSame(true, $this->extension_manager->is_available('vendor2/foo'));
		$this->assertSame(true, $this->extension_manager->is_available('vendor/moo'));
		$this->assertSame(true, $this->extension_manager->is_available('vendor2/bar'));
		$this->assertSame(false, $this->extension_manager->is_available('bertie/worlddominationplan'));
	}

	public function test_enable()
	{
		vendor2\bar\ext::$state = 0;

		$this->assertEquals(array('vendor2/foo'), array_keys($this->extension_manager->all_enabled()));
		$this->extension_manager->enable('vendor2/bar');
		$this->assertEquals(array('vendor2/bar', 'vendor2/foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('vendor/moo', 'vendor2/bar', 'vendor2/foo'), array_keys($this->extension_manager->all_configured()));

		$this->assertEquals(4, vendor2\bar\ext::$state);
	}

	public function test_enable_not_enableable()
	{
		vendor3\foo\ext::$enabled = false;

		$this->assertEquals(array('vendor2/foo'), array_keys($this->extension_manager->all_enabled()));
		$this->extension_manager->enable('vendor3/foo');
		$this->assertEquals(array('vendor2/foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('vendor/moo', 'vendor2/foo'), array_keys($this->extension_manager->all_configured()));

		$this->assertSame(false, vendor3\foo\ext::$enabled);
	}

	public function test_disable()
	{
		vendor2\foo\ext::$disabled = false;

		$this->assertEquals(array('vendor2/foo'), array_keys($this->extension_manager->all_enabled()));
		$this->extension_manager->disable('vendor2/foo');
		$this->assertEquals(array(), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('vendor/moo', 'vendor2/foo'), array_keys($this->extension_manager->all_configured()));

		$this->assertTrue(vendor2\foo\ext::$disabled);
	}

	public function test_purge()
	{
		vendor\moo\ext::$purged = false;

		$this->assertEquals(array('vendor2/foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('vendor/moo', 'vendor2/foo'), array_keys($this->extension_manager->all_configured()));
		$this->extension_manager->purge('vendor/moo');
		$this->assertEquals(array('vendor2/foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('vendor2/foo'), array_keys($this->extension_manager->all_configured()));

		$this->assertTrue(vendor\moo\ext::$purged);
	}

	public function test_enabled_no_cache()
	{
		$extension_manager = $this->create_extension_manager(false);

		$this->assertEquals(array('vendor2/foo'), array_keys($extension_manager->all_enabled()));
	}

	protected function create_extension_manager($with_cache = true)
	{

		$config = new \phpbb\config\config(array('version' => PHPBB_VERSION));
		$db = $this->new_dbal();
		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($db);
		$phpbb_root_path = __DIR__ . './../../phpBB/';
		$php_ext = 'php';
		$table_prefix = 'phpbb_';

		$container = new phpbb_mock_container_builder();

		$migrator = new \phpbb\db\migrator(
			$container,
			$config,
			$db,
			$db_tools,
			'phpbb_migrations',
			$phpbb_root_path,
			$php_ext,
			$table_prefix,
			array(),
			new \phpbb\db\migration\helper()
		);
		$container->set('migrator', $migrator);

		return new \phpbb\extension\manager(
			$container,
			$db,
			$config,
			new \phpbb\filesystem\filesystem(),
			'phpbb_ext',
			dirname(__FILE__) . '/',
			$php_ext,
			($with_cache) ? new phpbb_mock_cache() : null
		);
	}
}
