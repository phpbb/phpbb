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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_install.php';
require_once dirname(__FILE__) . '/phpbb_database_connection_odbc_pdo_wrapper.php';

class phpbb_database_test_connection_manager
{
	/** @var array */
	private $config;
	/** @var array */
	private $dbms;
	/** @var \PDO */
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
			case 'sqlite':	// SQLite3 driver
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
		if (!empty($this->config['custom_dsn']) && $this->config['dbms'] == 'phpbb\db\driver\mssql')
		{
			$dsn = 'odbc:' . $this->config['custom_dsn'];
		}

		try
		{
			switch ($this->config['dbms'])
			{
				case 'phpbb\db\driver\mssql':
				case 'phpbb\db\driver\mssql_odbc':
					$this->pdo = new phpbb_database_connection_odbc_pdo_wrapper('mssql', 0, $dsn, $this->config['dbuser'], $this->config['dbpasswd']);
				break;

				default:
					$this->pdo = new PDO($dsn, $this->config['dbuser'], $this->config['dbpasswd']);
				break;
			}
		}
		catch (PDOException $e)
		{
			$cleaned_dsn = str_replace($this->config['dbpasswd'], '*password*', $dsn);
			throw new Exception("Unable to connect to $cleaned_dsn using PDO with error: {$e->getMessage()}");
		}

		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		switch ($this->config['dbms'])
		{
			case 'phpbb\db\driver\mysql':
			case 'phpbb\db\driver\mysqli':
				$this->pdo->exec('SET NAMES utf8');

				/*
				* The phpBB MySQL drivers set the STRICT_ALL_TABLES and
				* STRICT_TRANS_TABLES flags/modes, so as a minimum requirement
				* we want to make sure those are set for the PDO side of the
				* test suite.
				*
				* The TRADITIONAL flag implies STRICT_ALL_TABLES and
				* STRICT_TRANS_TABLES as well as other useful strictness flags
				* the phpBB MySQL driver does not set.
				*/
				$this->pdo->exec("SET SESSION sql_mode='TRADITIONAL'");
			break;

			default:
		}
	}

	/**
	* Load the phpBB database schema into the database
	*/
	public function load_schema($db)
	{
		$this->ensure_connected(__METHOD__);

		$directory = dirname(__FILE__) . '/../../phpBB/install/schemas/';
		$this->load_schema_from_file($directory, $db);
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
			case 'phpbb\db\driver\sqlite':
			case 'phpbb\db\driver\sqlite3':
				$this->connect();
				// Drop all of the tables
				foreach ($this->get_tables() as $table)
				{
					$this->pdo->exec('DROP TABLE ' . $table);
				}
				$this->purge_extras();
			break;

			case 'phpbb\db\driver\oracle':
				$this->connect();
				// Drop all of the tables
				foreach ($this->get_tables() as $table)
				{
					$this->pdo->exec('DROP TABLE ' . $table . ' CASCADE CONSTRAINTS');
				}
				$this->purge_extras();
			break;

			case 'phpbb\db\driver\postgres':
				$this->connect();
				// Drop all of the tables
				foreach ($this->get_tables() as $table)
				{
					$this->pdo->exec('DROP TABLE ' . $table . ' CASCADE');
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
			case 'phpbb\db\driver\mysql':
			case 'phpbb\db\driver\mysqli':
				$sql = 'SHOW TABLES';
			break;

			case 'phpbb\db\driver\sqlite':
				$sql = 'SELECT name
					FROM sqlite_master
					WHERE type = "table"';
			break;

			case 'phpbb\db\driver\sqlite3':
				$sql = 'SELECT name
					FROM sqlite_master
					WHERE type = "table"
						AND name <> "sqlite_sequence"';
			break;

			case 'phpbb\db\driver\mssql':
			case 'phpbb\db\driver\mssql_odbc':
			case 'phpbb\db\driver\mssqlnative':
				$sql = "SELECT name
					FROM sysobjects
					WHERE type='U'";
			break;

			case 'phpbb\db\driver\postgres':
				$sql = 'SELECT relname
					FROM pg_stat_user_tables';
			break;

			case 'phpbb\db\driver\oracle':
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
	protected function load_schema_from_file($directory, \phpbb\db\driver\driver_interface $db)
	{
		$schema = $this->dbms['SCHEMA'];

		if ($this->config['dbms'] == 'phpbb\db\driver\mysql')
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

		if (file_exists($filename))
		{
			$queries = file_get_contents($filename);
			$sql = phpbb_remove_comments($queries);

			$sql = split_sql_file($sql, $this->dbms['DELIM']);

			foreach ($sql as $query)
			{
				$this->pdo->exec($query);
			}
		}

		// Ok we have the db info go ahead and work on building the table
		if (file_exists($directory . 'schema.json'))
		{
			$db_table_schema = file_get_contents($directory . 'schema.json');
			$db_table_schema = json_decode($db_table_schema, true);
		}
		else
		{
			global $phpbb_root_path, $phpEx, $table_prefix;

			$finder = new \phpbb\finder(new \phpbb\filesystem\filesystem(), $phpbb_root_path, null, $phpEx);
			$classes = $finder->core_path('phpbb/db/migration/data/')
				->get_classes();

			$db = new \phpbb\db\driver\sqlite();
			$factory = new \phpbb\db\tools\factory();
			$db_tools = $factory->get($db, true);

			$schema_generator = new \phpbb\db\migration\schema_generator($classes, new \phpbb\config\config(array()), $db, $db_tools, $phpbb_root_path, $phpEx, $table_prefix);
			$db_table_schema = $schema_generator->get_schema();
		}

		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($db, true);
		foreach ($db_table_schema as $table_name => $table_data)
		{
			$queries = $db_tools->sql_create_table(
				$table_name,
				$table_data
			);

			foreach ($queries as $query)
			{
				if ($query === 'begin')
				{
					$this->pdo->beginTransaction();
				}
				else if ($query === 'commit')
				{
					$this->pdo->commit();
				}
				else
				{
					$this->pdo->exec($query);
				}
			}
		}
	}

	/**
	* Map a phpBB dbms driver name to dbms data array
	*/
	protected function get_dbms_data($dbms)
	{
		$available_dbms = array(
			'phpbb\db\driver\mysqli'	=> array(
				'SCHEMA'		=> 'mysql_41',
				'DELIM'			=> ';',
				'PDO'			=> 'mysql',
			),
			'phpbb\db\driver\mysql'		=> array(
				'SCHEMA'		=> 'mysql',
				'DELIM'			=> ';',
				'PDO'			=> 'mysql',
			),
			'phpbb\db\driver\mssql'		=> array(
				'SCHEMA'		=> 'mssql',
				'DELIM'			=> 'GO',
				'PDO'			=> 'odbc',
			),
			'phpbb\db\driver\mssql_odbc'=>	array(
				'SCHEMA'		=> 'mssql',
				'DELIM'			=> 'GO',
				'PDO'			=> 'odbc',
			),
			'phpbb\db\driver\mssqlnative'		=> array(
				'SCHEMA'		=> 'mssql',
				'DELIM'			=> 'GO',
				'PDO'			=> 'sqlsrv',
			),
			'phpbb\db\driver\oracle'	=>	array(
				'SCHEMA'		=> 'oracle',
				'DELIM'			=> '/',
				'PDO'			=> 'oci',
			),
			'phpbb\db\driver\postgres' => array(
				'SCHEMA'		=> 'postgres',
				'DELIM'			=> ';',
				'PDO'			=> 'pgsql',
			),
			'phpbb\db\driver\sqlite'		=> array(
				'SCHEMA'		=> 'sqlite',
				'DELIM'			=> ';',
				'PDO'			=> 'sqlite2',
			),
			'phpbb\db\driver\sqlite3'		=> array(
				'SCHEMA'		=> 'sqlite',
				'DELIM'			=> ';',
				'PDO'			=> 'sqlite',
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
			case 'phpbb\db\driver\oracle':
				$sql = 'SELECT sequence_name
					FROM USER_SEQUENCES';
				$result = $this->pdo->query($sql);

				while ($row = $result->fetch(PDO::FETCH_NUM))
				{
					$queries[] = 'DROP SEQUENCE ' . current($row);
				}
			break;

			case 'phpbb\db\driver\postgres':
				$sql = 'SELECT sequence_name
					FROM information_schema.sequences';
				$result = $this->pdo->query($sql);

				while ($row = $result->fetch(PDO::FETCH_NUM))
				{
					$queries[] = 'DROP SEQUENCE ' . current($row);
				}

				$queries[] = 'DROP TYPE IF EXISTS varchar_ci CASCADE';
			break;
		}

		foreach ($queries as $query)
		{
			$this->pdo->exec($query);
		}
	}

	/**
	* Performs synchronisations on the database after a fixture has been loaded
	*
	* @param	PHPUnit_Extensions_Database_DataSet_XmlDataSet	$xml_data_set		Information about the tables contained within the loaded fixture
	*
	* @return null
	*/
	public function post_setup_synchronisation($xml_data_set)
	{
		$table_names = $xml_data_set->getTableNames();

		$tables = array();
		foreach ($table_names as $table)
		{
			$tables[$table] = $xml_data_set->getTableMetaData($table)->getColumns();
		}

		$this->database_synchronisation($tables);
	}

	/**
	* Performs synchronisations on the database after a fixture has been loaded
	*
	* @param	array	$table_column_map		Array of tables/columns to synchronise
	*											array(table1 => array(column1, column2))
	*
	* @return null
	*/
	public function database_synchronisation($table_column_map)
	{
		$this->ensure_connected(__METHOD__);
		$queries = array();

		// Get escaped versions of the table names to synchronise
		$table_names = array_map(array($this->pdo, 'PDO::quote'), array_keys($table_column_map));

		switch ($this->config['dbms'])
		{
			case 'phpbb\db\driver\oracle':
				// Get all of the information about the sequences
				$sql = "SELECT t.table_name, tc.column_name, d.referenced_name as sequence_name, s.increment_by, s.min_value
					FROM USER_TRIGGERS t
					JOIN USER_DEPENDENCIES d ON (d.name = t.trigger_name)
					JOIN USER_TRIGGER_COLS tc ON (tc.trigger_name = t.trigger_name)
					JOIN USER_SEQUENCES s ON (s.sequence_name = d.referenced_name)
					WHERE d.referenced_type = 'SEQUENCE'
						AND d.type = 'TRIGGER'
						AND t.table_name IN (" . implode(', ', array_map('strtoupper', $table_names)) . ')';

				$result = $this->pdo->query($sql);

				while ($row = $result->fetch(PDO::FETCH_ASSOC))
				{
					// Get the current max value of the table
					$sql = "SELECT MAX({$row['COLUMN_NAME']}) AS max FROM {$row['TABLE_NAME']}";
					$max_result = $this->pdo->query($sql);
					$max_row = $max_result->fetch(PDO::FETCH_ASSOC);

					if (!$max_row)
					{
						continue;
					}

					$max_val = (int) $max_row['MAX'];
					$max_val++;

					/**
					* This is not the "proper" way, but the proper way does not allow you to completely reset
					* tables with no rows since you have to select the next value to make the change go into effect.
					* You would have to go past the minimum value to set it correctly, but that's illegal.
					* Since we have no objects attached to our sequencers (triggers aren't attached), this works fine.
					*/
					$queries[] = 'DROP SEQUENCE ' . $row['SEQUENCE_NAME'];
					$queries[] = "CREATE SEQUENCE {$row['SEQUENCE_NAME']}
									MINVALUE {$row['MIN_VALUE']}
									INCREMENT BY {$row['INCREMENT_BY']}
									START WITH $max_val";
				}
			break;

			case 'phpbb\db\driver\postgres':
				// Get the sequences attached to the tables
				$sql = 'SELECT column_name, table_name FROM information_schema.columns
					WHERE table_name IN (' . implode(', ', $table_names) . ")
						AND strpos(column_default, '_seq''::regclass') > 0";
				$result = $this->pdo->query($sql);

				$setval_queries = array();
				while ($row = $result->fetch(PDO::FETCH_ASSOC))
				{
					// Get the columns used in the fixture for this table
					$column_names = $table_column_map[$row['table_name']];

					// Skip sequences that weren't specified in the fixture
					if (!in_array($row['column_name'], $column_names))
					{
						continue;
					}

					// Get the old value if it exists, or use 1 if it doesn't
					$sql = "SELECT COALESCE((SELECT MAX({$row['column_name']}) + 1 FROM {$row['table_name']}), 1) AS val";
					$result_max = $this->pdo->query($sql);
					$row_max = $result_max->fetch(PDO::FETCH_ASSOC);

					if ($row_max)
					{
						$seq_name = $this->pdo->quote($row['table_name'] . '_seq');
						$max_val = (int) $row_max['val'];

						// The last parameter is false so that the system doesn't increment it again
						$setval_queries[] = "SETVAL($seq_name, $max_val, false)";
					}
				}

				// Combine all of the SETVALs into one query
				if (sizeof($setval_queries))
				{
					$queries[] = 'SELECT ' . implode(', ', $setval_queries);
				}
			break;

			case 'phpbb\db\driver\sqlite3':
				/**
				* Just delete all of the sequences. When an insertion occurs, the sequence will be automatically
				* re-created from the key with the AUTOINCREMENT attribute
				*/
				$queries[] = 'DELETE FROM sqlite_sequence';
			break;
		}

		foreach ($queries as $query)
		{
			$this->pdo->exec($query);
		}
	}
}
