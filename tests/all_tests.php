<?php
define('IN_PHPBB', true);
if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'phpbb_all_tests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'utf/all_tests.php';

class phpbb_all_tests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('phpBB');

		$suite->addTest(phpbb_utf_all_tests::suite());

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'phpbb_all_tests::main')
{
	phpbb_all_tests::main();
}
?>