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

namespace phpbb\viewtopic\exception;

use phpbb\exception\runtime_exception;

/**
 * Topic not found exception.
 */
class login_required_exception extends runtime_exception
{
	/**
	 * Constructor
	 *
	 * @param string		$message	The Exception message to throw (must be a language variable).
	 * @param array			$parameters	The parameters to use with the language var.
	 * @param \Exception	$previous	The previous runtime_exception used for the runtime_exception chaining.
	 * @param integer		$code		The Exception code.
	 */
	public function __construct(string $message = '', array $parameters = array(), \Exception $previous = null, int $code = 0)
	{
		parent::__construct($message, $parameters, $previous, $code);
	}
}
