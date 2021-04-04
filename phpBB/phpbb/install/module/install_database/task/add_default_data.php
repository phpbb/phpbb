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

use Doctrine\DBAL\Connection;
use phpbb\install\database_task;
use phpbb\install\helper\config;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\sequential_task;
use phpbb\language\language;

/**
 * Create database schema
 */
class add_default_data extends database_task
{
	use sequential_task;

	/**
	 * @var Connection
	 */
	protected $db;

	/**
	 * @var database
	 */
	protected $database_helper;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param database				$db_helper	Installer's database helper
	 * @param config				$config		Installer config
	 * @param iohandler_interface	$iohandler	Installer's input-output handler
	 * @param language				$language	Language service
	 * @param string				$root_path	Root path of phpBB
	 */
	public function __construct(database $db_helper,
								config $config,
								iohandler_interface $iohandler,
								language $language,
								string $root_path)
	{
		$this->db				= self::get_doctrine_connection($db_helper, $config);
		$this->database_helper	= $db_helper;
		$this->config			= $config;
		$this->language			= $language;
		$this->phpbb_root_path	= $root_path;

		parent::__construct($this->db, $iohandler, true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
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

		$this->execute($this->config, $sql_query);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute_step($key, $value) : void
	{
		$sql = trim($value);
		switch ($sql)
		{
			case 'BEGIN':
				$this->db->beginTransaction();
			break;

			case 'COMMIT':
				$this->db->commit();
			break;

			default:
				$this->exec_sql($sql);
			break;
		}
	}

	/**
	 * Process DB specific SQL
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	protected function replace_dbms_specific_sql(string $query) : string
	{
		$dbms = $this->config->get('dbms');
		switch ($dbms)
		{
			case 'mssql_odbc':
			case 'mssqlnative':
				$query = preg_replace('#\# MSSQL IDENTITY (phpbb_[a-z_]+) (ON|OFF) \##s', 'SET IDENTITY_INSERT \1 \2;', $query);
			break;

			case 'postgres':
				$query = preg_replace('#\# POSTGRES (BEGIN|COMMIT) \##s', '\1; ', $query);
			break;

			case 'mysqli':
				$query = str_replace('\\', '\\\\', $query);
			break;
		}

		return $query;
	}

	/**
	 * Callback function for language replacing
	 *
	 * @param array	$matches
	 * @return string
	 */
	public function lang_replace_callback(array $matches) : string
	{
		if (!empty($matches[1]))
		{
			$translation = $this->language->lang($matches[1]);

			// This is might not be secure, but these queries should not be malicious anyway.
			$quoted = $this->db->quote($translation) ?: '\'' . addcslashes($translation, '\'') . '\'';
			return substr($quoted, 1, -1);
		}

		return '';
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
		return 'TASK_ADD_DEFAULT_DATA';
	}
}
