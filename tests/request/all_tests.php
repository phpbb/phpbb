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

if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'phpbb_request_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'request/request_var.php';
require_once 'request/request_class.php';

class phpbb_request_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Request Parameter Handling');

		$suite->addTestSuite('phpbb_request_request_class_test');
		$suite->addTestSuite('phpbb_request_request_var_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_request_all_tests::main')
{
	phpbb_request_all_tests::main();
}
