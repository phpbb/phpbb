<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

abstract class phpbb_database_test_case extends PHPUnit_Extensions_Database_TestCase
{
	private static $already_connected;

	protected $test_case_helpers;

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->backupStaticAttributesBlacklist += array(
			'PHP_CodeCoverage' => array('instance'),
			'PHP_CodeCoverage_Filter' => array('instance'),
			'PHP_CodeCoverage_Util' => array('ignoredLines', 'templateMethods'),
			'PHP_Timer' => array('startTimes',),
			'PHP_Token_Stream' => array('customTokens'),
			'PHP_Token_Stream_CachingFactory' => array('cache'),

			'phpbb_database_test_case' => array('already_connected'),
		);
	}

	public function get_test_case_helpers()
	{
		if (!$this->test_case_helpers)
		{
			$this->test_case_helpers = new phpbb_test_case_helpers($this);
		}

		return $this->test_case_helpers;
	}

	public function get_dbms_data($dbms)
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
			trigger_error('Database unsupported', E_USER_ERROR);
		}
	}

	public function get_database_config()
	{
		if (isset($_SERVER['PHPBB_TEST_DBMS']))
		{
			return array(
				'dbms'		=> isset($_SERVER['PHPBB_TEST_DBMS']) ? $_SERVER['PHPBB_TEST_DBMS'] : '',
				'dbhost'	=> isset($_SERVER['PHPBB_TEST_DBHOST']) ? $_SERVER['PHPBB_TEST_DBHOST'] : '',
				'dbport'	=> isset($_SERVER['PHPBB_TEST_DBPORT']) ? $_SERVER['PHPBB_TEST_DBPORT'] : '',
				'dbname'	=> isset($_SERVER['PHPBB_TEST_DBNAME']) ? $_SERVER['PHPBB_TEST_DBNAME'] : '',
				'dbuser'	=> isset($_SERVER['PHPBB_TEST_DBUSER']) ? $_SERVER['PHPBB_TEST_DBUSER'] : '',
				'dbpasswd'	=> isset($_SERVER['PHPBB_TEST_DBPASSWD']) ? $_SERVER['PHPBB_TEST_DBPASSWD'] : '',
			);
		}
		else if (file_exists(dirname(__FILE__) . '/../test_config.php'))
		{
			include(dirname(__FILE__) . '/../test_config.php');

			return array(
				'dbms'		=> $dbms,
				'dbhost'	=> $dbhost,
				'dbport'	=> $dbport,
				'dbname'	=> $dbname,
				'dbuser'	=> $dbuser,
				'dbpasswd'	=> $dbpasswd,
			);
		}
		else if (extension_loaded('sqlite') && version_compare(PHPUnit_Runner_Version::id(), '3.4.15', '>='))
		{
			// Silently use sqlite
			return array(
				'dbms'		=> 'sqlite',
				'dbhost'	=> dirname(__FILE__) . '/../phpbb_unit_tests.sqlite2', // filename
				'dbport'	=> '',
				'dbname'	=> '',
				'dbuser'	=> '',
				'dbpasswd'	=> '',
			);
		}
		else
		{
			$this->markTestSkipped('Missing test_config.php: See first error.');
		}
	}

	// NOTE: This function is not the same as split_sql_file from functions_install
	public function split_sql_file($sql, $dbms)
	{
		$dbms_data = $this->get_dbms_data($dbms);

		$sql = str_replace("\r" , '', $sql);
		$data = preg_split('/' . preg_quote($dbms_data['DELIM'], '/') . '$/m', $sql);

		$data = array_map('trim', $data);

		// The empty case
		$end_data = end($data);

		if (empty($end_data))
		{
			unset($data[key($data)]);
		}

		if ($dbms == 'sqlite')
		{
			// remove comment lines starting with # - they are not proper sqlite
			// syntax and break sqlite2
			foreach ($data as $i => $query)
			{
				$data[$i] = preg_replace('/^#.*$/m', "\n", $query);
			}
		}

		return $data;
	}

	/**
	* Retrieves a list of all tables from the database.
	*
	* @param	PDO $pdo
	* @param	string $dbms
	* @return	array(string)
	*/
	function get_tables($pdo, $dbms)
	{
		switch ($pdo)
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

		$result = $pdo->query($sql);

		$tables = array();
		while ($row = $result->fetch(PDO::FETCH_NUM))
		{
			$tables[] = current($row);
		}

		return $tables;
	}

	/**
	* Returns a PDO connection for the configured database.
	*
	* @param	array	$config		The database configuration
	* @param	array	$dbms		Information on the used DBMS.
	* @param	bool	$use_db		Whether the DSN should be tied to a
	*								particular database making it impossible
	*								to delete that database.
	* @return	PDO					The PDO database connection.
	*/
	public function new_pdo($config, $dbms, $use_db)
	{
		$dsn = $dbms['PDO'] . ':';

		switch ($dbms['PDO'])
		{
			case 'sqlite2':
				$dsn .= $config['dbhost'];
			break;

			case 'sqlsrv':
				// prefix the hostname (or DSN) with Server= so using just (local)\SQLExpress
				// works for example, further parameters can still be appended using ;x=y
				$dsn .= 'Server=';
			// no break -> rest like ODBC
			case 'odbc':
				// for ODBC assume dbhost is a suitable DSN
				// e.g. Driver={SQL Server Native Client 10.0};Server=(local)\SQLExpress;
				$dsn .= $config['dbhost'];

				if ($use_db)
				{
					$dsn .= ';Database=' . $config['dbname'];
				}
			break;

			default:
				$dsn .= 'host=' . $config['dbhost'];

				if ($use_db)
				{
					$dsn .= ';dbname=' . $config['dbname'];
				}
			break;
		}

		$pdo = new PDO($dsn, $config['dbuser'], $config['dbpasswd']);;

		// good for debug
		// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $pdo;
	}

	private function recreate_db($config, $dbms)
	{
		switch ($config['dbms'])
		{
			case 'sqlite':
				if (file_exists($config['dbhost']))
				{
					unlink($config['dbhost']);
				}
			break;

			default:
				$pdo = $this->new_pdo($config, $dbms, false);

				try
				{
					$pdo->exec('DROP DATABASE ' . $config['dbname']);
				}
				catch (PDOException $e)
                {
					// try to delete all tables if dropping the database was not possible.
					foreach ($this->get_tables() as $table)
					{
						try
						{
							$pdo->exec('DROP TABLE ' . $table);
						}
						catch (PDOException $e){} // ignore non-existent tables
					}
                }

				$pdo->exec('CREATE DATABASE ' . $config['dbname']);
			 break;
		}
	}

	private function load_schema($pdo, $config, $dbms)
	{
		if ($config['dbms'] == 'mysql')
		{
			$sth = $pdo->query('SELECT VERSION() AS version');
			$row = $sth->fetch(PDO::FETCH_ASSOC);

			if (version_compare($row['version'], '4.1.3', '>='))
			{
				$dbms['SCHEMA'] .= '_41';
			}
			else
			{
				$dbms['SCHEMA'] .= '_40';
			}
		}

		$sql = $this->split_sql_file(file_get_contents(dirname(__FILE__) . "/../../phpBB/install/schemas/{$dbms['SCHEMA']}_schema.sql"), $config['dbms']);

		foreach ($sql as $query)
		{
			$pdo->exec($query);
		}
	}

	public function getConnection()
	{
		$config = $this->get_database_config();
		$dbms = $this->get_dbms_data($config['dbms']);

		if (!self::$already_connected)
		{
			$this->recreate_db($config, $dbms);
		}

		$pdo = $this->new_pdo($config, $dbms, true);

		if (!self::$already_connected)
		{
			$this->load_schema($pdo, $config, $dbms);

			self::$already_connected = true;
		}

		return $this->createDefaultDBConnection($pdo, 'testdb');
	}

	public function new_dbal()
	{
		global $phpbb_root_path, $phpEx;

		$config = $this->get_database_config();

		require_once dirname(__FILE__) . '/../../phpBB/includes/db/' . $config['dbms'] . '.php';
		$dbal = 'dbal_' . $config['dbms'];
		$db = new $dbal();
		$db->sql_connect($config['dbhost'], $config['dbuser'], $config['dbpasswd'], $config['dbname'], $config['dbport']);

		return $db;
	}

	public function assertSqlResultEquals($expected, $sql, $message = '')
	{
		$db = $this->new_dbal();

		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$this->assertEquals($expected, $rows, $message);
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$this->get_test_case_helpers()->setExpectedTriggerError($errno, $message);
	}
}
