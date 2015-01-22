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

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class http_exception
 */
class http_exception extends runtime_exception implements HttpExceptionInterface
{
	/**
	 * Http status code.
	 *
	 * @var integer
	 */
	private $status_code;

	/**
	 * Additional headers to set in the response.
	 *
	 * @var array
	 */
	private $headers;

	/**
	 * Constructor
	 *
	 * @param integer		$status_code	The http status code.
	 * @param string		$message		The Exception message to throw (must be a language variable).
	 * @param array			$parameters		The parameters to use with the language var.
	 * @param \Exception	$previous		The previous exception used for the exception chaining.
	 * @param array			$headers		Additional headers to set in the response.
	 * @param integer		$code			The Exception code.
	 */
	public function __construct($status_code, $message = "", array $parameters = array(), \Exception $previous = null, array $headers = array(), $code = 0)
	{
		$this->status_code = $status_code;
		$this->headers = $headers;

		parent::__construct($message, $parameters, $previous, $code);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStatusCode()
	{
		return $this->status_code;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaders()
	{
		return $this->headers;
	}
}
