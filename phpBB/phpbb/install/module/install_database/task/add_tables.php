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

namespace phpbb\install\module\install_database\task;

use phpbb\db\doctrine\connection_factory;
use phpbb\db\driver\driver_interface;
use phpbb\db\tools\tools_interface;
use phpbb\install\helper\config;
use phpbb\install\helper\database;
use phpbb\install\sequential_task;
use phpbb\install\task_base;

/**
 * Create tables
 */
class add_tables extends task_base
{
	use sequential_task;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var tools_interface
	 */
	protected $db_tools;

	/**
	 * @var string
	 */
	protected $schema_file_path;

	/**
	 * @var string
	 */
	protected $table_prefix;

	/**
	 * @var bool
	 */
	protected $change_prefix;

	/**
	 * Constructor
	 *
	 * @param config	$config
	 * @param database	$db_helper
	 * @param string	$phpbb_root_path
	 */
	public function __construct(config $config,
								database $db_helper,
								string $phpbb_root_path)
	{
		$dbms = $db_helper->get_available_dbms($config->get('dbms'));
		$dbms = $dbms[$config->get('dbms')]['DRIVER'];
		$factory = new \phpbb\db\tools\factory();

		$this->db				= new $dbms();
		$this->db->sql_connect(
			$config->get('dbhost'),
			$config->get('dbuser'),
			$config->get('dbpasswd'),
			$config->get('dbname'),
			$config->get('dbport'),
			false,
			false
		);

		$doctrine_db = connection_factory::get_connection_from_params(
			$config->get('dbms'),
			$config->get('dbhost'),
			$config->get('dbuser'),
			$config->get('dbpasswd'),
			$config->get('dbname'),
			$config->get('dbport')
		);

		$this->config			= $config;
		$this->db_tools			= $factory->get($doctrine_db);
		$this->schema_file_path	= $phpbb_root_path . 'store/schema.json';
		$this->table_prefix		= $this->config->get('table_prefix');
		$this->change_prefix	= $this->config->get('change_table_prefix', true);

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->db->sql_return_on_error(true);

		if (!defined('CONFIG_TABLE'))
		{
			// CONFIG_TABLE is required by sql_create_index() to check the
			// length of index names. However table_prefix is not defined
			// here yet, so we need to create the constant ourselves.
			define('CONFIG_TABLE', $this->table_prefix . 'config');
		}

		$db_table_schema = @file_get_contents($this->schema_file_path);
		$db_table_schema = json_decode($db_table_schema, true);

		$this->execute($this->config, $db_table_schema);

		@unlink($this->schema_file_path);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute_step($key, $value) : void
	{
		$this->db_tools->sql_create_table(
			($this->change_prefix) ? ($this->table_prefix . substr($key, 6)) : $key,
			$value
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_step_count() : int
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name() : string
	{
		return 'TASK_CREATE_TABLES';
	}
}
