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

class migrations_check_config_added_test extends phpbb_test_case
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \Doctrine\DBAL\Connection */
	protected $db_doctrine;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/** @var \phpbb\db\migrator */
	protected $migrator;

	/** @var string */
	protected $table_prefix;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	protected function setUp(): void
	{
		global $phpbb_root_path;

		// Get schema data from file
		$this->schema_data = file_get_contents($phpbb_root_path . 'install/schemas/schema_data.sql');
	}

	public function get_config_options_from_migrations()
	{
		global $phpbb_root_path, $phpEx;

		$this->config = new \phpbb\config\config([
			'search_type'		=> '\phpbb\search\fulltext_mysql',
		]);

		$this->db = $this->createMock('\phpbb\db\driver\driver_interface');
		$this->db_doctrine = $this->createMock(\Doctrine\DBAL\Connection::class);
		$factory = new \phpbb\db\tools\factory();
		$this->db_tools = $factory->get($this->db_doctrine);
		$this->table_prefix = 'phpbb_';
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$tools = [
			new \phpbb\db\migration\tool\config($this->config),
		];

		$this->container = new phpbb_mock_container_builder();

		$this->migrator = new \phpbb\db\migrator(
			$this->container,
			$this->config,
			$this->db,
			$this->db_tools,
			'phpbb_migrations',
			$this->phpbb_root_path,
			$this->php_ext,
			$this->table_prefix,
			[],
			$tools,
			new \phpbb\db\migration\helper()
		);
		$this->container->set('migrator', $this->migrator);

		$this->extension_manager = new phpbb_mock_extension_manager(
			$this->phpbb_root_path,
			[],
			$this->container
		);

		// Get all migrations
		$migrations = $this->extension_manager
			->get_finder()
			->core_path('phpbb/db/migration/data/')
			->extension_directory('/migrations')
			->get_classes();

		$config_names = $config_removed = [];
		foreach ($migrations as $key => $class)
		{
			// Filter non-migration files
			if (!$this->migrator::is_migration($class))
			{
				unset($migrations[$key]);
				continue;
			}

			// Create migration object instance
			$migration = $this->migrator->get_migration($class);

			// $step[0] - action (config.*|if|custom|etc), $step[1][1] - action when wrapped with 'if' action
			// $step[1] - action parameters for non-'if'-wrapped actions (0 => config_name and 1 => config_value)
			// $step[1][0] - configuration option to add/update/remove (config_name)
			foreach ($migration->update_data() as $migration_name => $step)
			{
				if ($step[0] == 'if')
				{
					$step = $step[1][1];
				}

				// Filter out actions different from config.*
				if ($step[0] == 'custom' || strpos($step[0], 'config') === false)
				{
					continue;
				}

				$config_name = $step[1][0];
				// Exclude removed configuration options and filter them out
				if ($step[0] == 'config.remove')
				{
					if (!isset($config_removed[$config_name]))
					{
						$config_removed[$config_name] = true;
					}

					continue;
				}

				// Fill error entries for configuration options which were not added to schema_data.sql
				if (!isset($config_names[$config_name]))
				{
					$config_names[$config_name] = [$config_name, $class];
				}
			}
		}

		// Drop configuration options which were removed by config.remove
		$config_names = array_diff_key($config_names, $config_removed);
		return $config_names;
	}

	/**
	* @dataProvider get_config_options_from_migrations
	*/
	public function test_config_option_exists_in_schema_data($config_name, $class)
	{
		$message = 'Migration: %1$s, config_name: %2$s; not added to schema_data.sql';

		$this->assertNotFalse(strpos($this->schema_data, $config_name),
			sprintf($message, $class, $config_name)
		);
	}
}
