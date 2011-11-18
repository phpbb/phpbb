<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = 'phpBB/';
$phpEx = 'php';
$table_prefix = 'phpbb_';

if (!defined('E_DEPRECATED'))
{
    define('E_DEPRECATED', 8192);
}
error_reporting(E_ALL & ~E_DEPRECATED);

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
require_once $phpbb_root_path . 'includes/class_loader.' . $phpEx;

$phpbb_class_loader_ext = new phpbb_class_loader('phpbb_ext_', $phpbb_root_path . 'ext/', ".php");
$phpbb_class_loader_ext->register();
$phpbb_class_loader = new phpbb_class_loader('phpbb_', $phpbb_root_path . 'includes/', ".php");
$phpbb_class_loader->register();

require_once 'test_framework/phpbb_test_case_helpers.php';
require_once 'test_framework/phpbb_test_case.php';
require_once 'test_framework/phpbb_database_test_case.php';
require_once 'test_framework/phpbb_database_test_connection_manager.php';

if (version_compare(PHP_VERSION, '5.3.0-dev', '>='))
{
	require_once 'test_framework/phpbb_functional_test_case.php';
}
