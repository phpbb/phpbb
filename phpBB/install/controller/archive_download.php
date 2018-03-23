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

namespace phpbb\install\controller;

use phpbb\exception\http_exception;
use phpbb\install\helper\config;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class archive_download
{
	/**
	 * @var config
	 */
	protected $installer_config;

	/**
	 * Constructor
	 *
	 * @param config $config
	 */
	public function __construct(config $config)
	{
		$this->installer_config = $config;
		$this->installer_config->load_config();
	}

	/**
	 * Sends response with the merge conflict archive
	 *
	 * Merge conflicts always have to be resolved manually,
	 * so we use a different archive for that.
	 *
	 * @return BinaryFileResponse
	 */
	public function conflict_archive()
	{
		$filename = $this->installer_config->get('update_file_conflict_archive', '');

		if (empty($filename))
		{
			throw new http_exception(404, 'URL_NOT_FOUND');
		}

		return $this->send_response($filename);
	}

	/**
	 * Sends response with the updated files' archive
	 *
	 * @return BinaryFileResponse
	 */
	public function update_archive()
	{
		$filename = $this->installer_config->get('update_file_archive', '');

		if (empty($filename))
		{
			throw new http_exception(404, 'URL_NOT_FOUND');
		}

		return $this->send_response($filename);
	}

	/**
	 * Generates a download response
	 *
	 * @param string	$filename	Path to the file to download
	 *
	 * @return BinaryFileResponse	Response object
	 */
	private function send_response($filename)
	{
		$response = new BinaryFileResponse($filename);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			basename($filename)
		);

		return $response;
	}
}
