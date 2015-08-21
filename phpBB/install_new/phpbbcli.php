#!/usr/bin/env php
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

if (php_sapi_name() !== 'cli')
{
	echo 'This program must be run from the command line.' . PHP_EOL;
	exit(1);
}

define('IN_PHPBB', true);
define('IN_INSTALL', true);
define('PHPBB_ENVIRONMENT', 'production');
$phpbb_root_path = __DIR__ . '/../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

//
// Let's do the common.php logic
//
require($phpbb_root_path . 'includes/startup.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);

$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();

// In case $phpbb_adm_relative_path is not set (in case of an update), use the default.
$phpbb_adm_relative_path = (isset($phpbb_adm_relative_path)) ? $phpbb_adm_relative_path : 'adm/';
$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : $phpbb_root_path . $phpbb_adm_relative_path;

// Include files
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_content.' . $phpEx);
include($phpbb_root_path . 'includes/functions_compatibility.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');

$phpbb_installer_container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
$phpbb_installer_container = $phpbb_installer_container_builder
	->with_environment('installer')
	->without_extensions()
	->without_cache()
	->get_container();

/** @var \phpbb\filesystem\filesystem $phpbb_filesystem */
$phpbb_filesystem = $phpbb_installer_container->get('filesystem');

/** @var \phpbb\language\language $language */
$language = $phpbb_installer_container->get('language');
$language->add_lang(array('common', 'acp/common', 'acp/board', 'install_new', 'posting', 'cli'));

$application = new \phpbb\console\application('phpBB Installer', PHPBB_VERSION, $language);
$application->register_container_commands($phpbb_installer_container->get('console.installer.command_collection'));
$application->run($input);
