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
	define('PHPUnit_MAIN_METHOD', 'phpbb_utf_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'utf/utf8_wordwrap_test.php';
require_once 'utf/utf8_clean_string_test.php';

class phpbb_utf_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Unicode Transformation Format');

		$suite->addTestSuite('phpbb_utf_utf8_wordwrap_test');
		$suite->addTestSuite('phpbb_utf_utf8_clean_string_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_utf_all_tests::main')
{
	phpbb_utf_all_tests::main();
}

