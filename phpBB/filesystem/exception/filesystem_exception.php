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

namespace phpbb\filesystem\exception;

class filesystem_exception extends \phpbb\exception\runtime_exception
{
	/**
	 * Constructor
	 *
	 * @param string		$message	The Exception message to throw (must be a language variable).
	 * @param string		$filename	The file that caused the error.
	 * @param array			$parameters	The parameters to use with the language var.
	 * @param \Exception	$previous	The previous runtime_exception used for the runtime_exception chaining.
	 * @param integer		$code		The Exception code.
	 */
	public function __construct($message = "", $filename = '', $parameters = array(), \Exception $previous = null, $code = 0)
	{
		parent::__construct($message, array_merge(array('filename' => $filename), $parameters), $previous, $code);
	}

	/**
	 * Returns the filename that triggered the error
	 *
	 * @return string
	 */
	public function get_filename()
	{
		$parameters = parent::get_parameters();
		return $parameters['filename'];
	}
}
