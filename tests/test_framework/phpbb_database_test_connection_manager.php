<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_install.php';
require_once dirname(__FILE__) . '/phpbb_database_connection_odbc_pdo_wrapper.php';

class phpbb_database_test_connection_manager
{
	private $config;
	private $dbms;
	private $pdo;

	/**
	* Constructor
	*
	* @param	array	$config		Tests database configuration as returned by
	* 								phpbb_database_test_case::get_database_config()
	*/
	public function __construct($config)
	{
		$this->config = $config;
		$this->dbms = $this->get_dbms_data($this->config['dbms']);
	}

	/**
	* Return the current PDO instance
	*/
	public function get_pdo()
	{
		return $this->pdo;
	}

	/**
	* Creates a PDO connection for the configured database.
	*
	* @param	bool	$use_db		Whether the DSN should be tied to a
	*								particular database making it impossible
	*								to delete that database.
	*/
	public function connect($use_db = true)
	{
		$dsn = $this->dbms['PDO'] . ':';

		switch ($this->dbms['PDO'])
		{
			case 'sqlite2':
				$dsn .= $this->config['dbhost'];
			break;

			case 'sqlsrv':
				// prefix the hostname (or DSN) with Server= so using just (local)\SQLExpress
				// works for example, further parameters can still be appended using ;x=y
				$dsn .= 'Server=';
			// no break -> rest like ODBC
			case 'odbc':
				// for ODBC assume dbhost is a suitable DSN
				// e.g. Driver={SQL Server Native Client 10.0};Server=(local)\SQLExpress;
				$dsn .= $this->config['dbhost'];

				// Primarily for MSSQL Native/Azure as ODBC needs it in $dbhost, attached to the Server param
				if ($this->config['dbport'])
				{
					$port_delimiter = (defined('PHP_OS') && substr(PHP_OS, 0, 3) === 'WIN') ? ',' : ':';
					$dsn .= $port_delimiter . $this->config['dbport'];
				}

				if ($use_db)
				{
					$dsn .= ';Database=' . $this->config['dbname'];
				}
			break;

			default:
				$dsn .= 'host=' . $this->config['dbhost'];

				if ($this->config['dbport'])
				{
					$dsn .= ';port=' . $this->config['dbport'];
				}

				if ($use_db)
				{
					$dsn .= ';dbname=' . $this->config['dbname'];
				}
				else if ($this->dbms['PDO'] == 'pgsql')
				{
					// Postgres always connects to a
					// database. If the database is not
					// specified here, but the username
					// is specified, then connection
					// will be to the database named
					// as the username.
					//
					// For greater compatibility, connect
					// instead to postgres database which
					// should always exist:
					// http://www.postgresql.org/docs/9.0/static/manage-ag-templatedbs.html
					$dsn .= ';dbname=postgres';
				}
			break;
		}

		// These require different connection strings on the phpBB side than they do in PDO
		// so you must provide a DSN string for ODBC separately
		if (!empty($this->config['custom_dsn']) && ($this->config['dbms'] == 'mssql' || $this->config['dbms'] == 'firebird'))
		{
			$dsn = 'odbc:' . $this->config['custom_dsn'];
		}

		try
		{
			switch ($this->config['dbms'])
			{
				case 'mssql':
				case 'mssql_odbc':
					$this->pdo = new phpbb_database_connection_odbc_pdo_wrapper('mssql', 0, $dsn, $this->config['dbuser'], $this->config['dbpasswd']);
				break;

				case 'firebird':
					if (!empty($this->config['custom_dsn']))
					{
						$this->pdo = new phpbb_database_connection_odbc_pdo_wrapper('firebird', 0, $dsn, $this->config['dbuser'], $this->config['dbpasswd']);
						break;
					}
					// Fall through if they're using the firebird PDO driver and not the generic ODBC driver

				default:
					$this->pdo = new PDO($dsn, $this->config['dbuser'], $this->config['dbpasswd']);
				break;
			}
		}
		catch (PDOException $e)
		{
			$cleaned_dsn = str_replace($this->config['dbpasswd'], '*password*', $dsn);
			throw new Exception("Unable do connect to $cleaned_dsn using PDO with error: {$e->getMessage()}");
		}

		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	* Load the phpBB database schema into the database
	*/
	public function load_schema()
	{
		$this->ensure_connected(__METHOD__);

		$directory = dirname(__FILE__) . '/../../phpBB/install/schemas/';
		$this->load_schema_from_file($directory);
	}

	/**
	* Drop the database if it exists and re-create it
	*
	* Note: This does not load the schema, and it is suggested
	* to re-connect after calling to get use_db isolation.
	*/
	public function recreate_db()
	{
		switch ($this->config['dbms'])
		{
			case 'sqlite':
				if (file_exists($this->config['dbhost']))
				{
					unlink($this->config['dbhost']);
				}
			break;

			case 'firebird':
				$this->connect();
				// Drop all of the tables
				foreach ($this->get_tables() as $table)
				{
					$this->pdo->exec('DROP TABLE ' . $table);
				}
				$this->purge_extras();
			break;

			case 'oracle':
				$this->connect();
				// Drop all of the tables
				foreach ($this->get_tables() as $table)
				{
					$this->pdo->exec('DROP TABLE ' . $table . ' CASCADE CONSTRAINTS');
				}
				$this->purge_extras();
			break;

			default:
				$this->connect(false);

				try
				{
					$this->pdo->exec('DROP DATABASE ' . $this->config['dbname']);

					try
					{
						$this->pdo->exec('CREATE DATABASE ' . $this->config['dbname']);
					}
					catch (PDOException $e)
					{
						throw new Exception("Unable to re-create database: {$e->getMessage()}");
					}
				}
				catch (PDOException $e)
				{
					// try to delete all tables if dropping the database was not possible.
					foreach ($this->get_tables() as $table)
					{
						$this->pdo->exec('DROP TABLE ' . $table);
					}
					$this->purge_extras();
				}
			 break;
		}
	}

	/**
	* Retrieves a list of all tables from the database.
	*
	* @return	array(string)
	*/
	public function get_tables()
	{
		$this->ensure_connected(__METHOD__);

		switch ($this->config['dbms'])
		{
			case 'mysql':
			case 'mysql4':
			case 'mysqli':
				$sql = 'SHOW TABLES';
			break;

			case 'sqlite':
				$sql = 'SELECT name
					FROM sqlite_master
					WHERE type = "table"';
			break;

			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
				$sql = "SELECT name
					FROM sysobjects
					WHERE type='U'";
			break;

			case 'postgres':
				$sql = 'SELECT relname
					FROM pg_stat_user_tables';
			break;

			case 'firebird':
				$sql = 'SELECT rdb$relation_name
					FROM rdb$relations
					WHERE rdb$view_source is null
						AND rdb$system_flag = 0';
			break;

			case 'oracle':
				$sql = 'SELECT table_name
					FROM USER_TABLES';
			break;
		}

		$result = $this->pdo->query($sql);

		$tables = array();
		while ($row = $result->fetch(PDO::FETCH_NUM))
		{
			$tables[] = current($row);
		}

		return $tables;
	}

	/**
	* Throw an exception if not connected
	*/
	protected function ensure_connected($method_name)
	{
		if (null === $this->pdo)
		{
			throw new Exception(sprintf('You must connect before calling %s', $method_name));
		}
	}

	/**
	* Compile the correct schema filename (as per create_schema_files) and
	* load it into the database.
	*/
	protected function load_schema_from_file($directory)
	{
		$schema = $this->dbms['SCHEMA'];
		
		if ($this->config['dbms'] == 'mysql')
		{
			$sth = $this->pdo->query('SELECT VERSION() AS version');
			$row = $sth->fetch(PDO::FETCH_ASSOC);

			if (version_compare($row['version'], '4.1.3', '>='))
			{
				$schema .= '_41';
			}
			else
			{
				$schema .= '_40';
			}
		}

		$filename = $directory . $schema . '_schema.sql';

		$queries = file_get_contents($filename);
		$sql = phpbb_remove_comments($queries);
		
		$sql = split_sql_file($sql, $this->dbms['DELIM']);

		foreach ($sql as $query)
		{
			$this->pdo->exec($query);
		}
	}

	/**
	* Map a phpBB dbms driver name to dbms data array
	*/
	protected function get_dbms_data($dbms)
	{
		$available_dbms = array(
			'firebird'	=> array(
				'SCHEMA'		=> 'firebird',
				'DELIM'			=> ';;',
				'PDO'			=> 'firebird',
			),
			'mysqli'	=> array(
				'SCHEMA'		=> 'mysql_41',
				'DELIM'			=> ';',
				'PDO'			=> 'mysql',
			),
			'mysql'		=> array(
				'SCHEMA'		=> 'mysql',
				'DELIM'			=> ';',
				'PDO'			=> 'mysql',
			),
			'mssql'		=> array(
				'SCHEMA'		=> 'mssql',
				'DELIM'			=> 'GO',
				'PDO'			=> 'odbc',
			),
			'mssql_odbc'=>	array(
				'SCHEMA'		=> 'mssql',
				'DELIM'			=> 'GO',
				'PDO'			=> 'odbc',
			),
			'mssqlnative'		=> array(
				'SCHEMA'		=> 'mssql',
				'DELIM'			=> 'GO',
				'PDO'			=> 'sqlsrv',
			),
			'oracle'	=>	array(
				'SCHEMA'		=> 'oracle',
				'DELIM'			=> '/',
				'PDO'			=> 'oci',
			),
			'postgres' => array(
				'SCHEMA'		=> 'postgres',
				'DELIM'			=> ';',
				'PDO'			=> 'pgsql',
			),
			'sqlite'		=> array(
				'SCHEMA'		=> 'sqlite',
				'DELIM'			=> ';',
				'PDO'			=> 'sqlite2',
			),
		);

		if (isset($available_dbms[$dbms]))
		{
			return $available_dbms[$dbms];
		}
		else
		{
			$message = "Supplied dbms \"$dbms\" is not a valid phpBB dbms, must be one of: ";
			$message .= implode(', ', array_keys($available_dbms));
			throw new Exception($message);
		}
	}

	/**
	* Removes extra objects from a database. This is for cases where dropping the database fails.
	*/
	public function purge_extras()
	{
		$this->ensure_connected(__METHOD__);
		$queries = array();

		switch ($this->config['dbms'])
		{
			case 'firebird':
				$sql = 'SELECT RDB$GENERATOR_NAME
					FROM RDB$GENERATORS
					WHERE RDB$SYSTEM_FLAG = 0';
				$result = $this->pdo->query($sql);

				while ($row = $result->fetch(PDO::FETCH_NUM))
				{
					$queries[] = 'DROP GENERATOR ' . current($row);
				}
			break;

			case 'oracle':
				$sql = 'SELECT sequence_name
					FROM USER_SEQUENCES';
				$result = $this->pdo->query($sql);

				while ($row = $result->fetch(PDO::FETCH_NUM))
				{
					$queries[] = 'DROP SEQUENCE ' . current($row);
				}
			break;
		}

		foreach ($queries as $query)
		{
			$this->pdo->exec($query);
		}
	}
}
