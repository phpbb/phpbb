<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/ext/bar/ext.php';
require_once dirname(__FILE__) . '/ext/foo/ext.php';
require_once dirname(__FILE__) . '/ext/vendor/moo/ext.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/db/db_tools.php';

class phpbb_extension_migrator_test extends phpbb_database_test_case
{
	protected $migrator;
	protected $extension_manager;
	protected $class_loader;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/extensions.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		global $table_prefix, $phpbb_root_path;

		$config = new phpbb_config(array());
		$db = $this->new_dbal();
		$db_tools = new phpbb_db_tools($db);
		$php_ext = 'php';

		$this->extension_manager = new phpbb_extension_manager(
			new phpbb_mock_container_builder(),
			$db,
			$config,
			'phpbb_ext',
			dirname(__FILE__) . '/',
			'.' . $php_ext,
			($with_cache) ? new phpbb_mock_cache() : null
		);

		$this->migrator = new phpbb_db_migrator(
			$config,
			$db,
			$db_tools,
			'phpbb_migrations',
			$phpbb_root_path,
			$php_ext,
			$table_prefix,
			array()
		);

		$this->extension_migrator = new phpbb_extension_migrator($this->extension_manager, $this->migrator);
	}

	public function test_enable()
	{
		phpbb_ext_bar_ext::$state = 0;

		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->extension_migrator->enable('bar');
		$this->assertEquals(array('bar', 'foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('bar', 'foo', 'vendor/moo'), array_keys($this->extension_manager->all_configured()));

		$this->assertEquals(4, phpbb_ext_bar_ext::$state);
	}

	public function test_disable()
	{
		phpbb_ext_foo_ext::$disabled = false;

		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->extension_migrator->disable('foo');
		$this->assertEquals(array(), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('foo', 'vendor/moo'), array_keys($this->extension_manager->all_configured()));

		$this->assertTrue(phpbb_ext_foo_ext::$disabled);
	}

	public function test_purge()
	{
		phpbb_ext_vendor_moo_ext::$purged = false;

		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('foo', 'vendor/moo'), array_keys($this->extension_manager->all_configured()));
		$this->extension_migrator->purge('vendor/moo');
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals(array('foo'), array_keys($this->extension_manager->all_configured()));

		$this->assertTrue(phpbb_ext_vendor_moo_ext::$purged);
	}
}
