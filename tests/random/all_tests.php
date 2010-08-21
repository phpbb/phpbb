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
	define('PHPUnit_MAIN_METHOD', 'phpbb_random_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'random/gen_rand_string.php';

class phpbb_random_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Random Functions');

		$suite->addTestSuite('phpbb_random_gen_rand_string_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_random_all_tests::main')
{
	phpbb_random_all_tests::main();
}
