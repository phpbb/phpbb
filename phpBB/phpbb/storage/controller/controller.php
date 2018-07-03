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

class controller
{

	/** @var service */
	protected $cache;

	/** @var driver_interface */
	protected $db;

	/** @var storage */
	protected $storage;

	public function __construct(service $cache, driver_interface $db, storage $storage)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->storage = $storage;
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
		if (!headers_sent())
		{
			header('Cache-Control: public');

			$file_info = $this->storage->file_info($file);

			try
			{
				header('Content-Type: ' . $file_info->mimetype);
			}
			catch (\phpbb\storage\exception\exception $e)
			{
				// Just don't send this header
			}

			try
			{
				header('Content-Length: ' . $file_info->size);
			}
			catch (\phpbb\storage\exception\exception $e)
			{
				// Just don't send this header
			}

			$fp = $this->storage->read_stream($file);

			// Close db connection
			$this->file_gc();

			$output = fopen('php://output', 'w+b');

			stream_copy_to_stream($fp, $output);

			fclose($fp);
			fclose($output);

			flush();
		}
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
