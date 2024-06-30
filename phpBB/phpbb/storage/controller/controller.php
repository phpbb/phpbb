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
use phpbb\mimetype\extension_guesser;
use phpbb\storage\exception\storage_exception;
use phpbb\storage\storage;
use Symfony\Component\HttpFoundation\Request as symfony_request;
use Symfony\Component\HttpFoundation\Response;
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

	/** @var extension_guesser */
	protected $extension_guesser;

	/** @var storage */
	protected $storage;

	/** @var symfony_request */
	protected $symfony_request;

	/**
	 * Constructor
	 *
	 * @param service				$cache
	 * @param driver_interface		$db
	 * @param storage				$storage
	 * @param symfony_request		$symfony_request
	 */
	public function __construct(service $cache, driver_interface $db, extension_guesser $extension_guesser, storage $storage, symfony_request $symfony_request)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->extension_guesser = $extension_guesser;
		$this->storage = $storage;
		$this->symfony_request = $symfony_request;
	}

	/**
	 * Handler
	 *
	 * @param string $file		File path
	 *
	 * @return Response a Symfony response object
	 *
	 * @throws http_exception when can't access $file
	 * @throws storage_exception when there is an error reading the file
	 */
	public function handle(string $file): Response
	{
		$response = new StreamedResponse();

		if (!static::is_allowed($file))
		{
			throw new http_exception(403, 'Forbidden');
		}

		if (!static::file_exists($file))
		{
			throw new http_exception(404, 'Not Found');
		}

		static::prepare($response, $file);

		if (headers_sent())
		{
			throw new http_exception(500, 'Headers already sent');
		}

		return $response;
	}

	/**
	 * If the user is allowed to download the file
	 *
	 * @param string $file		File path
	 *
	 * @return bool
	 */
	protected function is_allowed(string $file): bool
	{
		return true;
	}

	/**
	 * Check if file exists
	 *
	 * @param string $file		File path
	 *
	 * @return bool
	 */
	protected function file_exists(string $file): bool
	{
		return $this->storage->exists($file);
	}

	/**
	 * Prepare response
	 *
	 * @param StreamedResponse $response
	 * @param string $file File path
	 *
	 * @return void
	 * @throws storage_exception when there is an error reading the file
	 */
	protected function prepare(StreamedResponse $response, string $file): void
	{
		// Add Content-Type header
		if (!$response->headers->has('Content-Type'))
		{
			try
			{
				$content_type = $this->extension_guesser->guess($file);
			}
			catch (storage_exception $e)
			{
				$content_type = 'application/octet-stream';
			}

			$response->headers->set('Content-Type', $content_type);
		}

		// Add Content-Length header if we have the file size
		if (!$response->headers->has('Content-Length'))
		{
			try
			{
				$response->headers->set('Content-Length', $this->storage->file_size($file));
			}
			catch (storage_exception $e)
			{
				// Just don't send this header
			}
		}

		@set_time_limit(0);

		$fp = $this->storage->read_stream($file);

		// Close db connection
		$this->file_gc();

		$output = fopen('php://output', 'w+b');

		$response->setCallback(function () use ($fp, $output) {
			stream_copy_to_stream($fp, $output);
			fclose($fp);
			fclose($output);
			flush();

			// Terminate script to avoid the execution of terminate events
			// This avoid possible errors with db connection closed
			exit;
		});

		$response->isNotModified($this->symfony_request);
	}

	/**
	* Garbage Collection
	*/
	protected function file_gc(): void
	{
		$this->cache->unload(); // Equivalent to $this->cache->get_driver()->unload();
		$this->db->sql_close();
	}
}
