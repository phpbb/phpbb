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

/**
 * Set up database for table generation
 */
class set_up_database extends \phpbb\install\task_base
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
	protected $schema_file_path;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\config							$config
	 * @param \phpbb\install\helper\database						$db_helper
	 * @param \phpbb\filesystem\filesystem_interface				$filesystem
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler
	 * @param string												$phpbb_root_path
	 */
	public function __construct(\phpbb\install\helper\config $config,
								\phpbb\install\helper\database $db_helper,
								\phpbb\filesystem\filesystem_interface $filesystem,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler,
								$phpbb_root_path)
	{
		$dbms = $db_helper->get_available_dbms($config->get('dbms'));
		$dbms = $dbms[$config->get('dbms')]['DRIVER'];

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
		$this->database_helper	= $db_helper;
		$this->filesystem		= $filesystem;
		$this->iohandler		= $iohandler;
		$this->phpbb_root_path	= $phpbb_root_path;

		parent::__construct(false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_requirements()
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
		$this->db->sql_return_on_error(true);

		$dbms = $this->config->get('dbms');
		$dbms_info = $this->database_helper->get_available_dbms($dbms);
		$delimiter = $dbms_info[$dbms]['DELIM'];
		$table_prefix = $this->config->get('table_prefix');

		$sql_query = @file_get_contents($this->schema_file_path);
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
		return 'TASK_SETUP_DATABASE';
	}
}
