<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'phpbb_visibility_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'class_visibility_test.php';

class phpbb_visibility_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Visibility class');

		$suite->addTestSuite('phpbb_class_visibility_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_visibility_all_tests::main')
{
	phpbb_visibility_all_tests::main();
}
