<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
* Minimum Requirement: PHP 4.3.3
*/

// Remove the following line to enable this software, be sure you note what it
// says before continuing
die('This software is unsupported in any and all respects. By removing this notice (found in common.php) you are noting your acceptance of this. Do not ask support questions of any kind for this release at either area51.phpbb.com or www.phpbb.com. Support for this version will appear when the beta cycle begins');

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

error_reporting(E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
//error_reporting(E_ALL);
set_magic_quotes_runtime(0);

// Be paranoid with passed vars
if (@ini_get('register_globals'))
{
	foreach ($_REQUEST as $var_name => $void)
	{
		unset(${$var_name});
	}
}

if (defined('IN_CRON'))
{
	chdir($phpbb_root_path);
	$phpbb_root_path = getcwd() . '/';
}

require($phpbb_root_path . 'config.'.$phpEx);

if (!defined('PHPBB_INSTALLED'))
{
	header('Location: install/install.'.$phpEx);
	exit;
}

if (defined('DEBUG_EXTRA'))
{
	$base_memory_usage = 0;
	if (function_exists('memory_get_usage'))
	{
		$base_memory_usage = memory_get_usage();
	}
}

// Load Extensions
if (!empty($load_extensions))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}

define('STRIP', (get_magic_quotes_gpc()) ? true : false);

// Include files
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
require($phpbb_root_path . 'includes/acm/acm_main.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
require($phpbb_root_path . 'includes/template.' . $phpEx);
require($phpbb_root_path . 'includes/session.' . $phpEx);
require($phpbb_root_path . 'includes/auth.' . $phpEx);
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);

// Set PHP error handler to ours
set_error_handler('msg_handler');

// Instantiate some basic classes
$user		= new user();
$auth		= new auth();
$template	= new template();
$cache		= new cache();
$db			= new $sql_db();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

// We do not need this any longer, unset for safety purposes
unset($dbpasswd);

// Grab global variables, re-cache if necessary
$config = $cache->obtain_config();

// Warn about install/ directory
if (file_exists('install'))
{
//	trigger_error('REMOVE_INSTALL');
}

?>