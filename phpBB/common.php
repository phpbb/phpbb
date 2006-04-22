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

// If we are on PHP >= 6.0.0 we do not need some code
if (version_compare(phpversion(), '6.0.0', '>='))
{
	define('STRIP', false);
}
else
{
	set_magic_quotes_runtime(0);

	// Protect against GLOBALS tricks
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']))
	{
		exit;
	}

	// Protect against _SESSION tricks
	if (isset($_SESSION) && !is_array($_SESSION))
	{
		exit;
	}

	// Be paranoid with passed vars
	if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
	{
		$not_unset = array('_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_SESSION', '_ENV', '_FILES', 'phpEx', 'phpbb_root_path');

		// Not only will array_merge give a warning if a parameter
		// is not an array, it will actually fail. So we check if
		// _SESSION has been initialised.
		if (!isset($_SESSION) || !is_array($_SESSION))
		{
			$_SESSION = array();
		}

		// Merge all into one extremely huge array; unset
		// this later
		$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_SESSION, $_ENV, $_FILES);

		foreach ($input as $varname => $void)
		{
			if (!in_array($varname, $not_unset))
			{
				unset(${$varname});
			}
		}

		unset($input);
	}

	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

if (defined('IN_CRON'))
{
	chdir($phpbb_root_path);
	$phpbb_root_path = getcwd() . '/';
}

require($phpbb_root_path . 'config.'.$phpEx);

if (!defined('PHPBB_INSTALLED'))
{
	header('Location: install/index.'.$phpEx);
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
$dss_seeded = false;

// Warn about install/ directory
if (file_exists('install'))
{
//	trigger_error('REMOVE_INSTALL');
}

?>
