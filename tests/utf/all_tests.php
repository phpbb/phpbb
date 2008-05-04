<?php
define('IN_PHPBB', true);
if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'phpbb_utf_all_tests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'utf/utf8_wordwrap_test.php';

class phpbb_utf_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB Unicode Transformation Format');

		$suite->addTestSuite('phpbb_utf_utf8_wordwrap_test');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_utf_all_tests::main')
{
	phpbb_utf_all_tests::main();
}
?>