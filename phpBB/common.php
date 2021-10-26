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
* Minimum Requirement: PHP 7.1.3
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

require($phpbb_root_path . 'includes/startup.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);

$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();

$phpbb_config_php_file = new \phpbb\config_php_file($phpbb_root_path, $phpEx);
extract($phpbb_config_php_file->get_all());

if (!defined('PHPBB_ENVIRONMENT'))
{
	@define('PHPBB_ENVIRONMENT', 'production');
}

if (!defined('PHPBB_INSTALLED'))
{
	// Redirect the user to the installer
	require($phpbb_root_path . 'includes/functions.' . $phpEx);

	// We have to generate a full HTTP/1.1 header here since we can't guarantee to have any of the information
	// available as used by the redirect function
	$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
	$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
	$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 1 : 0;

	if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
	{
		$secure = 1;
		$server_port = 443;
	}

	$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	if (!$script_name)
	{
		$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	}

	// $phpbb_root_path accounts for redirects from e.g. /adm
	$script_path = trim(dirname($script_name)) . '/' . $phpbb_root_path . 'install/app.' . $phpEx;
	// Replace any number of consecutive backslashes and/or slashes with a single slash
	// (could happen on some proxy setups and/or Windows servers)
	$script_path = preg_replace('#[\\\\/]{2,}#', '/', $script_path);

	// Eliminate . and .. from the path
	require($phpbb_root_path . 'phpbb/filesystem.' . $phpEx);
	$phpbb_filesystem = new phpbb\filesystem\filesystem();
	$script_path = $phpbb_filesystem->clean_path($script_path);

	$url = (($secure) ? 'https://' : 'http://') . $server_name;

	if ($server_port && (($secure && $server_port <> 443) || (!$secure && $server_port <> 80)))
	{
		// HTTP HOST can carry a port number...
		if (strpos($server_name, ':') === false)
		{
			$url .= ':' . $server_port;
		}
	}

	$url .= $script_path;
	header('Location: ' . $url);
	exit;
}

// In case $phpbb_adm_relative_path is not set (in case of an update), use the default.
$phpbb_adm_relative_path = (isset($phpbb_adm_relative_path)) ? $phpbb_adm_relative_path : 'adm/';
$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : $phpbb_root_path . $phpbb_adm_relative_path;

// Include files
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_content.' . $phpEx);
include($phpbb_root_path . 'includes/functions_compatibility.' . $phpEx);

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// Registered before building the container so the development environment stay capable of intercepting
// the container builder exceptions.
if (PHPBB_ENVIRONMENT === 'development')
{
	\phpbb\debug\debug::enable();
}
else
{
	set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');
}

$phpbb_class_loader_ext = new \phpbb\class_loader('\\', "{$phpbb_root_path}ext/", $phpEx);
$phpbb_class_loader_ext->register();

// Set up container
try
{
	$phpbb_container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
	$phpbb_container = $phpbb_container_builder->with_config($phpbb_config_php_file)->get_container();
}
catch (InvalidArgumentException $e)
{
	if (PHPBB_ENVIRONMENT !== 'development')
	{
		trigger_error(
			'The requested environment ' . PHPBB_ENVIRONMENT . ' is not available.',
			E_USER_ERROR
		);
	}
	else
	{
		throw $e;
	}
}

if ($phpbb_container->getParameter('debug.error_handler'))
{
	\phpbb\debug\debug::enable();
}

$phpbb_class_loader->set_cache($phpbb_container->get('cache.driver'));
$phpbb_class_loader_ext->set_cache($phpbb_container->get('cache.driver'));

$phpbb_container->get('dbal.conn')->set_debug_sql_explain($phpbb_container->getParameter('debug.sql_explain'));
$phpbb_container->get('dbal.conn')->set_debug_load_time($phpbb_container->getParameter('debug.load_time'));
require($phpbb_root_path . 'includes/compatibility_globals.' . $phpEx);

register_compatibility_globals();

// Add own hook handler
require($phpbb_root_path . 'includes/hooks/index.' . $phpEx);
$phpbb_hook = new phpbb_hook(array('exit_handler', 'phpbb_user_session_handler', 'append_sid', array('template', 'display')));

/* @var $phpbb_hook_finder \phpbb\hook\finder */
$phpbb_hook_finder = $phpbb_container->get('hook_finder');

foreach ($phpbb_hook_finder->find() as $hook)
{
	@include($phpbb_root_path . 'includes/hooks/' . $hook . '.' . $phpEx);
}

/**
* Main event which is triggered on every page
*
* You can use this event to load function files and initiate objects
*
* NOTE:	At this point the global session ($user) and permissions ($auth)
*		do NOT exist yet. If you need to use the user object
*		(f.e. to include language files) or need to check permissions,
*		please use the core.user_setup event instead!
*
* @event core.common
* @since 3.1.0-a1
*/
$phpbb_dispatcher->dispatch('core.common');
