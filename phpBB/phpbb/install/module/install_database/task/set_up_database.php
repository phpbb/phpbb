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

use phpbb\filesystem\filesystem_interface;
use phpbb\install\database_task;
use phpbb\install\helper\config;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;

/**
 * Set up database for table generation
 */
class set_up_database extends database_task
{
	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var database
	 */
	protected $database_helper;

	/**
	 * @var filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var string
	 */
	protected $schema_file_path;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param database				$db_helper
	 * @param filesystem_interface	$filesystem
	 * @param iohandler_interface	$iohandler
	 * @param string				$phpbb_root_path
	 */
	public function __construct(config $config,
								database $db_helper,
								filesystem_interface $filesystem,
								iohandler_interface $iohandler,
								string $phpbb_root_path)
	{
		$this->config			= $config;
		$this->database_helper	= $db_helper;
		$this->filesystem		= $filesystem;
		$this->phpbb_root_path	= $phpbb_root_path;

		parent::__construct(
			self::get_doctrine_connection($db_helper, $config),
			$iohandler,
			false
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_requirements() : bool
	{
		$dbms = $this->config->get('dbms');
		$dbms_info = $this->database_helper->get_available_dbms($dbms);
		$schema_name = $dbms_info[$dbms]['SCHEMA'];

		if ($dbms === 'mysql')
		{
			$schema_name .= '_41';
		}

		$this->schema_file_path = $this->phpbb_root_path . 'install/schemas/' . $schema_name . '_schema.sql';

		return $this->filesystem->exists($this->schema_file_path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$dbms = $this->config->get('dbms');
		$dbms_info = $this->database_helper->get_available_dbms($dbms);
		$delimiter = $dbms_info[$dbms]['DELIM'];
		$table_prefix = $this->config->get('table_prefix');

		$sql_query = @file_get_contents($this->schema_file_path);
		$sql_query = str_replace('phpbb_', ' ' . $table_prefix, $sql_query);
		$sql_query = $this->database_helper->remove_comments($sql_query);
		$sql_query = $this->database_helper->split_sql_file($sql_query, $delimiter);

		foreach ($sql_query as $sql)
		{
			$this->exec_sql($sql);
		}

		unset($sql_query);
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
		return 'TASK_SETUP_DATABASE';
	}
}
