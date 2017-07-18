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

namespace phpbb\install\helper;

use phpbb\install\exception\invalid_dbms_exception;

/**
 * Database related general functionality for installer
 */
class database
{
	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var array
	 */
	protected $supported_dbms = array(
		// Note: php 5.5 alpha 2 deprecated mysql.
		// Keep mysqli before mysql in this list.
		'mysqli'	=> array(
			'LABEL'			=> 'MySQL with MySQLi Extension',
			'SCHEMA'		=> 'mysql_41',
			'MODULE'		=> 'mysqli',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\mysqli',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'mysql'		=> array(
			'LABEL'			=> 'MySQL',
			'SCHEMA'		=> 'mysql',
			'MODULE'		=> 'mysql',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\mysql',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'mssql_odbc'=>	array(
			'LABEL'			=> 'MS SQL Server [ ODBC ]',
			'SCHEMA'		=> 'mssql',
			'MODULE'		=> 'odbc',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\mssql_odbc',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'mssqlnative'		=> array(
			'LABEL'			=> 'MS SQL Server 2005+ [ Native ]',
			'SCHEMA'		=> 'mssql',
			'MODULE'		=> 'sqlsrv',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\mssqlnative',
			'AVAILABLE'		=> true,
			'2.0.x'			=> false,
		),
		'oracle'	=>	array(
			'LABEL'			=> 'Oracle',
			'SCHEMA'		=> 'oracle',
			'MODULE'		=> 'oci8',
			'DELIM'			=> '/',
			'DRIVER'		=> 'phpbb\db\driver\oracle',
			'AVAILABLE'		=> true,
			'2.0.x'			=> false,
		),
		'postgres' => array(
			'LABEL'			=> 'PostgreSQL 8.3+',
			'SCHEMA'		=> 'postgres',
			'MODULE'		=> 'pgsql',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\postgres',
			'AVAILABLE'		=> true,
			'2.0.x'			=> true,
		),
		'sqlite3'		=> array(
			'LABEL'			=> 'SQLite3',
			'SCHEMA'		=> 'sqlite',
			'MODULE'		=> 'sqlite3',
			'DELIM'			=> ';',
			'DRIVER'		=> 'phpbb\db\driver\sqlite3',
			'AVAILABLE'		=> true,
			'2.0.x'			=> false,
		),
	);

	/**
	 * Constructor
	 *
	 * @param \phpbb\filesystem\filesystem_interface	$filesystem			Filesystem interface
	 * @param string									$phpbb_root_path	Path to phpBB's root
	 */
	public function __construct(\phpbb\filesystem\filesystem_interface $filesystem, $phpbb_root_path)
	{
		$this->filesystem		= $filesystem;
		$this->phpbb_root_path	= $phpbb_root_path;
	}

	/**
	 * Returns an array of available DBMS supported by phpBB
	 *
	 * If a DBMS is specified it will only return data for that DBMS
	 * and will load its extension if necessary.
	 *
	 * @param	mixed	$dbms				name of the DBMS that's info is required or false for all DBMS info
	 * @param	bool	$return_unavailable	set it to true if you expect unavailable but supported DBMS
	 * 										returned as well
	 * @param	bool	$only_20x_options	set it to true if you only want to recover 2.0.x options
	 *
	 * @return	array	Array of available and supported DBMS
	 */
	public function get_available_dbms($dbms = false, $return_unavailable = false, $only_20x_options = false)
	{
		$available_dbms = $this->supported_dbms;

		if ($dbms)
		{
			if (isset($this->supported_dbms[$dbms]))
			{
				$available_dbms = array($dbms => $this->supported_dbms[$dbms]);
			}
			else
			{
				return array();
			}
		}

		$any_dbms_available = false;
		foreach ($available_dbms as $db_name => $db_array)
		{
			if ($only_20x_options && !$db_array['2.0.x'])
			{
				if ($return_unavailable)
				{
					$available_dbms[$db_name]['AVAILABLE'] = false;
				}
				else
				{
					unset($available_dbms[$db_name]);
				}

				continue;
			}

			$dll = $db_array['MODULE'];
			if (!@extension_loaded($dll))
			{
				if ($return_unavailable)
				{
					$available_dbms[$db_name]['AVAILABLE'] = false;
				}
				else
				{
					unset($available_dbms[$db_name]);
				}

				continue;
			}

			$any_dbms_available = true;
		}

		if ($return_unavailable)
		{
			$available_dbms['ANY_DB_SUPPORT'] = $any_dbms_available;
		}

		return $available_dbms;
	}

	/**
	 * Removes "/* style" as well as "# style" comments from $input.
	 *
	 * @param string $sql_query	Input string
	 *
	 * @return string Input string with comments removed
	 */
	public function remove_comments($sql_query)
	{
		// Remove /* */ comments (http://ostermiller.org/findcomment.html)
		$sql_query = preg_replace('#/\*(.|[\r\n])*?\*/#', "\n", $sql_query);

		// Remove # style comments
		$sql_query = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $sql_query));

		return $sql_query;
	}

	/**
	 * split_sql_file() will split an uploaded sql file into single sql statements.
	 *
	 * Note: expects trim() to have already been run on $sql.
	 *
	 * @param	string	$sql		SQL statements
	 * @param	string	$delimiter	Delimiter between sql statements
	 *
	 * @return array Array of sql statements
	 */
	public function split_sql_file($sql, $delimiter)
	{
		$sql = str_replace("\r" , '', $sql);
		$data = preg_split('/' . preg_quote($delimiter, '/') . '$/m', $sql);

		$data = array_map('trim', $data);

		// The empty case
		$end_data = end($data);

		if (empty($end_data))
		{
			unset($data[key($data)]);
		}

		return $data;
	}

	/**
	 * Validates table prefix
	 *
	 * @param string	$dbms			The selected dbms
	 * @param string	$table_prefix	The table prefix to validate
	 *
	 * @return bool|array	true if table prefix is valid, array of errors otherwise
	 *
	 * @throws \phpbb\install\exception\invalid_dbms_exception When $dbms is not a valid
	 */
	public function validate_table_prefix($dbms, $table_prefix)
	{
		$errors = array();

		if (!preg_match('#^[a-zA-Z][a-zA-Z0-9_]*$#', $table_prefix))
		{
			$errors[] = array(
				'title' => 'INST_ERR_DB_INVALID_PREFIX',
			);
		}

		// Do dbms specific checks
		$dbms_info = $this->get_available_dbms($dbms);
		switch ($dbms_info[$dbms]['SCHEMA'])
		{
			case 'mysql':
			case 'mysql_41':
				$prefix_length = 36;
			break;
			case 'mssql':
				$prefix_length = 90;
			break;
			case 'oracle':
				$prefix_length = 6;
			break;
			case 'postgres':
				$prefix_length = 36;
			break;
			case 'sqlite':
				$prefix_length = 200;
			break;
			default:
				throw new invalid_dbms_exception();
			break;
		}

		// Check the prefix length to ensure that index names are not too long
		if (strlen($table_prefix) > $prefix_length)
		{
			$errors[] = array(
				'title' => array('INST_ERR_PREFIX_TOO_LONG', $prefix_length),
			);
		}

		return (empty($errors)) ? true : $errors;
	}

	/**
	 * Check if the user provided database parameters are correct
	 *
	 * This function checks the database connection data and also checks for
	 * any other problems that could cause an error during the installation
	 * such as if there is any database table names conflicting.
	 *
	 * Note: The function assumes that $table_prefix has been already validated
	 * with validate_table_prefix().
	 *
	 * @param string	$dbms			Selected database type
	 * @param string	$dbhost			Database host address
	 * @param int		$dbport			Database port number
	 * @param string	$dbuser			Database username
	 * @param string	$dbpass			Database password
	 * @param string	$dbname			Database name
	 * @param string	$table_prefix	Database table prefix
	 *
	 * @return array|bool	Returns true if test is successful, array of errors otherwise
	 */
	public function check_database_connection($dbms, $dbhost, $dbport, $dbuser, $dbpass, $dbname, $table_prefix)
	{
		$dbms_info = $this->get_available_dbms($dbms);
		$dbms_info = $dbms_info[$dbms];
		$errors = array();

		// Instantiate it and set return on error true
		/** @var \phpbb\db\driver\driver_interface $db */
		$db = new $dbms_info['DRIVER'];
		$db->sql_return_on_error(true);

		// Check that we actually have a database name before going any further
		if (!in_array($dbms_info['SCHEMA'], array('sqlite', 'oracle'), true) && $dbname === '')
		{
			$errors[] = array(
				'title' => 'INST_ERR_DB_NO_NAME',
			);
		}

		// Make sure we don't have a daft user who thinks having the SQLite database in the forum directory is a good idea
		if ($dbms_info['SCHEMA'] === 'sqlite'
			&& stripos($this->filesystem->realpath($dbhost), $this->filesystem->realpath($this->phpbb_root_path) === 0))
		{
			$errors[] = array(
				'title' =>'INST_ERR_DB_FORUM_PATH',
			);
		}

		// Check if SQLite database is writable
		if ($dbms_info['SCHEMA'] === 'sqlite'
			&& (!$this->filesystem->is_writable($dbhost) || !$this->filesystem->is_writable(pathinfo($dbhost, PATHINFO_DIRNAME))))
		{
			$errors[] = array(
				'title' =>'INST_ERR_DB_NO_WRITABLE',
			);
		}

		// Try to connect to db
		if (is_array($db->sql_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport, false, true)))
		{
			$db_error = $db->sql_error();
			$errors[] = array(
				'title' => 'INST_ERR_DB_CONNECT',
				'description' => ($db_error['message']) ? utf8_convert_message($db_error['message']) : 'INST_ERR_DB_NO_ERROR',
			);
		}
		else
		{
			// Check if there is any table name collisions
			$temp_prefix = strtolower($table_prefix);
			$table_ary = array(
				$temp_prefix . 'attachments',
				$temp_prefix . 'config',
				$temp_prefix . 'sessions',
				$temp_prefix . 'topics',
				$temp_prefix . 'users',
			);

			$db_tools_factory = new \phpbb\db\tools\factory();
			$db_tools = $db_tools_factory->get($db);
			$tables = $db_tools->sql_list_tables();
			$tables = array_map('strtolower', $tables);
			$table_intersect = array_intersect($tables, $table_ary);

			if (count($table_intersect))
			{
				$errors[] = array(
					'title' => 'INST_ERR_PREFIX',
				);
			}

			// Check if database version is supported
			switch ($dbms)
			{
				case 'mysqli':
					if (version_compare($db->sql_server_info(true), '4.1.3', '<'))
					{
						$errors[] = array(
							'title' => 'INST_ERR_DB_NO_MYSQLI',
						);
					}
				break;
				case 'sqlite3':
					if (version_compare($db->sql_server_info(true), '3.6.15', '<'))
					{
						$errors[] = array(
							'title' => 'INST_ERR_DB_NO_SQLITE3',
						);
					}
				break;
				case 'oracle':
					$sql = "SELECT *
						FROM NLS_DATABASE_PARAMETERS
						WHERE PARAMETER = 'NLS_RDBMS_VERSION'
							OR PARAMETER = 'NLS_CHARACTERSET'";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$stats[$row['parameter']] = $row['value'];
					}
					$db->sql_freeresult($result);

					if (version_compare($stats['NLS_RDBMS_VERSION'], '9.2', '<') && $stats['NLS_CHARACTERSET'] !== 'UTF8')
					{
						$errors[] = array(
							'title' => 'INST_ERR_DB_NO_ORACLE',
						);
					}
				break;
				case 'postgres':
					$sql = "SHOW server_encoding;";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if ($row['server_encoding'] !== 'UNICODE' && $row['server_encoding'] !== 'UTF8')
					{
						$errors[] = array(
							'title' => 'INST_ERR_DB_NO_POSTGRES',
						);
					}
				break;
			}
		}

		return (empty($errors)) ? true : $errors;
	}
}
