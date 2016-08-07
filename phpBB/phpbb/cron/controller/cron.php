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

namespace phpbb\cron\controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for running cron jobs
 */
class cron
{
	/**
	 * Handles CRON requests
	 *
	 * @param string $cron_type
	 *
	 * @return Response
	 */
	public function handle($cron_type)
	{
		$response = new Response();
		$response->headers->set('Cache-Control', 'no-cache');
		$response->headers->set('Content-type', 'image/gif');
		$response->headers->set('Content-length', '43');
		$response->setContent(base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='));

		return $response;
	}
}
