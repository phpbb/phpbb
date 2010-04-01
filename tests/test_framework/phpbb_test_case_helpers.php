<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_test_case_helpers
{
	protected $expectedTriggerError = false;

	protected $test_case;

	public function __construct($test_case)
	{
		$this->test_case = $test_case;
	}

	public function get_database_config()
	{
		static $show_error = true;

		if (!file_exists('test_config.php'))
		{
			if ($show_error)
			{
				$show_error = false;
			}
			else
			{
				$this->test_case->markTestSkipped('Missing test_config.php: See first error.');
				return;
			}

			trigger_error("You have to create a test_config.php like this:
\"<?php
\$dbms = 'mysqli';
\$dbhost = 'localhost';
\$dbport = '';
\$dbname = 'database';
\$dbuser = 'user';
\$dbpasswd = 'password';
\"

NOTE: The database is dropped and recreated with the phpbb-db-schema! Do NOT specify a database with important data.", E_USER_ERROR);
		}
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
		$exceptionName = '';
		switch ($errno)
		{
			case E_NOTICE:
			case E_STRICT:
				PHPUnit_Framework_Error_Notice::$enabled = true;
				$exceptionName = 'PHPUnit_Framework_Error_Notice';
			break;

			case E_WARNING:
				PHPUnit_Framework_Error_Warning::$enabled = true;
				$exceptionName = 'PHPUnit_Framework_Error_Warning';
			break;

			default:
				$exceptionName = 'PHPUnit_Framework_Error';
			break;
		}
		$this->expectedTriggerError = true;
		$this->test_case->setExpectedException($exceptionName, (string) $message, $errno);
	}
}
