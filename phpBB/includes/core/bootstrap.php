<?php
/**
*
* @package core
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* Within this file only the framework with all components but no phpBB-specific things will be loaded
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

// Report all errors, except notices
error_reporting(E_ALL | E_STRICT); // ^ E_NOTICE
date_default_timezone_set('UTC');

// Initialize some standard variables, constants and classes we need
require_once PHPBB_ROOT_PATH . 'includes/core/core.' . PHP_EXT;
require_once PHPBB_ROOT_PATH . 'plugins/bootstrap.' . PHP_EXT;

// Define STRIP if it is not already defined
if (!defined('STRIP'))
{
	// If we are on PHP >= 6.0.0 we do not need some code
	if (version_compare(PHP_VERSION, '6.0.0-dev', '>='))
	{
		/**
		* @ignore
		*/
		define('STRIP', false);
	}
	else
	{
		@set_magic_quotes_runtime(0);

		// We do not allow register globals set
		if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on' || !function_exists('ini_get'))
		{
			die('phpBB will not work with register globals turned on. Please turn register globals off.');
		}

		define('STRIP', (@get_magic_quotes_gpc()) ? true : false);
	}
}

// we check for the cron script and change the root path
if (defined('IN_CRON'))
{
	@define('PHPBB_ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

// Set some default configuration parameter if the config file does not exist
if (!file_exists(PHPBB_ROOT_PATH . 'config.' . PHP_EXT))
{
	// phpbb::$base_config['config_set'] = false
	// This allows common.php or an installation script to do specific actions if the configuration is missing
}
else
{
	require PHPBB_ROOT_PATH . 'config.' . PHP_EXT;
}

// Register autoload function
spl_autoload_register('__phpbb_autoload');

// Set error handler before a real one is there
set_error_handler(array('phpbb', 'error_handler'));

// Add constants
include_once PHPBB_ROOT_PATH . 'includes/constants.' . PHP_EXT;

// Add global functions
// @todo remove functions_content, trim down functions.php
require_once PHPBB_ROOT_PATH . 'includes/functions.' . PHP_EXT;
require_once PHPBB_ROOT_PATH . 'includes/functions_content.' . PHP_EXT;

// Add UTF8 tools
require_once PHPBB_ROOT_PATH . 'includes/utf/utf_tools.' . PHP_EXT;

// Add pre-defined system core files
require_once PHPBB_ROOT_PATH . 'includes/core/request.' . PHP_EXT;

phpbb::register('security', false, 'core/security');
phpbb::register('url', false, 'core/url');
phpbb::register('system', false, 'core/system');
phpbb::register('server-vars', 'phpbb_system_info', 'core/system_info');

// Make plugins structure available
phpbb::register('plugins');

?>