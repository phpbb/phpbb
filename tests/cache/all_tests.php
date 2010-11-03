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
	define('PHPUnit_MAIN_METHOD', 'phpbb_request_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'cache/cache_test.php';

class phpbb_cache_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Cache System');

		$suite->addTestSuite('phpbb_cache_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_cache_all_tests::main')
{
	phpbb_cache_all_tests::main();
}
