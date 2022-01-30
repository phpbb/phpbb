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

namespace phpbb\debug;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\language\language_file_loader;
use Symfony\Component\Debug\BufferingLogger;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\ExceptionHandler;

/**
 * Registers all the debug tools.

 * @see Symfony\Component\Debug\Debug
 */
class debug
{
	private static $enabled = false;
	private static $exception_handler_enabled = false;

	/** @var exception_handler */
	private static $exception_handler;

	/**
	 * Enables the debug tools.
	 *
	 * This method registers an error handler and an exception handler.
	 *
	 * If the Symfony ClassLoader component is available, a special
	 * class loader is also registered.
	 *
	 * @param int  $errorReportingLevel The level of error reporting you want
	 * @param bool $displayErrors       Whether to display errors (for development) or just log them (for production)
	 */
	public static function enable($errorReportingLevel = null, $displayErrors = true)
	{
		if (static::$enabled)
		{
			return;
		}

		static::$enabled = true;

		if ($errorReportingLevel !== null)
		{
			error_reporting($errorReportingLevel);
		}
		else
		{
			error_reporting(-1);
		}

		if ('cli' !== php_sapi_name())
		{
			ini_set('display_errors', 0);
			ExceptionHandler::register();
		}
		else if ($displayErrors && (!ini_get('log_errors') || ini_get('error_log')))
		{
			// CLI - display errors only if they're not already logged to STDERR
			ini_set('display_errors', 1);
		}

		if ($displayErrors)
		{
			error_handler::register(new error_handler(new BufferingLogger()));
		}
		else
		{
			error_handler::register()->throwAt(0, true);
		}

		DebugClassLoader::enable();
	}

	/**
	 * Enable exception handler
	 *
	 * @param string $root_path phpBB root path
	 * @param string $php_ext PHP file extension
	 *
	 * @return void
	 */
	static public function enable_exception_handler(string $root_path, string $php_ext): void
	{
		if (self::$exception_handler_enabled)
		{
			return;
		}

		self::$exception_handler_enabled = true;

		$language = new language(new language_file_loader($root_path, $php_ext));

		self::$exception_handler = exception_handler::register(PHPBB_ENVIRONMENT === 'development');
		self::$exception_handler->set_language($language)
			->set_root_path($root_path);
	}

	/**
	 * Set config instance for exception handler
	 *
	 * @param config $config
	 *
	 * @return void
	 */
	static public function set_exception_handler_config(config $config)
	{
		if (!self::$exception_handler_enabled || !self::$exception_handler)
		{
			return;
		}

		self::$exception_handler->set_config($config);
	}

	/**
	 * Enable debug in exception handler
	 *
	 * @return void
	 */
	static public function enable_exception_handler_debug(): void
	{
		self::$exception_handler->set_debug_enabled();
	}
}
