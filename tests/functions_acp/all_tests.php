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
	define('PHPUnit_MAIN_METHOD', 'phpbb_functions_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'functions_acp/build_select.php';
require_once 'functions_acp/h_radio.php';
require_once 'functions_acp/validate_range.php';

class phpbb_functions_acp_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Network Functions');

		$suite->addTestSuite('phpbb_functions_acp_built_select_test');
		$suite->addTestSuite('phpbb_functions_acp_h_radio_test');
		$suite->addTestSuite('phpbb_functions_acp_validate_range_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_functions_acp_all_tests::main')
{
	phpbb_functions_acp_all_tests::main();
}
