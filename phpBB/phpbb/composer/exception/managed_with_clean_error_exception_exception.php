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

namespace phpbb\composer\exception;

/**
 * Packaged managed with success but error occurred when cleaning the filesystem
 */
class managed_with_clean_error_exception extends managed_with_error_exception
{
	/**
	 * Constructor
	 *
	 * @param string		$prefix		The language string prefix
	 * @param string		$message	The Exception message to throw (must be a language variable).
	 * @param array			$parameters	The parameters to use with the language var.
	 * @param \Exception	$previous	The previous runtime_exception used for the runtime_exception chaining.
	 * @param integer		$code		The Exception code.
	 */
	public function __construct($prefix, $message = '', array $parameters = [], \Exception $previous = null, $code = 0)
	{
		parent::__construct($prefix . $message, $parameters, $previous, $code);
	}

}
