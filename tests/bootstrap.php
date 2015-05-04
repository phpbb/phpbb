<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

define('IN_PHPBB', true);
define('PHPBB_ENVIRONMENT', 'test');

$phpbb_root_path = 'phpBB/';
$phpEx = 'php';
require_once $phpbb_root_path . 'includes/startup.php';

$table_prefix = 'phpbb_';
require_once $phpbb_root_path . 'includes/constants.php';
require_once $phpbb_root_path . 'phpbb/class_loader.' . $phpEx;
require_once($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

$phpbb_class_loader_mock = new \phpbb\class_loader('phpbb_mock_', $phpbb_root_path . '../tests/mock/', "php");
$phpbb_class_loader_mock->register();
$phpbb_class_loader_ext = new \phpbb\class_loader('\\', $phpbb_root_path . 'ext/', "php");
$phpbb_class_loader_ext->register();
$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', $phpbb_root_path . 'phpbb/', "php");
$phpbb_class_loader->register();

require_once 'test_framework/phpbb_test_case_helpers.php';
require_once 'test_framework/phpbb_test_case.php';
require_once 'test_framework/phpbb_database_test_case.php';
require_once 'test_framework/phpbb_database_test_connection_manager.php';
require_once 'test_framework/phpbb_functional_test_case.php';
require_once 'test_framework/phpbb_ui_test_case.php';

if (version_compare(PHP_VERSION, '5.3.19', ">=") && file_exists(__DIR__ . '/vendor/autoload.php'))
{
	require_once __DIR__ . '/vendor/autoload.php';
}
