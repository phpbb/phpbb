<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = '../phpBB/';
$phpEx = 'php';
$table_prefix = '';

// If we are on PHP >= 6.0.0 we do not need some code
if (version_compare(PHP_VERSION, '6.0.0-dev', '>='))
{
	define('STRIP', false);
}
else
{
	@set_magic_quotes_runtime(0);
	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

require_once $phpbb_root_path . 'includes/constants.php';

// require at least PHPUnit 3.3.0
require_once 'PHPUnit/Runner/Version.php';
if (version_compare(PHPUnit_Runner_Version::id(), '3.3.0', '<'))
{
	trigger_error('PHPUnit >= 3.3.0 required');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'test_framework/phpbb_test_case_helpers.php';
require_once 'test_framework/phpbb_test_case.php';
require_once 'test_framework/phpbb_database_test_case.php';
