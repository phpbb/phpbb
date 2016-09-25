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
class add_default_data extends \phpbb\install\task_base
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\install\helper\database
	 */
	protected $database_helper;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\database						$db_helper	Installer's database helper
	 * @param \phpbb\install\helper\config							$config		Installer config
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler	Installer's input-output handler
	 * @param \phpbb\install\helper\container_factory				$container	Installer's DI container
	 * @param \phpbb\language\language								$language	Language service
	 * @param string												$root_path	Root path of phpBB
	 */
	public function __construct(\phpbb\install\helper\database $db_helper,
								\phpbb\install\helper\config $config,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler,
								\phpbb\install\helper\container_factory $container,
								\phpbb\language\language $language,
								$root_path)
	{
		$this->db				= $container->get('dbal.conn.driver');
		$this->database_helper	= $db_helper;
		$this->config			= $config;
		$this->iohandler		= $iohandler;
		$this->language			= $language;
		$this->phpbb_root_path	= $root_path;

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->db->sql_return_on_error(true);

		$table_prefix = $this->config->get('table_prefix');
		$dbms = $this->config->get('dbms');
		$dbms_info = $this->database_helper->get_available_dbms($dbms);

		// Get schema data from file
		$sql_query = @file_get_contents($this->phpbb_root_path . 'install/schemas/schema_data.sql');

		// Clean up SQL
		$sql_query = $this->replace_dbms_specific_sql($sql_query);
		$sql_query = preg_replace('# phpbb_([^\s]*) #i', ' ' . $table_prefix . '\1 ', $sql_query);
		$sql_query = preg_replace_callback('#\{L_([A-Z0-9\-_]*)\}#s', array($this, 'lang_replace_callback'), $sql_query);
		$sql_query = $this->database_helper->remove_comments($sql_query);
		$sql_query = $this->database_helper->split_sql_file($sql_query, $dbms_info[$dbms]['DELIM']);

		$i = $this->config->get('add_default_data_index', 0);
		$total = sizeof($sql_query);
		$sql_query = array_slice($sql_query, $i);

		foreach ($sql_query as $sql)
		{
			if (!$this->db->sql_query($sql))
			{
				$error = $this->db->sql_error($this->db->get_sql_error_sql());
				$this->iohandler->add_error_message('INST_ERR_DB', $error['message']);
			}

			$i++;

			// Stop execution if resource limit is reached
			if ($this->config->get_time_remaining() <= 0 || $this->config->get_memory_remaining() <= 0)
			{
				break;
			}
		}

		$this->config->set('add_default_data_index', $i);

		if ($i < $total)
		{
			throw new resource_limit_reached_exception();
		}
	}

	/**
	 * Process DB specific SQL
	 *
	 * @return string
	 */
	protected function replace_dbms_specific_sql($query)
	{
		if ($this->db instanceof \phpbb\db\driver\mssql_base)
		{
			$query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $query);
		}
		else if ($this->db instanceof \phpbb\db\driver\postgres)
		{
			$query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $query);
		}
		else if ($this->db instanceof \phpbb\db\driver\mysql_base)
		{
			$query = str_replace('\\', '\\\\', $query);
		}

		return $query;
	}

	/**
	 * Callback function for language replacing
	 *
	 * @param array	$matches
	 * @return string
	 */
	public function lang_replace_callback($matches)
	{
		if (!empty($matches[1]))
		{
			return $this->db->sql_escape($this->language->lang($matches[1]));
		}

		return '';
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
		return 'TASK_ADD_DEFAULT_DATA';
	}
}
