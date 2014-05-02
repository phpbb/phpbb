<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// set up caching
$cache = $phpbb_container->get('cache');

// Instantiate some basic classes
$phpbb_dispatcher = $phpbb_container->get('dispatcher');
$request	= $phpbb_container->get('request');
$user		= $phpbb_container->get('user');
$auth		= $phpbb_container->get('auth');
$db			= $phpbb_container->get('dbal.conn');

// make sure request_var uses this request instance
request_var('', 0, false, false, $request); // "dependency injection" for a function

// Grab global variables, re-cache if necessary
$config = $phpbb_container->get('config');
set_config(null, null, null, $config);
set_config_count(null, null, null, $config);

$phpbb_log = $phpbb_container->get('log');
$symfony_request = $phpbb_container->get('symfony_request');
$phpbb_filesystem = $phpbb_container->get('filesystem');
$phpbb_path_helper = $phpbb_container->get('path_helper');

// load extensions
$phpbb_extension_manager = $phpbb_container->get('ext.manager');
$phpbb_subscriber_loader = $phpbb_container->get('event.subscriber_loader');

$template = $phpbb_container->get('template');
