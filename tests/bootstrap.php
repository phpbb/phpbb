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

$class_loader = new phpbb_class_loader($phpbb_root_path, '.php');
$class_loader->register();

require $phpbb_root_path . 'includes/class_loader.php';

$class_loader = new phpbb_class_loader($phpbb_root_path, '.php');
$class_loader->register();

require_once 'test_framework/phpbb_test_case_helpers.php';
require_once 'test_framework/phpbb_test_case.php';
require_once 'test_framework/phpbb_database_test_case.php';
