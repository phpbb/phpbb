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
 * Class back_exception
 */
class back_exception extends http_exception
{
	/** @var string */
	protected $route_name;

	/** @var array */
	protected $route_params;

	/**
	 * Constructor.
	 *
	 * @param int			$status_code	The http status code.
	 * @param string		$message		The Exception message to throw (must be a language variable).
	 * @param string|array	$route			The route name or an array with the route data.
	 * @param array			$parameters		The parameters to use with the language var.
	 * @param \Exception	$previous		The previous exception used for the exception chaining.
	 * @param array			$headers		Additional headers to set in the response.
	 * @param integer		$code			The Exception code.
	 */
	public function __construct($status_code, $message = '', $route = '', array $parameters = [], \Exception $previous = null, array $headers = [], $code = 0)
	{
		$this->route_name	= is_array($route) ? array_shift($route) : $route;
		$this->route_params	= is_array($route) ? $route : [];

		parent::__construct($status_code, $message, $parameters, $previous, $headers, $code);
	}

	public function get_route_name()
	{
		return (string) $this->route_name;
	}

	public function get_route_params()
	{
		return (array) $this->route_params;
	}
}
