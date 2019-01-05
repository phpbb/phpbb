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

/**
 * @ignore
 */
define('IN_PHPBB', true);
define('IN_INSTALL', true);
define('PHPBB_ENVIRONMENT', 'production');
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

if (version_compare(PHP_VERSION, '5.4.7', '<') || version_compare(PHP_VERSION, '7.3-dev', '>='))
{
	die('You are running an unsupported PHP version. Please upgrade to PHP equal to or greater than 5.4.7 but less than 7.3-dev in order to install or update to phpBB 3.2');
}

$startup_new_path = $phpbb_root_path . 'install/update/update/new/install/startup.' . $phpEx;
$startup_path = (file_exists($startup_new_path)) ? $startup_new_path : $phpbb_root_path . 'install/startup.' . $phpEx;
require($startup_path);

/** @var \phpbb\filesystem\filesystem $phpbb_filesystem */
$phpbb_filesystem = $phpbb_installer_container->get('filesystem');

/** @var \phpbb\template\template $template */
$template = $phpbb_installer_container->get('template');

// Path to templates
$paths = array($phpbb_root_path . 'install/update/new/adm/style', $phpbb_admin_path . 'style');
$paths = array_filter($paths, 'is_dir');

$template->set_custom_style(array(
	array(
		'name' 		=> 'adm',
		'ext_path' 	=> 'adm/style/',
	),
), $paths);

/** @var $phpbb_dispatcher \phpbb\event\dispatcher */
$phpbb_dispatcher = $phpbb_installer_container->get('dispatcher');

/** @var \phpbb\language\language $language */
$language = $phpbb_installer_container->get('language');
$language->add_lang(array('common', 'acp/common', 'acp/board', 'install', 'posting'));

/** @var $http_kernel \Symfony\Component\HttpKernel\HttpKernel */
$http_kernel = $phpbb_installer_container->get('http_kernel');

/** @var $symfony_request \phpbb\symfony_request */
$symfony_request = $phpbb_installer_container->get('symfony_request');
$response = $http_kernel->handle($symfony_request);
$response->send();
$http_kernel->terminate($symfony_request, $response);
