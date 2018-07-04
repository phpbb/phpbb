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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

	public function __construct(service $cache, driver_interface $db, storage $storage)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->storage = $storage;
		$this->response = new StreamedResponse();
	}

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

		$this->send($file);

		return $this->response->send();
	}

	protected function is_allowed($file)
	{
		return true;
	}

	protected function file_exists($file)
	{
		return $this->storage->exists($file);
	}

	protected function send($file)
	{
		$this->response->setPublic();

		$file_info = $this->storage->file_info($file);

		try
		{
			$this->response->headers->set('Content-Type', $file_info->mimetype);
		}
		catch (\phpbb\storage\exception\exception $e)
		{
			// Just don't send this header
		}

		try
		{
			$this->response->headers->set('Content-Length', $file_info->size);
		}
		catch (\phpbb\storage\exception\exception $e)
		{
			// Just don't send this header
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
	}

	/**
	* Garbage Collection
	*
	* @param bool $exit		Whether to die or not.
	*
	* @return null
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
