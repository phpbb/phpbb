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

namespace phpbb;

use phpbb\legacy\exception\exit_with_response_exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
* JSON class
*/
class json_response
{
	/**
	 * Send the data to the client and exit the script.
	 *
	 * @param array $data Any additional data to send.
	 * @param bool $exit Will exit the script if true.
	 *
	 * @throws exit_with_response_exception
	 *
	 * @deprecated 3.1 Use Symfony\Component\HttpFoundation\JsonResponse instead
	 */
	public function send($data, $exit = true)
	{
		throw new exit_with_response_exception(new JsonResponse($data));
	}
}
