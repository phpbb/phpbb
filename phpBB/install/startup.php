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

function installer_msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $phpbb_installer_container;

	if (error_reporting() == 0)
	{
		return true;
	}

	switch ($errno)
	{
		case E_NOTICE:
		case E_WARNING:
		case E_USER_WARNING:
		case E_USER_NOTICE:
			$msg = '[phpBB Debug] "' . $msg_text . '" in file ' . $errfile . ' on line ' . $errline;

			if (!empty($phpbb_installer_container))
			{
				try
				{
					/** @var \phpbb\install\helper\iohandler\iohandler_interface $iohandler */
					$iohandler = $phpbb_installer_container->get('installer.helper.iohandler');
					$iohandler->add_warning_message($msg);
				}
				catch (\phpbb\install\helper\iohandler\exception\iohandler_not_implemented_exception $e)
				{
					print($msg);
				}
			}
			else
			{
				print($msg);
			}

			return;
		break;
		case E_USER_ERROR:
			$msg = '<b>General Error:</b><br />' . $msg_text . '<br /> in file ' . $errfile . ' on line ' . $errline;

			$backtrace = get_backtrace();
			if ($backtrace)
			{
				$msg .= '<br /><br />BACKTRACE<br />' . $backtrace;
			}

			throw new \phpbb\exception\runtime_exception($msg);
		break;
		case E_DEPRECATED:
			return true;
		break;
	}

	return false;
}

phpbb_require_updated('includes/startup.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('phpbb/class_loader.' . $phpEx, $phpbb_root_path);

$phpbb_class_loader_new = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}install/update/new/phpbb/", $phpEx);
$phpbb_class_loader_new->register();
$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();
$phpbb_class_loader = new \phpbb\class_loader('phpbb\\convert\\', "{$phpbb_root_path}install/convert/", $phpEx);
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
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'installer_msg_handler');

$phpbb_installer_container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
$phpbb_installer_container_builder
	->with_environment('installer')
	->without_extensions();

$other_config_path = $phpbb_root_path . 'install/update/new/config';
$config_path = (file_exists($other_config_path . '/installer/config.yml')) ? $other_config_path : $phpbb_root_path . 'config';

$phpbb_installer_container = $phpbb_installer_container_builder
	->with_config_path($config_path)
	->with_custom_parameters(array('cache.driver.class' => 'phpbb\cache\driver\file'))
	->get_container();
