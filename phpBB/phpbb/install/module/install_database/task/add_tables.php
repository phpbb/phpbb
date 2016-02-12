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

use phpbb\install\exception\resource_limit_reached_exception;

/**
 * Create tables
 */
class add_tables extends \phpbb\install\task_base
{
	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $config;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\db\tools\tools_interface
	 */
	protected $db_tools;

	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var string
	 */
	protected $schema_file_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\config				$config
	 * @param \phpbb\install\helper\database			$db_helper
	 * @param \phpbb\filesystem\filesystem_interface	$filesystem
	 * @param string									$phpbb_root_path
	 */
	public function __construct(\phpbb\install\helper\config $config,
								\phpbb\install\helper\database $db_helper,
								\phpbb\filesystem\filesystem_interface $filesystem,
								$phpbb_root_path)
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

		$this->config			= $config;
		$this->db_tools			= $factory->get($this->db);
		$this->filesystem		= $filesystem;
		$this->schema_file_path	= $phpbb_root_path . 'store/schema.json';

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->db->sql_return_on_error(true);

		$table_prefix = $this->config->get('table_prefix');
		$change_prefix = $this->config->get('change_table_prefix', true);

		if (!defined('CONFIG_TABLE'))
		{
			// CONFIG_TABLE is required by sql_create_index() to check the
			// length of index names. However table_prefix is not defined
			// here yet, so we need to create the constant ourselves.
			define('CONFIG_TABLE', $table_prefix . 'config');
		}

		$db_table_schema = @file_get_contents($this->schema_file_path);
		$db_table_schema = json_decode($db_table_schema, true);
		$total = sizeof($db_table_schema);
		$i = $this->config->get('add_table_index', 0);
		$db_table_schema = array_slice($db_table_schema, $i);

		foreach ($db_table_schema as $table_name => $table_data)
		{
			$i++;

			$this->db_tools->sql_create_table(
				( ($change_prefix) ? ($table_prefix . substr($table_name, 6)) : $table_name ),
				$table_data
			);

			// Stop execution if resource limit is reached
			if ($this->config->get_time_remaining() <= 0 || $this->config->get_memory_remaining() <= 0)
			{
				break;
			}
		}

		$this->config->set('add_table_index', $i);

		if ($i < $total)
		{
			throw new resource_limit_reached_exception();
		}
		else
		{
			@unlink($this->schema_file_path);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return 'TASK_CREATE_TABLES';
	}
}
