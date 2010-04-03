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
	define('PHPUnit_MAIN_METHOD', 'phpbb_dbal_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'dbal/dbal.php';

class phpbb_dbal_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Database Abstraction Layer');

		$suite->addTestSuite('phpbb_dbal_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_dbal_all_tests::main')
{
	phpbb_dbal_all_tests::main();
}
