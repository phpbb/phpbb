<?php
/**
*
* @package testing
* @version $Id: all_tests.php 8549 2008-05-04 22:54:16Z naderman $
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);

if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'phpbb_security_all_tests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'security/extract_current_page.php';
require_once 'security/redirect.php';

class phpbb_security_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Security Fixes');

		$suite->addTestSuite('phpbb_security_extract_current_page_test');
		$suite->addTestSuite('phpbb_security_redirect_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_security_all_tests::main')
{
	phpbb_security_all_tests::main();
}
?>