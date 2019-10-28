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

use Symfony\Component\Console\Input\ArgvInput;

if (php_sapi_name() !== 'cli')
{
	echo 'This program must be run from the command line.' . PHP_EOL;
	exit(1);
}

define('IN_PHPBB', true);
define('IN_INSTALL', true);
define('PHPBB_ENVIRONMENT', 'production');
define('PHPBB_VERSION', '3.2.8');
$phpbb_root_path = __DIR__ . '/../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

//
// Let's do the common.php logic
//
$startup_new_path = $phpbb_root_path . 'install/update/update/new/install/startup.' . $phpEx;
$startup_path = (file_exists($startup_new_path)) ? $startup_new_path : $phpbb_root_path . 'install/startup.' . $phpEx;
require($startup_path);

$input = new ArgvInput();

// Enable superglobals for cli support
$phpbb_installer_container->get('request')->enable_super_globals();

/** @var \phpbb\filesystem\filesystem $phpbb_filesystem */
$phpbb_filesystem = $phpbb_installer_container->get('filesystem');

/** @var \phpbb\language\language $language */
$language = $phpbb_installer_container->get('language');
$language->add_lang(array('common', 'acp/common', 'acp/board', 'install', 'posting', 'cli'));

$application = new \phpbb\console\application('phpBB Installer', PHPBB_VERSION, $language);
$application->setDispatcher($phpbb_installer_container->get('dispatcher'));
$application->register_container_commands($phpbb_installer_container->get('console.installer.command_collection'));
$application->run($input);
