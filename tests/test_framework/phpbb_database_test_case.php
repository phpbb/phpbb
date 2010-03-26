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

	public function getConnection()
	{
		$this->init_test_case_helpers();
		$database_config = $this->test_case_helpers->get_database_config();

		$pdo = new PDO('mysql:host=' . $database_config['dbhost'] . ';dbname=' . $database_config['dbname'], $database_config['dbuser'], $database_config['dbpasswd']);
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
