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

require_once __DIR__ . '/migration/dummy.php';
require_once __DIR__ . '/migration/unfulfillable.php';
require_once __DIR__ . '/migration/if.php';
require_once __DIR__ . '/migration/recall.php';
require_once __DIR__ . '/migration/if_params.php';
require_once __DIR__ . '/migration/recall_params.php';
require_once __DIR__ . '/migration/revert.php';
require_once __DIR__ . '/migration/revert_with_dependency.php';
require_once __DIR__ . '/migration/revert_table.php';
require_once __DIR__ . '/migration/revert_table_with_dependency.php';
require_once __DIR__ . '/migration/fail.php';
require_once __DIR__ . '/migration/installed.php';
require_once __DIR__ . '/migration/schema.php';

class phpbb_dbal_migrator_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \Doctrine\DBAL\Connection */
	protected $doctrine_db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var \phpbb\db\migrator */
	protected $migrator;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/fixtures/migrator.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->doctrine_db = $this->new_doctrine_dbal();
		$factory = new \phpbb\db\tools\factory();
		$this->db_tools = $factory->get($this->doctrine_db);

		$this->config = new \phpbb\config\db($this->db, new phpbb_mock_cache, 'phpbb_config');

		$finder_factory = $this->createMock('\phpbb\finder\factory');

		$tools = array(
			new \phpbb\db\migration\tool\config($this->config),
		);

		$container = new phpbb_mock_container_builder();

		$this->migrator = new \phpbb\db\migrator(
			$container,
			$this->config,
			$this->db,
			$this->db_tools,
			'phpbb_migrations',
			__DIR__ . '/../../phpBB/',
			'php',
			'phpbb_',
			self::get_core_tables(),
			$tools,
			new \phpbb\db\migration\helper()
		);
		$container->set('migrator', $this->migrator);
		$container->set('event_dispatcher', new phpbb_mock_event_dispatcher());

		$this->extension_manager = new \phpbb\extension\manager(
			$container,
			$this->db,
			$this->config,
			$finder_factory,
			'phpbb_ext',
			__DIR__ . '/../../phpBB/',
			null
		);
	}

	public function test_update()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_dummy'));

		// schema
		$start_time = time();
		$this->migrator->update();
		$this->assertFalse($this->migrator->finished());

		$this->assertSqlResultEquals(
			array(array('success' => '1')),
			"SELECT 1 as success
				FROM phpbb_migrations
				WHERE migration_name = 'phpbb_dbal_migration_dummy'
					AND migration_start_time >= " . ($start_time - 1) . "
					AND migration_start_time <= " . (time() + 1),
			'Start time set correctly'
		);

		// data
		$start_time = time();
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
					AND migration_end_time >= " . ($start_time - 1) . "
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

		while ($this->migrator->migration_state('phpbb_dbal_migration_if') !== false)
		{
			$this->migrator->revert('phpbb_dbal_migration_if');
		}

		$this->assertFalse($migrator_test_if_true_failed, 'True test after revert failed');
		$this->assertFalse($migrator_test_if_false_failed, 'False test after revert failed');
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

	public function test_if_params()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_if_params'));

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

		while ($this->migrator->migration_state('phpbb_dbal_migration_if_params') !== false)
		{
			$this->migrator->revert('phpbb_dbal_migration_if_params');
		}

		$this->assertFalse($migrator_test_if_true_failed, 'True test after revert failed');
		$this->assertFalse($migrator_test_if_false_failed, 'False test after revert failed');
	}

	public function test_recall_params()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_recall_params'));

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

		$this->assertSame(5, $migrator_test_call_input);
	}

	public function test_revert()
	{
		global $migrator_test_revert_counter;

		// Make sure there are no other migrations in the db, this could cause issues
		$this->db->sql_query("DELETE FROM phpbb_migrations");
		$this->migrator->load_migration_state();

		$migrator_test_revert_counter = 0;

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

		$this->assertEquals(1, $migrator_test_revert_counter, 'Revert did call custom function again');
	}

	public function test_revert_table()
	{
		// Make sure there are no other migrations in the db, this could cause issues
		$this->db->sql_query("DELETE FROM phpbb_migrations");
		$this->migrator->load_migration_state();

		$this->migrator->set_migrations(array('phpbb_dbal_migration_revert_table', 'phpbb_dbal_migration_revert_table_with_dependency'));

		$this->assertFalse($this->migrator->migration_state('phpbb_dbal_migration_revert_table'));
		$this->assertFalse($this->migrator->migration_state('phpbb_dbal_migration_revert_table_with_dependency'));

		// Install the migration first
		while (!$this->migrator->finished())
		{
			$this->migrator->update();
		}

		$this->assertTrue($this->migrator->migration_state('phpbb_dbal_migration_revert_table') !== false);
		$this->assertTrue($this->migrator->migration_state('phpbb_dbal_migration_revert_table_with_dependency') !== false);

		$this->assertTrue($this->db_tools->sql_column_exists('phpbb_foobar', 'baz_column'));
		$this->assertFalse($this->db_tools->sql_column_exists('phpbb_foobar', 'bar_column'));

		// Revert migrations
		while ($this->migrator->migration_state('phpbb_dbal_migration_revert_table') !== false)
		{
			$this->migrator->revert('phpbb_dbal_migration_revert_table');
		}

		$this->assertFalse($this->migrator->migration_state('phpbb_dbal_migration_revert_table'));
		$this->assertFalse($this->migrator->migration_state('phpbb_dbal_migration_revert_table_with_dependency'));

		$this->assertFalse($this->db_tools->sql_table_exists('phpbb_foobar'));
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
		catch (\phpbb\db\migration\exception $e) {}

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

	public function test_schema()
	{
		$this->migrator->set_migrations(array('phpbb_dbal_migration_schema'));

		while (!$this->migrator->finished())
		{
			$this->migrator->update();
		}

		$this->assertTrue($this->db_tools->sql_column_exists('phpbb_config', 'test_column1'));
		$this->assertTrue($this->db_tools->sql_table_exists('phpbb_foobar'));

		while ($this->migrator->migration_state('phpbb_dbal_migration_schema'))
		{
			$this->migrator->revert('phpbb_dbal_migration_schema');
		}

		$this->assertFalse($this->db_tools->sql_column_exists('phpbb_config', 'test_column1'));
		$this->assertFalse($this->db_tools->sql_table_exists('phpbb_foobar'));
	}
}
