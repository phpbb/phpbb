<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/db/migrator.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/db/migration.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/db/db_tools.php';

require_once dirname(__FILE__) . '/migration/dummy.php';
require_once dirname(__FILE__) . '/migration/unfulfillable.php';

class phpbb_dbal_migrator_test extends phpbb_database_test_case
{
	protected $db;
	protected $db_tools;
	protected $migrator;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/migrator.xml');
	}

	public function setup()
	{
		parent::setup();

		$this->db = $this->new_dbal();
		$this->db_tools = new phpbb_db_tools($this->db);

		$this->config = new phpbb_config_db($this->db, new phpbb_mock_cache, 'phpbb_config');

		$this->container = new \Symfony\Component\DependencyInjection\Container;
		$this->container->set('dbal.conn', $this->db);
		$this->container->set('dbal.tools', $this->db_tools);
		$this->container->set('config', $this->config);

		$this->container->setParameter('core.table_prefix', 'phpbb_');
		$this->container->setParameter('core.root_path', 'phpBB/');
		$this->container->setParameter('core.php_ext', 'php');
		$this->container->setParameter('tables.migrations', 'phpbb_migrations');

		$tools = array(
			new phpbb_db_migration_tool_config($this->config),
		);
		$this->migrator = new phpbb_db_migrator($this->container, $tools, array());
	}

	public function tearDown()
	{
		// cleanup
		$this->db_tools->sql_column_remove('phpbb_config', 'extra_column');
	}

	public function test_update()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_dummy'));

		// schema
		$this->migrator->update();
		$this->assertFalse($this->migrator->finished());

		$this->assertSqlResultEquals(
			array(array('success' => '1')),
			"SELECT 1 as success
				FROM phpbb_migrations
				WHERE migration_name = 'phpbb_dbal_migration_dummy'
					AND migration_start_time >= " . (time() - 1) . "
					AND migration_start_time <= " . (time() + 1),
			'Start time set correctly'
		);

		// data
		$this->migrator->update();
		$this->assertTrue($this->migrator->finished());

		$this->assertSqlResultEquals(
			array(array('extra_column' => '1')),
			"SELECT extra_column FROM phpbb_config WHERE config_name = 'foo'",
			'Dummy migration created extra_column with value 1 in all rows.'
		);

		$this->assertSqlResultEquals(
			array(array('success' => '1')),
			"SELECT 1 as success
				FROM phpbb_migrations
				WHERE migration_name = 'phpbb_dbal_migration_dummy'
					AND migration_start_time <= migration_end_time
					AND migration_end_time >= " . (time() - 1) . "
					AND migration_end_time <= " . (time() + 1),
			'End time set correctly'
		);
	}

	public function test_unfulfillable()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_unfulfillable', 'phpbb_dbal_migration_dummy'));

		while (!$this->migrator->finished())
		{
			$this->migrator->update();
		}

		$this->assertTrue($this->migrator->finished());

		$this->assertSqlResultEquals(
			array(array('extra_column' => '1')),
			"SELECT extra_column FROM phpbb_config WHERE config_name = 'foo'",
			'Dummy migration was run, even though an unfulfillable migration was found.'
		);
	}
}
