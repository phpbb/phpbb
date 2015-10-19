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

/** @ignore */
if (!defined('IN_PHPBB') || !defined('IN_INSTALL'))
{
	exit;
}

function phpbb_require_updated($path, $phpbb_root_path, $optional = false)
{
	$new_path = $phpbb_root_path . 'install/update/new/' . $path;
	$old_path = $phpbb_root_path . $path;

	if (file_exists($new_path))
	{
		require($new_path);
	}
	else if (!$optional || file_exists($old_path))
	{
		require($old_path);
	}
}

function phpbb_include_updated($path, $phpbb_root_path, $optional = false)
{
	$new_path = $phpbb_root_path . 'install/update/new/' . $path;
	$old_path = $phpbb_root_path . $path;

	if (file_exists($new_path))
	{
		include($new_path);
	}
	else if (!$optional || file_exists($old_path))
	{
		include($old_path);
	}
}

phpbb_require_updated('includes/startup.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('phpbb/class_loader.' . $phpEx, $phpbb_root_path);

$phpbb_class_loader_new = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}install/update/new/phpbb/", $phpEx);
$phpbb_class_loader_new->register();
$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();
$phpbb_class_loader_ext = new \phpbb\class_loader('\\', "{$phpbb_root_path}ext/", $phpEx);
$phpbb_class_loader_ext->register();

// In case $phpbb_adm_relative_path is not set (in case of an update), use the default.
$phpbb_adm_relative_path = (isset($phpbb_adm_relative_path)) ? $phpbb_adm_relative_path : 'adm/';
$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : $phpbb_root_path . $phpbb_adm_relative_path;

// Include files
phpbb_require_updated('includes/functions.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('includes/functions_content.' . $phpEx, $phpbb_root_path);
phpbb_include_updated('includes/functions_compatibility.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('includes/functions_user.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('includes/utf/utf_tools.' . $phpEx, $phpbb_root_path);

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');

$phpbb_installer_container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
$phpbb_installer_container_builder
	->with_environment('installer')
	->without_extensions();

$other_config_path = $phpbb_root_path . 'install/update/new/config';
$config_path = (file_exists($other_config_path . '/installer/config.yml')) ? $other_config_path : $phpbb_root_path . 'config';

$phpbb_installer_container = $phpbb_installer_container_builder
	->with_config_path($config_path)
	->get_container();
