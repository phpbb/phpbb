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

class phpbb_migrations_remove_jabber_migration_test extends phpbb_database_test_case
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
		return $this->createXMLDataSet(__DIR__.'/fixtures/migration_remove_jabber.xml');
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
		$cache = $this->cache_service = new \phpbb\cache\service(new \phpbb\cache\driver\dummy(), new \phpbb\config\config(array()), $this->db, $phpbb_dispatcher, $phpbb_root_path, $phpEx);

		$this->config = new \phpbb\config\db($this->db, $this->cache, 'phpbb_config');
		$this->config->initialise($this->cache);

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

		$tools = array(
			new \phpbb\db\migration\tool\config($this->config),
			new \phpbb\db\migration\tool\module($this->db, $this->user, $module_manager, 'phpbb_modules'),
			new \phpbb\db\migration\tool\permission($this->db, $this->cache_service, $this->auth, $phpbb_root_path, $phpEx),
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
			$tools,
			new \phpbb\db\migration\helper()
		);
		$container->set('migrator', $this->migrator);

		$remove_jabber_migration = $this->migrator->get_migration('\phpbb\db\migration\data\v400\remove_jabber');
		$depends = $remove_jabber_migration->depends_on();
		$this->migrator->populate_migrations($depends);
	}

	public function test_remove_jabber_migration()
	{
		$sql = "SELECT id FROM phpbb_user_notifications
			WHERE method = 'notification.method.jabber'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);
		$this->assertEquals(14, count($rowset));

		$sql = "SELECT config_name FROM phpbb_config
			WHERE config_name = 'jab_enable'";
		$this->assertNotFalse($this->db->sql_query($sql));

		$sql = "SELECT auth_option FROM phpbb_acl_options
			WHERE auth_option = 'a_jabber'";
		$this->assertNotFalse($this->db->sql_query($sql));

		$this->migrator->set_migrations(['\phpbb\db\migration\data\v400\remove_jabber']);

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

		$sql = "SELECT id FROM phpbb_user_notifications
			WHERE method = 'notification.method.jabber'";
		$this->db->sql_query($sql);
		$this->assertFalse($this->db->sql_fetchfield('id'));
		
		$sql = "SELECT id FROM phpbb_user_notifications
			WHERE method = 'notification.method.email'";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);
		$this->assertEquals(14, count($rowset));

		$sql = "SELECT config_name FROM phpbb_config
			WHERE config_name = 'jab_enable'";
		$this->db->sql_query($sql);
		$this->assertFalse($this->db->sql_fetchfield('config_name'));

		$sql = "SELECT auth_option FROM phpbb_acl_options
			WHERE auth_option = 'a_jabber'";
		$this->db->sql_query($sql);
		$this->assertFalse($this->db->sql_fetchfield('auth_option'));

		while ($this->migrator->migration_state('\phpbb\db\migration\data\v400\remove_jabber'))
		{
			try
			{
				$this->migrator->revert('\phpbb\db\migration\data\v400\remove_jabber');
			}
			catch (\phpbb\db\migration\exception $e)
			{
				$this->fail('Reverting migration error: ' . $e->__toString());
			}
		}

		$sql = "SELECT config_name FROM phpbb_config
			WHERE config_name = 'jab_enable'";
		$this->db->sql_query($sql);
		$this->assertEquals('jab_enable', $this->db->sql_fetchfield('config_name'));

		$sql = "SELECT auth_option FROM phpbb_acl_options
			WHERE auth_option = 'a_jabber'";
		$this->db->sql_query($sql);
		$this->assertEquals('a_jabber', $this->db->sql_fetchfield('auth_option'));
	}
}
