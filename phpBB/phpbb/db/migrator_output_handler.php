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

namespace phpbb\db;

class migrator_output_handler
{
	const VERBOSITY_QUIET        = 0;
	const VERBOSITY_NORMAL       = 1;
	const VERBOSITY_VERBOSE      = 2;
	const VERBOSITY_VERY_VERBOSE = 3;
	const VERBOSITY_DEBUG        = 4;

	/**
	 * A callable used to write the output.
	 *
	 * @var callable
	 */
	private $closure;

	/**
	 * Constructor
	 *
	 * @param callable $closure The closure used to write the output. (null by default)
	 */
	public function __construct(\Closure $closure = null)
	{
		if ($closure === null) {
			$closure = function($message, $verbosity) {};
		}
		$this->closure = $closure;
	}

	/**
	 * Write output using the configured closure.
	 *
	 * @param string|array $message The message to write or an array containing the language key and all of its parameters.
	 * @param int $verbosity The verbosity of the message.
	 */
	public function write($message, $verbosity)
	{
		$closure = $this->closure;
		$closure((array) $message, $verbosity);
	}
}
