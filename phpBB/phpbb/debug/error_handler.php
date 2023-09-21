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
use Symfony\Component\ErrorHandler\ErrorHandler;

/**
 * @psalm-suppress InvalidExtendClass
 */
class error_handler extends ErrorHandler
{
	/**
	 * @psalm-suppress MethodSignatureMismatch
	 */
	public function __construct(BufferingLogger $bootstrappingLogger = null, private readonly bool $debug = false) // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
	{
		parent::__construct($bootstrappingLogger, $debug);
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 */
	public function handleError(int $type, string $message, string $file, int $line): bool
	{
		if (!$this->debug || $type === E_USER_WARNING || $type === E_USER_NOTICE)
		{
			$handler = defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler';

			return $handler($type, $message, $file, $line);
		}

		return parent::handleError($type, $message, $file, $line);
	}
}
