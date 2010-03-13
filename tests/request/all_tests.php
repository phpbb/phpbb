<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'phpbb_request_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'request/type_cast_helper.php';
require_once 'request/deactivated_super_global.php';
require_once 'request/request.php';
require_once 'request/request_var.php';

class phpbb_request_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Request Parameter Handling');

		$suite->addTestSuite('phpbb_type_cast_helper_test');
		$suite->addTestSuite('phpbb_deactivated_super_global_test');
		$suite->addTestSuite('phpbb_request_test');
		$suite->addTestSuite('phpbb_request_var_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_request_all_tests::main')
{
	phpbb_request_all_tests::main();
}

