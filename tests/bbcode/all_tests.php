<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);

if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'phpbb_bbcode_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'bbcode/parser_test.php';

class phpbb_bbcode_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Formatted Text / BBCode');

		$suite->addTestSuite('phpbb_bbcode_parser_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_bbcode_all_tests::main')
{
	phpbb_bbcode_all_tests::main();
}
