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
 * Create database schema
 */
class create_schema extends \phpbb\install\task_base
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
	 * @var \phpbb\install\helper\database
	 */
	protected $database_helper;

	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\config							$config				Installer's config provider
	 * @param \phpbb\install\helper\database						$db_helper			Installer's database helper
	 * @param \phpbb\filesystem\filesystem_interface				$filesystem			Filesystem service
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler			Installer's input-output handler
	 * @param string												$phpbb_root_path	Path phpBB's root
	 * @param string												$php_ext			Extension of PHP files
	 */
	public function __construct(\phpbb\install\helper\config $config,
								\phpbb\install\helper\database $db_helper,
								\phpbb\filesystem\filesystem_interface $filesystem,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler,
								$phpbb_root_path,
								$php_ext)
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
		$this->database_helper	= $db_helper;
		$this->filesystem		= $filesystem;
		$this->iohandler		= $iohandler;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// As this task may take a large amount of time to complete refreshing the page might be necessary for some
		// server configurations with limited resources
		if (!$this->config->get('pre_schema_forced_refresh'))
		{
			if ($this->config->get_time_remaining() < 5)
			{
				$this->config->set('pre_schema_forced_refresh', true);
				throw new resource_limit_reached_exception();
			}
		}

		$this->db->sql_return_on_error(true);

		$dbms = $this->config->get('dbms');
		$dbms_info = $this->database_helper->get_available_dbms($dbms);
		$schema_name = $dbms_info[$dbms]['SCHEMA'];
		$delimiter = $dbms_info[$dbms]['DELIM'];
		$table_prefix = $this->config->get('table_prefix');

		if ($dbms === 'mysql')
		{
			if (version_compare($this->db->sql_server_info(true), '4.1.3', '>='))
			{
				$schema_name .= '_41';
			}
			else
			{
				$schema_name .= '_40';
			}
		}

		$db_schema_path = $this->phpbb_root_path . 'install/schemas/' . $schema_name . '_schema.sql';

		// Load database vendor specific code if there is any
		if ($this->filesystem->exists($db_schema_path))
		{
			$sql_query = @file_get_contents($db_schema_path);
			$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);
			$sql_query = $this->database_helper->remove_comments($sql_query);
			$sql_query = $this->database_helper->split_sql_file($sql_query, $delimiter);

			foreach ($sql_query as $sql)
			{
				if (!$this->db->sql_query($sql))
				{
					$error = $this->db->sql_error($this->db->get_sql_error_sql());
					$this->iohandler->add_error_message('INST_ERR_DB', $error['message']);
				}
			}

			unset($sql_query);
		}

		$change_prefix = false;

		// Generate database schema
		if ($this->filesystem->exists($this->phpbb_root_path . 'install/schemas/schema.json'))
		{
			$db_table_schema = @file_get_contents($this->phpbb_root_path . 'install/schemas/schema.json');
			$db_table_schema = json_decode($db_table_schema, true);
			$change_prefix = true;
		}
		else
		{
			global $table_prefix;

			$table_prefix = $this->config->get('table_prefix');

			if (!defined('CONFIG_TABLE'))
			{
				// We need to include the constants file for the table constants
				// when we generate the schema from the migration files.
				include ($this->phpbb_root_path . 'includes/constants.' . $this->php_ext);
			}

			$finder = new \phpbb\finder($this->filesystem, $this->phpbb_root_path, null, $this->php_ext);
			$migrator_classes = $finder->core_path('phpbb/db/migration/data/')->get_classes();
			$factory = new \phpbb\db\tools\factory();
			$db_tools = $factory->get($this->db, true);
			$schema_generator = new \phpbb\db\migration\schema_generator(
				$migrator_classes,
				new \phpbb\config\config(array()),
				$this->db,
				$db_tools,
				$this->phpbb_root_path,
				$this->php_ext,
				$table_prefix
			);
			$db_table_schema = $schema_generator->get_schema();
		}

		if (!defined('CONFIG_TABLE'))
		{
			// CONFIG_TABLE is required by sql_create_index() to check the
			// length of index names. However table_prefix is not defined
			// here yet, so we need to create the constant ourselves.
			define('CONFIG_TABLE', $table_prefix . 'config');
		}

		foreach ($db_table_schema as $table_name => $table_data)
		{
			$this->db_tools->sql_create_table(
				( ($change_prefix) ? ($table_prefix . substr($table_name, 6)) : $table_name ),
				$table_data
			);
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
		return 'TASK_CREATE_DATABASE_SCHEMA';
	}
}
