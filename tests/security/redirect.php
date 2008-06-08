<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/OutputTestCase.php';

define('PHPBB_ROOT_PATH', './../phpBB/');
define('PHP_EXT', 'php');

require_once '../phpBB/includes/functions.php';
require_once '../phpBB/includes/session.php';

class phpbb_security_redirect_test extends PHPUnit_Extensions_OutputTestCase
{
	protected $error_triggered = false;

	public static function provider()
	{
		// array(Input -> redirect(), expected triggered error (else false), expected returned result url (else false))
		return array(
			array('data://x', false, 'http://localhost/phpBB'),
			array('http://www.otherdomain.com/somescript.php', false, 'http://localhost/phpBB'),
			array("http://localhost/phpBB/memberlist.php\n\rConnection: close", 'Tried to redirect to potentially insecure url.', false),
			array('javascript:test', false, 'http://localhost/phpBB/../tests/javascript:test'),
			array('http://localhost/phpBB/index.php;url=', 'Tried to redirect to potentially insecure url.', false),
		);
	}

	/**
	* Own error handler to catch trigger_error() calls within phpBB
	*/
	public function own_error_handler($errno, $errstr, $errfile, $errline)
	{
		echo $errstr;
		$this->error_triggered = true;
	}

	/**
	* @dataProvider provider
	*/
	public function test_redirect($test, $expected_error, $expected_result)
	{
		global $user;

		set_error_handler(array($this, 'own_error_handler'));
		$result = redirect($test, true);

		// If we expect no error and a returned result, we set the output string to be expected and check if an error was triggered (then fail instantly)
		if ($expected_error === false)
		{
			$this->expectOutputString($expected_result);
			print $result;

			if ($this->error_triggered)
			{
				$this->fail();
			}
		}
		// If we expect an error, we set the expected output string to the error and check if there was an error triggered.
		else
		{
			$this->expectOutputString($expected_error);

			if (!$this->error_triggered)
			{
				$this->fail();
			}

			$this->error_triggered = false;
		}

		restore_error_handler();
	}
}

?>