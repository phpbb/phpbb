<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
* Minimum Requirement: PHP 5.3.2
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\EventDispatcher\EventDispatcher;

require($phpbb_root_path . 'includes/startup.' . $phpEx);

if (file_exists($phpbb_root_path . 'config.' . $phpEx))
{
	require($phpbb_root_path . 'config.' . $phpEx);
}

if (!defined('PHPBB_INSTALLED'))
{
	// Redirect the user to the installer
	// We have to generate a full HTTP/1.1 header here since we can't guarantee to have any of the information
	// available as used by the redirect function
	$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
	$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
	$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;

	$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	if (!$script_name)
	{
		$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	}

	// Replace any number of consecutive backslashes and/or slashes with a single slash
	// (could happen on some proxy setups and/or Windows servers)
	$script_path = trim(dirname($script_name)) . '/install/index.' . $phpEx;
	$script_path = preg_replace('#[\\\\/]{2,}#', '/', $script_path);

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

// Load Extensions
// dl() is deprecated and disabled by default as of PHP 5.3.
if (!empty($load_extensions) && function_exists('dl'))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}

// Include files
require($phpbb_root_path . 'includes/class_loader.' . $phpEx);

require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_content.' . $phpEx);

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');

// Setup class loader first
$phpbb_class_loader_ext = new phpbb_class_loader('phpbb_ext_', $phpbb_root_path . 'ext/', ".$phpEx");
$phpbb_class_loader_ext->register();
$phpbb_class_loader = new phpbb_class_loader('phpbb_', $phpbb_root_path . 'includes/', ".$phpEx");
$phpbb_class_loader->register();

// set up caching
$cache_factory = new phpbb_cache_factory($acm_type);
$cache = $cache_factory->get_service();
$phpbb_class_loader_ext->set_cache($cache->get_driver());
$phpbb_class_loader->set_cache($cache->get_driver());

// Instantiate some basic classes
$phpbb_dispatcher = new phpbb_event_dispatcher();
$request	= new phpbb_request();
$user		= new phpbb_user();
$auth		= new phpbb_auth();
$db			= new $sql_db();

// make sure request_var uses this request instance
request_var('', 0, false, false, $request); // "dependency injection" for a function

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);

// We do not need this any longer, unset for safety purposes
unset($dbpasswd);

// Grab global variables, re-cache if necessary
$config = new phpbb_config_db($db, $cache->get_driver(), CONFIG_TABLE);
set_config(null, null, null, $config);
set_config_count(null, null, null, $config);

// load extensions
$phpbb_extension_manager = new phpbb_extension_manager($db, EXT_TABLE, $phpbb_root_path, ".$phpEx", $cache->get_driver());

// Initialize style
$phpbb_style_resource_locator = new phpbb_style_resource_locator();
$phpbb_style_path_provider = new phpbb_style_extension_path_provider($phpbb_extension_manager, new phpbb_style_path_provider());
$template = new phpbb_template($phpbb_root_path, $phpEx, $config, $user, $phpbb_style_resource_locator, $phpbb_extension_manager);
$phpbb_style = new phpbb_style($phpbb_root_path, $phpEx, $config, $user, $phpbb_style_resource_locator, $phpbb_style_path_provider, $template);

$phpbb_subscriber_loader = new phpbb_event_extension_subscriber_loader($phpbb_dispatcher, $phpbb_extension_manager);
$phpbb_subscriber_loader->load();

// Add own hook handler
require($phpbb_root_path . 'includes/hooks/index.' . $phpEx);
$phpbb_hook = new phpbb_hook(array('exit_handler', 'phpbb_user_session_handler', 'append_sid', array('template', 'display')));

foreach ($cache->obtain_hooks() as $hook)
{
	@include($phpbb_root_path . 'includes/hooks/' . $hook . '.' . $phpEx);
}

if (!$config['use_system_cron'])
{
	$cron = new phpbb_cron_manager(new phpbb_cron_task_provider($phpbb_extension_manager), $cache->get_driver());
}
