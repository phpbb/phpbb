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

		if ($already_connected)
		{
			$pdo = new PDO('mysql:host=' . $database_config['dbhost'] . ';dbname=' . $database_config['dbname'], $database_config['dbuser'], $database_config['dbpasswd']);
		}
		else
		{
			$pdo = new PDO('mysql:host=' . $database_config['dbhost'] . ';', $database_config['dbuser'], $database_config['dbpasswd']);

			try
			{
				$pdo->exec('DROP DATABASE ' . $database_config['dbname']);
			}
			catch (PDOException $e){} // ignore non existent db
			
			$pdo->exec('CREATE DATABASE ' . $database_config['dbname']);

			$pdo = new PDO('mysql:host=' . $database_config['dbhost'] . ';dbname=' . $database_config['dbname'], $database_config['dbuser'], $database_config['dbpasswd']);

			$sql_query = $this->split_sql_file(file_get_contents('../phpBB/install/schemas/mysql_41_schema.sql'), ';');

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
