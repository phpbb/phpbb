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

namespace phpbb\exception;

/**
 * Class form_invalid_exception
 */
class form_invalid_exception extends back_exception
{
	/**
	 * Constructor.
	 *
	 * @param string|array	$route			The route name or an array with the route data.
	 * @param int			$status_code	The http status code.
	 * @param string		$message		The Exception message to throw (must be a language variable).
	 * @param array			$parameters		The parameters to use with the language var.
	 * @param \Exception	$previous		The previous exception used for the exception chaining.
	 * @param array			$headers		Additional headers to set in the response.
	 * @param integer		$code			The Exception code.
	 */
	public function __construct($route = '', $status_code = 400, $message = 'FORM_INVALID', array $parameters = [], \Exception $previous = null, array $headers = [], $code = 0)
	{
		parent::__construct($status_code, $message, $route, $parameters, $previous, $headers, $code);
	}
}
