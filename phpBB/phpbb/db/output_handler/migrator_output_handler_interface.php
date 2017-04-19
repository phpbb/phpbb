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

namespace phpbb\db\output_handler;

interface migrator_output_handler_interface
{
	const VERBOSITY_QUIET        = 16;
	const VERBOSITY_NORMAL       = 32;
	const VERBOSITY_VERBOSE      = 64;
	const VERBOSITY_VERY_VERBOSE = 128;
	const VERBOSITY_DEBUG        = 256;

	/**
	 * Write output using the configured closure.
	 *
	 * @param string|array $message The message to write or an array containing the language key and all of its parameters.
	 * @param int $verbosity The verbosity of the message.
	 */
	public function write($message, $verbosity);
}
