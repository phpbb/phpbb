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
	protected $test_case_helpers;

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
				'PDO'			=> 'odbc',
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
		static $show_error = true;

		if (file_exists('test_config.php'))
		{
			include('test_config.php');

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
				'dbhost'	=> 'phpbb_unit_tests.sqlite2', // filename
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
			// trim # off query to satisfy sqlite
			foreach ($data as $i => $query)
			{
				$data[$i] = preg_replace('/^#.*$/m', "\n", $query);
			}
		}

		return $data;
	}

	public function getConnection()
	{
		static $already_connected;

		$database_config = $this->get_database_config();

		$dbms_data = $this->get_dbms_data($database_config['dbms']);

		if ($already_connected)
		{
			if ($database_config['dbms'] == 'sqlite')
			{
				$pdo = new PDO($dbms_data['PDO'] . ':' . $database_config['dbhost']);
			}
			else
			{
				$pdo = new PDO($dbms_data['PDO'] . ':host=' . $database_config['dbhost'] . ';dbname=' . $database_config['dbname'], $database_config['dbuser'], $database_config['dbpasswd']);
			}
		}
		else
		{
			if ($database_config['dbms'] == 'sqlite')
			{
				// delete existing database
				if (file_exists($database_config['dbhost']))
				{
					unlink($database_config['dbhost']);
				}

				$pdo = new PDO($dbms_data['PDO'] . ':' . $database_config['dbhost']);
			}
			else
			{
				$pdo = new PDO($dbms_data['PDO'] . ':host=' . $database_config['dbhost'] . ';', $database_config['dbuser'], $database_config['dbpasswd']);try
				{
					$pdo->exec('DROP DATABASE ' . $database_config['dbname']);
				}
				catch (PDOException $e){} // ignore non existent db

				$pdo->exec('CREATE DATABASE ' . $database_config['dbname']);

				$pdo = new PDO($dbms_data['PDO'] . ':host=' . $database_config['dbhost'] . ';dbname=' . $database_config['dbname'], $database_config['dbuser'], $database_config['dbpasswd']);
			}

			// good for debug
			// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			if ($database_config['dbms'] == 'mysql')
			{
				$sth = $pdo->query('SELECT VERSION() AS version');
				$row = $sth->fetch(PDO::FETCH_ASSOC);

				if (version_compare($row['version'], '4.1.3', '>='))
				{
					$dbms_data['SCHEMA'] .= '_41';
				}
				else
				{
					$dbms_data['SCHEMA'] .= '_40';
				}

				unset($row, $sth);
			}

			$sql_query = $this->split_sql_file(file_get_contents("../phpBB/install/schemas/{$dbms_data['SCHEMA']}_schema.sql"), $database_config['dbms']);

			foreach ($sql_query as $sql)
			{
				$pdo->exec($sql);
			}

			$already_connected = true;
		}

		return $this->createDefaultDBConnection($pdo, 'testdb');
	}

	public function new_dbal()
	{
		global $phpbb_root_path, $phpEx;

		$config = $this->get_database_config();

		require_once '../phpBB/includes/db/' . $config['dbms'] . '.php';
		$dbal = 'dbal_' . $config['dbms'];
		$db = new $dbal();
		$db->sql_connect($config['dbhost'], $config['dbuser'], $config['dbpasswd'], $config['dbname'], $config['dbport']);

		return $db;
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$this->get_test_case_helpers()->setExpectedTriggerError($errno, $message);
	}
}
