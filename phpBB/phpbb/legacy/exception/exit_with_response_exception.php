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

namespace phpbb\legacy\exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class exit_with_response_exception
 *
 * Special exception to return a response from anywhere
 */
class exit_with_response_exception extends \Exception
{
	/** @var Response */
	private $response;

	/**
	 * exit_with_response_exception constructor.
	 *
	 * @param Response $response
	 */
	public function __construct(Response $response)
	{
		$this->response = $response;

		parent::__construct();
	}

	/**
	 * @return Response
	 */
	public function get_response()
	{
		return $this->response;
	}
}
