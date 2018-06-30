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
use phpbb\storage\storage;

class controller
{

	/** @var service */
	protected $cache;

	/** @var storage */
	protected $storage;

	public function __construct(service $cache, storage $storage)
	{
		$this->cache = $cache;
		$this->storage = $storage;
	}

	public function handle($file)
	{
		if (!$this->is_allowed($file))
		{
			send_status_line(403, 'Forbidden');
			$this->file_gc();
			exit;
		}

		if (!$this->file_exists($file))
		{
			send_status_line(404, 'Not Found');
			$this->file_gc();
			exit;
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
			$this->file_gc(false);

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
	protected function file_gc($exit = true)
	{
		if (!empty($this->cache))
		{
			$this->cache->unload();
		}

		$this->db->sql_close();

		if ($exit)
		{
			exit;
		}
	}
}
