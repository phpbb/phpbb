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
	define('PHPUnit_MAIN_METHOD', 'phpbb_regex_all_tests::main');
}

require_once 'test_framework/framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'regex/email.php';
require_once 'regex/ipv4.php';
require_once 'regex/ipv6.php';
require_once 'regex/url.php';

class phpbb_regex_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Regular Expressions');

		$suite->addTestSuite('phpbb_regex_email_test');
		$suite->addTestSuite('phpbb_regex_ipv4_test');
		$suite->addTestSuite('phpbb_regex_ipv6_test');
		$suite->addTestSuite('phpbb_regex_url_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_regex_all_tests::main')
{
	phpbb_regex_all_tests::main();
}
