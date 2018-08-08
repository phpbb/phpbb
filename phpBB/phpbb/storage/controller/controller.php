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

namespace phpbb\storage\controller;

use phpbb\cache\service;
use phpbb\db\driver\driver_interface;
use phpbb\exception\http_exception;
use phpbb\storage\storage;
use Symfony\Component\HttpFoundation\Request as symfony_request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Generic controller for storage
 */
class controller
{
	/** @var service */
	protected $cache;

	/** @var driver_interface */
	protected $db;

	/** @var storage */
	protected $storage;

	/** @var StreamedResponse */
	protected $response;

	/** @var symfony_request */
	protected $symfony_request;

	/**
	 * Constructor
	 *
	 * @param service				$cache
	 * @param driver_interfacd		$db
	 * @param storage				$storage
	 * @param symfony_request		$symfony_request
	 */
	public function __construct(service $cache, driver_interface $db, storage $storage, symfony_request $symfony_request)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->storage = $storage;
		$this->symfony_request = $symfony_request;
		$this->response = new StreamedResponse();
	}

	/**
	 * Handler
	 *
	 * @param string		$file		File path
	 *
	 * @throws \phpbb\exception\http_exception when can't access $file
	 *
	 * @return \Symfony\Component\HttpFoundation\StreamedResponse a Symfony response object
	 */
	public function handle($file)
	{
		if (!$this->is_allowed($file))
		{
			throw new http_exception(403, 'Forbidden');
		}

		if (!$this->file_exists($file))
		{
			throw new http_exception(404, 'Not Found');
		}

		$this->prepare($file);

		if (headers_sent())
		{
			throw new http_exception(500, 'Headers already sent');
		}

		$this->response->send();

		// Terminate script to avoid the execution of terminate events
		// This avoid possible errors with db connection closed
		exit;
	}

	/**
	 * If the user is allowed to download the file
	 *
	 * @param string		$file		File path
	 *
	 * @return bool
	 */
	protected function is_allowed($file)
	{
		return true;
	}

	/**
	 * Check if file exists
	 *
	 * @param string		$file		File path
	 *
	 * @return bool
	 */
	protected function file_exists($file)
	{
		return $this->storage->exists($file);
	}

	/**
	 * Prepare response
	 *
	 * @param string		$file		File path
	 */
	protected function prepare($file)
	{
		$this->response->setPublic();

		$file_info = $this->storage->file_info($file);

		if (!$this->response->headers->has('Content-Type'))
		{
			try
			{
				$content_type = $file_info->mimetype;
			}
			catch (\phpbb\storage\exception\exception $e)
			{
				$content_type = 'application/octet-stream';
			}

			$this->response->headers->set('Content-Type', $content_type);
		}

		if (!$this->response->headers->has('Content-Length'))
		{
			try
			{
				$this->response->headers->set('Content-Length', $file_info->size);
			}
			catch (\phpbb\storage\exception\exception $e)
			{
				// Just don't send this header
			}
		}

		@set_time_limit(0);

		$fp = $this->storage->read_stream($file);

		// Close db connection
		$this->file_gc();

		$output = fopen('php://output', 'w+b');

		$this->response->setCallback(function () use ($fp, $output) {
			stream_copy_to_stream($fp, $output);
			fclose($fp);
			fclose($output);
			flush();
		});

		$this->response->isNotModified($this->symfony_request);
	}

	/**
	* Garbage Collection
	*
	* @param bool $exit		Whether to die or not
	*/
	protected function file_gc()
	{
		if (!empty($this->cache))
		{
			$this->cache->unload();
		}

		$this->db->sql_close();
	}
}
