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

use Symfony\Component\ErrorHandler\BufferingLogger;
use Symfony\Component\ErrorHandler\DebugClassLoader;

/**
 * Registers all the debug tools.

 * @see \Symfony\Component\ErrorHandler\Debug
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
	 * @param int|null $errorReportingLevel The level of error reporting you want
	 * @param bool $displayErrors Whether to display errors (for development) or just log them (for production)
	 */
	public static function enable(int|null $errorReportingLevel = null, bool $displayErrors = true): void
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
		}
		else if ($displayErrors && (!ini_get('log_errors') || ini_get('error_log')))
		{
			// CLI - display errors only if they're not already logged to STDERR
			ini_set('display_errors', 1);
		}

		DebugClassLoader::enable();

		error_handler::register(new error_handler(new BufferingLogger(), $displayErrors));
	}
}
