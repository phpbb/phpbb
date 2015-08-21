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
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Report all errors, except notices and deprecation messages
$level = E_ALL & ~E_NOTICE & ~E_DEPRECATED;
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
	if (get_magic_quotes_runtime())
	{
		// Deactivate
		@set_magic_quotes_runtime(0);
	}

	// Be paranoid with passed vars
	if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on' || !function_exists('ini_get'))
	{
		deregister_globals();
	}

	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

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

// Autoloading of dependencies.
// Three options are supported:
// 1. If dependencies are installed with Composer, Composer will create a
//    vendor/autoload.php. If this file exists it will be
//    automatically used by phpBB. This is the default mode that phpBB
//    will use when shipped.
// 2. To disable composer autoloading, PHPBB_NO_COMPOSER_AUTOLOAD can be specified.
// 	  Additionally specify PHPBB_AUTOLOAD=/path/to/autoload.php in the
//    environment. This is useful for running CLI scripts and tests.
//    /path/to/autoload.php should define and register class loaders
//    for all of phpBB's dependencies.
// 3. You can also set PHPBB_NO_COMPOSER_AUTOLOAD without setting PHPBB_AUTOLOAD.
//    In this case autoloading needs to be defined before running any phpBB
//    script. This might be useful in cases when phpBB is integrated into a
//    larger program.
if (getenv('PHPBB_NO_COMPOSER_AUTOLOAD'))
{
	if (getenv('PHPBB_AUTOLOAD'))
	{
		require(getenv('PHPBB_AUTOLOAD'));
	}
}
else
{
	if (!file_exists($phpbb_root_path . 'vendor/autoload.php'))
	{
		trigger_error(
			'Composer dependencies have not been set up yet, run ' .
			"'php ../composer.phar install' from the phpBB directory to do so.",
			E_USER_ERROR
		);
	}
	require($phpbb_root_path . 'vendor/autoload.php');
}

$starttime = microtime(true);
