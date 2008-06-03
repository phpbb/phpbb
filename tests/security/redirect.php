<?php
/**
*
* @package testing
* @version $Id: request_var.php 8549 2008-05-04 22:54:16Z naderman $
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/OutputTestCase.php';

define('PHPBB_ROOT_PATH', './../phpBB/');
define('PHP_EXT', 'php');

// Functional phpBB Installation required... we are actually embedding phpBB here

require_once '../phpBB/includes/functions.php';
require_once '../phpBB/includes/session.php';

class phpbb_security_redirect_test extends PHPUnit_Extensions_OutputTestCase
{
	public static function provider()
	{
		return array(
			array('data://x', 'Tried to redirect to potentially insecure url.', 'data://x'),
			array('javascript:test', '', 'http://../tests/javascript:test'),
		);
	}

	/**
	* Own error handler to catch trigger_error() calls within phpBB
	*/
	public function own_error_handler($errno, $errstr, $errfile, $errline)
	{
		echo $errstr;
	}

	/**
	* @dataProvider provider
	*/
	public function test_redirect($test, $expected_output, $expected_result)
	{
		global $user;

		// Set no user and trick a bit to circumvent errors
		$user = new user();
		$user->lang = true;
		$user->page = session::extract_current_page(PHPBB_ROOT_PATH);

		$this->expectOutputString($expected_output . '#' . $expected_result);

		set_error_handler(array($this, 'own_error_handler'));

		$result = redirect($test, true);
		print "#" . $result;

		restore_error_handler();
	}
}

?>