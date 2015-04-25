<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Report all errors, except notices and deprecation messages
if (!defined('E_DEPRECATED'))
{
	define('E_DEPRECATED', 8192);
}
$level = E_ALL & ~E_NOTICE & ~E_DEPRECATED;
if (version_compare(PHP_VERSION, '5.4.0-dev', '>='))
{
	// PHP 5.4 adds E_STRICT to E_ALL.
	// Our utf8 normalizer triggers E_STRICT output on PHP 5.4.
	// Unfortunately it cannot be made E_STRICT-clean while
	// continuing to work on PHP 4.
	// Therefore, in phpBB 3.0.x we disable E_STRICT on PHP 5.4+,
	// while phpBB 3.1 will fix utf8 normalizer.
	// E_STRICT is defined starting with PHP 5
	if (!defined('E_STRICT'))
	{
		define('E_STRICT', 2048);
	}
	$level &= ~E_STRICT;
}
error_reporting($level);

/*
* Remove variables created by register_globals from the global scope
* Thanks to Matt Kavanagh
*/
function deregister_globals()
{
	$not_unset = array(
		'GLOBALS'	=> true,
		'_GET'		=> true,
		'_POST'		=> true,
		'_COOKIE'	=> true,
		'_REQUEST'	=> true,
		'_SERVER'	=> true,
		'_SESSION'	=> true,
		'_ENV'		=> true,
		'_FILES'	=> true,
		'phpEx'		=> true,
		'phpbb_root_path'	=> true
	);

	// Not only will array_merge and array_keys give a warning if
	// a parameter is not an array, array_merge will actually fail.
	// So we check if _SESSION has been initialised.
	if (!isset($_SESSION) || !is_array($_SESSION))
	{
		$_SESSION = array();
	}

	// Merge all into one extremely huge array; unset this later
	$input = array_merge(
		array_keys($_GET),
		array_keys($_POST),
		array_keys($_COOKIE),
		array_keys($_SERVER),
		array_keys($_SESSION),
		array_keys($_ENV),
		array_keys($_FILES)
	);

	foreach ($input as $varname)
	{
		if (isset($not_unset[$varname]))
		{
			// Hacking attempt. No point in continuing.
			if (isset($_COOKIE[$varname]))
			{
				echo "Clear your cookies. ";
			}
			echo "Malicious variable name detected. Contact the administrator and ask them to disable register_globals.";
			exit;
		}

		unset($GLOBALS[$varname]);
	}

	unset($input);
}

/**
 * Check if requested page uses a trailing path
 *
 * @param string $phpEx PHP extension
 *
 * @return bool True if trailing path is used, false if not
 */
function phpbb_has_trailing_path($phpEx)
{
	// Check if path_info is being used
	if (!empty($_SERVER['PATH_INFO']) || (!empty($_SERVER['ORIG_PATH_INFO']) && $_SERVER['SCRIPT_NAME'] != $_SERVER['ORIG_PATH_INFO']))
	{
		return true;
	}

	// Match any trailing path appended to a php script in the REQUEST_URI.
	// It is assumed that only actual PHP scripts use names like foo.php. Due
	// to this, any phpBB board inside a directory that has the php extension
	// appended to its name will stop working, i.e. if the board is at
	// example.com/phpBB/test.php/ or example.com/test.php/
	if (preg_match('#^[^?]+\.' . preg_quote($phpEx, '#') . '/#', $_SERVER['REQUEST_URI']))
	{
		return true;
	}

	return false;
}

// Check if trailing path is used
if (phpbb_has_trailing_path($phpEx))
{
	if (substr(strtolower(@php_sapi_name()), 0, 3) === 'cgi')
	{
		$prefix = 'Status:';
	}
	else if (!empty($_SERVER['SERVER_PROTOCOL']) && is_string($_SERVER['SERVER_PROTOCOL']) && preg_match('#^HTTP/[0-9]\.[0-9]$#', $_SERVER['SERVER_PROTOCOL']))
	{
		$prefix = $_SERVER['SERVER_PROTOCOL'];
	}
	else
	{
		$prefix = 'HTTP/1.0';
	}
	header("$prefix 404 Not Found", true, 404);
	echo 'Trailing paths and PATH_INFO is not supported by phpBB 3.0';
	exit;
}

// Register globals and magic quotes have been dropped in PHP 5.4
if (version_compare(PHP_VERSION, '5.4.0-dev', '>='))
{
	/**
	* @ignore
	*/
	define('STRIP', false);
}
else
{
	@set_magic_quotes_runtime(0);

	// Be paranoid with passed vars
	if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on' || !function_exists('ini_get'))
	{
		deregister_globals();
	}

	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

// Prevent date/time functions from throwing E_WARNING on PHP 5.3 by setting a default timezone
if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get'))
{
	// For PHP 5.1.0 the date/time functions have been rewritten
	// and setting a timezone is required prior to calling any date/time function.

	// Since PHP 5.2.0 calls to date/time functions without having a timezone set
	// result in E_STRICT errors being thrown.
	// Note: We already exclude E_STRICT errors
	// (to be exact: they are not included in E_ALL in PHP 5.2)

	// In PHP 5.3.0 the error level has been raised to E_WARNING which causes problems
	// because we show E_WARNING errors and do not set a default timezone.
	// This is because we have our own timezone handling and work in UTC only anyway.

	// So what we basically want to do is set our timezone to UTC,
	// but we don't know what other scripts (such as bridges) are involved,
	// so we check whether a timezone is already set by calling date_default_timezone_get().

	// Unfortunately, date_default_timezone_get() itself might throw E_WARNING
	// if no timezone has been set, so we have to keep it quiet with @.

	// date_default_timezone_get() tries to guess the correct timezone first
	// and then falls back to UTC when everything fails.
	// We just set the timezone to whatever date_default_timezone_get() returns.
	date_default_timezone_set(@date_default_timezone_get());
}

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];
