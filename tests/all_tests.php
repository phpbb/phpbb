<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

error_reporting(E_ALL);

if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'phpbb_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'utf/all_tests.php';
require_once 'request/all_tests.php';
require_once 'security/all_tests.php';
require_once 'template/all_tests.php';
require_once 'text_processing/all_tests.php';
require_once 'dbal/all_tests.php';
require_once 'class_visibility/all_tests.php';

// exclude the test directory from code coverage reports
PHPUnit_Util_Filter::addDirectoryToFilter('./');

class phpbb_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB');

		$suite->addTest(phpbb_utf_all_tests::suite());
		$suite->addTest(phpbb_request_all_tests::suite());
		$suite->addTest(phpbb_security_all_tests::suite());
		$suite->addTest(phpbb_template_all_tests::suite());
		$suite->addTest(phpbb_text_processing_all_tests::suite());
		$suite->addTest(phpbb_dbal_all_tests::suite());
		$suite->addTest(phpbb_visibility_all_tests::suite());

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_all_tests::main')
{
	phpbb_all_tests::main();
}

