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
	define('PHPUnit_MAIN_METHOD', 'phpbb_all_tests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'bbcode/all_tests.php';
require_once 'utf/all_tests.php';
require_once 'request/all_tests.php';
require_once 'security/all_tests.php';
require_once 'template/all_tests.php';

// exclude the test directory from code coverage reports
PHPUnit_Util_Filter::addDirectoryToFilter('./');

class phpbb_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB');

		$suite->addTest(phpbb_bbcode_all_tests::suite());
		$suite->addTest(phpbb_utf_all_tests::suite());
		$suite->addTest(phpbb_request_all_tests::suite());
		$suite->addTest(phpbb_security_all_tests::suite());
		$suite->addTest(phpbb_template_all_tests::suite());

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_all_tests::main')
{
	phpbb_all_tests::main();
}

?>