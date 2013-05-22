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
require_once dirname(__FILE__) . '/../../phpBB/includes/db/migration/migration.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/db/db_tools.php';

require_once dirname(__FILE__) . '/migration/dummy.php';
require_once dirname(__FILE__) . '/migration/unfulfillable.php';
require_once dirname(__FILE__) . '/migration/if.php';
require_once dirname(__FILE__) . '/migration/recall.php';
require_once dirname(__FILE__) . '/migration/revert.php';
require_once dirname(__FILE__) . '/migration/revert_with_dependency.php';
require_once dirname(__FILE__) . '/migration/fail.php';
require_once dirname(__FILE__) . '/migration/installed.php';

class phpbb_dbal_migrator_test extends phpbb_database_test_case
{
	protected $db;
	protected $db_tools;
	protected $migrator;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/migrator.xml');
	}

	public function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->db_tools = new phpbb_db_tools($this->db);

		$this->config = new phpbb_config_db($this->db, new phpbb_mock_cache, 'phpbb_config');

		$tools = array(
			new phpbb_db_migration_tool_config($this->config),
		);

		$this->migrator = new phpbb_db_migrator(
			$this->config,
			$this->db,
			$this->db_tools,
			'phpbb_migrations',
			dirname(__FILE__) . '/../../phpBB/',
			'php',
			'phpbb_',
			$tools
		);

		$container = new phpbb_mock_container_builder();
		$container->set('migrator', $migrator);

		$this->extension_manager = new phpbb_extension_manager(
			$container,
			$this->db,
			$this->config,
			new phpbb_filesystem(),
			'phpbb_ext',
			dirname(__FILE__) . '/../../phpBB/',
			'php',
			null
		);
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

		// cleanup
		$this->db_tools->sql_column_remove('phpbb_config', 'extra_column');
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

		$this->db_tools->sql_column_remove('phpbb_config', 'extra_column');
	}

	public function test_if()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_if'));

		// Don't like this, but I'm not sure there is any other way to do this
		global $migrator_test_if_true_failed, $migrator_test_if_false_failed;
		$migrator_test_if_true_failed = true;
		$migrator_test_if_false_failed = false;

		while (!$this->migrator->finished())
		{
			$this->migrator->update();
		}

		$this->assertFalse($migrator_test_if_true_failed, 'True test failed');
		$this->assertFalse($migrator_test_if_false_failed, 'False test failed');
	}

	public function test_recall()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_recall'));

		global $migrator_test_call_input;

		// Run the schema first
		$this->migrator->update();

		$i = 0;
		while (!$this->migrator->finished())
		{
			$this->migrator->update();

			$this->assertSame($i, $migrator_test_call_input);

			$i++;
		}

		$this->assertSame(10, $migrator_test_call_input);
	}

	public function test_revert()
	{
		// Make sure there are no other migrations in the db, this could cause issues
		$this->db->sql_query("DELETE FROM phpbb_migrations");
		$this->migrator->load_migration_state();

		$this->migrator->set_migrations(array('phpbb_dbal_migration_revert', 'phpbb_dbal_migration_revert_with_dependency'));

		$this->assertFalse($this->migrator->migration_state('phpbb_dbal_migration_revert'));
		$this->assertFalse($this->migrator->migration_state('phpbb_dbal_migration_revert_with_dependency'));

		// Install the migration first
		while (!$this->migrator->finished())
		{
			$this->migrator->update();
		}

		$this->assertTrue($this->migrator->migration_state('phpbb_dbal_migration_revert') !== false);
		$this->assertTrue($this->migrator->migration_state('phpbb_dbal_migration_revert_with_dependency') !== false);

		$this->assertSqlResultEquals(
			array(array('bar_column' => '1')),
			"SELECT bar_column FROM phpbb_config WHERE config_name = 'foo'",
			'Installing revert migration failed to create bar_column.'
		);

		$this->assertTrue(isset($this->config['foobartest']));

		while ($this->migrator->migration_state('phpbb_dbal_migration_revert') !== false)
		{
			$this->migrator->revert('phpbb_dbal_migration_revert');
		}

		$this->assertFalse($this->migrator->migration_state('phpbb_dbal_migration_revert'));
		$this->assertFalse($this->migrator->migration_state('phpbb_dbal_migration_revert_with_dependency'));

		$this->assertFalse(isset($this->config['foobartest']));

		$sql = 'SELECT * FROM phpbb_config';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (isset($row['bar_column']))
		{
			$this->fail('Revert did not remove test_column.');
		}
	}

	public function test_fail()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_fail'));

		$this->assertFalse(isset($this->config['foobar3']));

		try
		{
			while (!$this->migrator->finished())
			{
				$this->migrator->update();
			}
		}
		catch (phpbb_db_migration_exception $e) {}

		// Failure should have caused an automatic roll-back, so this should not exist.
		$this->assertFalse(isset($this->config['foobar3']));

		$sql = 'SELECT * FROM phpbb_config';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (isset($row['test_column']))
		{
			$this->fail('Revert did not remove test_column.');
		}
	}

	public function test_installed()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_installed'));

		global $migrator_test_installed_failed;
		$migrator_test_installed_failed = false;

		while (!$this->migrator->finished())
		{
			$this->migrator->update();
		}

		$this->assertTrue($this->migrator->migration_state('phpbb_dbal_migration_installed') !== false);

		if ($migrator_test_installed_failed)
		{
			$this->fail('Installed test failed');
		}
	}
}
