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

abstract class phpbb_migration_test_base extends phpbb_database_test_case
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\cache\service */
	protected $cache_service;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var \Doctrine\DBAL\Connection */
	protected $doctrine_db;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/** @var \phpbb\db\migrator */
	protected $migrator;

	/** @var \phpbb\db\migration\tool\tool_interface */
	protected $tools;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $fixture;

	/** @var string */
	protected $migration_class;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . $this->fixture);
	}

	protected function setUp(): void
	{
		global $cache, $db, $phpbb_log, $phpbb_root_path, $phpEx, $skip_add_log, $table_prefix, $user;

		parent::setUp();

		// Disable the logs
		$skip_add_log = true;

		$db = $this->db = $this->new_dbal();
		$this->doctrine_db = $this->new_doctrine_dbal();
		$factory = new \phpbb\db\tools\factory();
		$this->db_tools = $factory->get($this->doctrine_db);
		$this->db_tools->set_table_prefix($table_prefix);
		$this->cache = new phpbb_mock_cache();
		$this->auth = new \phpbb\auth\auth();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$this->config = new \phpbb\config\db($this->db, $this->cache, 'phpbb_config');
		$this->config->initialise($this->cache);
		$cache = $this->cache_service = new \phpbb\cache\service($this->cache, $this->config, $this->db, $phpbb_dispatcher, $phpbb_root_path, $phpEx);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = $this->user = new \phpbb\user($lang, '\phpbb\datetime');

		$phpbb_log = new \phpbb\log\log($this->db, $this->user, $this->auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		$container = new phpbb_mock_container_builder();
		$container->set('event_dispatcher', $phpbb_dispatcher);

		$finder_factory = $this->createMock('\phpbb\finder\factory');
		$this->extension_manager = new \phpbb\extension\manager(
			$container,
			$this->db,
			$this->config,
			$finder_factory,
			'phpbb_ext',
			__DIR__ . '/../../phpBB/',
			null
		);

		$module_manager = new \phpbb\module\module_manager($this->cache, $this->db, $this->extension_manager, 'phpbb_modules', $phpbb_root_path, $phpEx);

		$this->tools = array(
			'config'		=> new \phpbb\db\migration\tool\config($this->config),
			'config_text'	=> new \phpbb\db\migration\tool\config_text(new \phpbb\config\db_text($this->db, 'phpbb_config_text')),
			'module'		=> new \phpbb\db\migration\tool\module($this->db, $this->user, $module_manager, 'phpbb_modules'),
			'permission'	=> new \phpbb\db\migration\tool\permission($this->db, $this->cache_service, $this->auth, $phpbb_root_path, $phpEx),
		);

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
			$this->tools,
			new \phpbb\db\migration\helper()
		);
		$container->set('migrator', $this->migrator);

		$migration = $this->migrator->get_migration($this->migration_class);
		$depends = $migration->depends_on();
		$this->migrator->populate_migrations($depends);

		$this->migrator->set_migrations([$this->migration_class]);
	}

	protected function apply_migration()
	{
		while (!$this->migrator->finished())
		{
			try
			{
				$this->migrator->update();
			}
			catch (\phpbb\db\migration\exception $e)
			{
				$this->fail('Applying migration error: ' . $e->__toString());
			}
		}

		return $this->migrator->finished();
	}

	protected function revert_migration()
	{
		while ($this->migrator->migration_state($this->migration_class) !== false)
		{
			try
			{
				$this->migrator->revert($this->migration_class);
			}
			catch (\phpbb\db\migration\exception $e)
			{
				$this->fail('Reverting migration error: ' . $e->__toString());
			}
		}

		return !$this->migrator->migration_state($this->migration_class);
	}

	protected function get_schema()
	{
		return $this->doctrine_db->createSchemaManager()->introspectSchema();
	}
}
