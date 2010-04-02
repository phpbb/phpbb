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

	public function init_test_case_helpers()
	{
		if (!$this->test_case_helpers)
		{
			$this->test_case_helpers = new phpbb_test_case_helpers($this);
		}
	}

	function get_dbms_data($dbms)
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
				'PDO'			=> 'sqlite',
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

	function split_sql_file($sql, $delimiter)
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

	public function getConnection()
	{
		static $already_connected;

		$this->init_test_case_helpers();
		$database_config = $this->test_case_helpers->get_database_config();

		$dbms_data = $this->get_dbms_data($database_config['dbms']);

		if ($already_connected)
		{
			$pdo = new PDO($dbms_data['PDO'] . ':host=' . $database_config['dbhost'] . ';dbname=' . $database_config['dbname'], $database_config['dbuser'], $database_config['dbpasswd']);
		}
		else
		{
			$pdo = new PDO($dbms_data['PDO'] . ':host=' . $database_config['dbhost'] . ';', $database_config['dbuser'], $database_config['dbpasswd']);

			try
			{
				$pdo->exec('DROP DATABASE ' . $database_config['dbname']);
			}
			catch (PDOException $e){} // ignore non existent db

			$pdo->exec('CREATE DATABASE ' . $database_config['dbname']);

			$pdo = new PDO($dbms_data['PDO'] . ':host=' . $database_config['dbhost'] . ';dbname=' . $database_config['dbname'], $database_config['dbuser'], $database_config['dbpasswd']);

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

			$sql_query = $this->split_sql_file(file_get_contents("../phpBB/install/schemas/{$dbms_data['SCHEMA']}_schema.sql"), $dbms_data['DELIM']);

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
		$this->init_test_case_helpers();
		return $this->test_case_helpers->new_dbal();
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$this->init_test_case_helpers();
		$this->test_case_helpers->setExpectedTriggerError($errno, $message);
	}
}
