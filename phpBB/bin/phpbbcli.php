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

if (php_sapi_name() != 'cli')
{
	echo 'This program must be run from the command line.' . PHP_EOL;
	exit(1);
}

define('IN_PHPBB', true);
$phpbb_root_path = __DIR__ . '/../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'includes/startup.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);

$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();
$phpbb_class_loader_ext = new \phpbb\class_loader('\\', "{$phpbb_root_path}ext/", $phpEx);
$phpbb_class_loader_ext->register();

$phpbb_config_php_handler = new \phpbb\config_php($phpbb_root_path, $phpEx);
extract($phpbb_config_php_handler->get_all());

$phpbb_container_factory = new \phpbb\di\container_factory($phpbb_config_php_handler, $phpbb_root_path, $phpEx);
$phpbb_container_factory->set_use_extensions(false);
$phpbb_container_factory->set_dump_container(false);

$phpbb_container = $phpbb_container_factory->get_container();
$phpbb_container->get('request')->enable_super_globals();
require($phpbb_root_path . 'includes/compatibility_globals.' . $phpEx);

$user = $phpbb_container->get('user');
$user->add_lang('acp/common');

$application = new \phpbb\console\application('phpBB Console', PHPBB_VERSION, $user);
$application->register_container_commands($phpbb_container);
$application->run();
