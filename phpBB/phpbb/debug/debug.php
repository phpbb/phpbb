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
}
