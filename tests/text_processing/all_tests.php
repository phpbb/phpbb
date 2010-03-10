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
	define('PHPUnit_MAIN_METHOD', 'phpbb_text_processing_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'text_processing/make_clickable.php';

class phpbb_text_processing_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Text Processing Tools');

		$suite->addTestSuite('phpbb_text_processing_make_clickable_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_text_processing_all_tests::main')
{
	phpbb_text_processing_all_tests::main();
}

