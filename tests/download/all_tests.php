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
	define('PHPUnit_MAIN_METHOD', 'phpbb_download_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'download/http_byte_range.php';

class phpbb_download_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Download Tests');

		$suite->addTestSuite('phpbb_download_http_byte_range_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_download_all_tests::main')
{
	phpbb_regex_all_tests::main();
}
