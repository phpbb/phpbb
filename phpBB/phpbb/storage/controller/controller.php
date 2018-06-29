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

use phpbb\storage\storage;

class controller
{
	/** @var storage */
	protected $storage;

	public function __construct(storage $storage)
	{
		$this->storage = $storage;
	}

	public function handle($file)
	{
		if (!function_exists('file_gc'))
		{
			global $phpbb_root_path, $phpEx;
			require($phpbb_root_path . 'includes/functions_download' . '.' . $phpEx);
		}

		if (!$this->is_allowed($file))
		{
			send_status_line(403, 'Forbidden');
			file_gc();
			exit;
		}

		if (!$this->file_exists($file))
		{
			send_status_line(404, 'Not Found');
			file_gc();
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
		if (!function_exists('file_gc'))
		{
			global $phpbb_root_path, $phpEx;
			require($phpbb_root_path . 'includes/functions_download' . '.' . $phpEx);
		}

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
			file_gc(false);

			$output = fopen('php://output', 'w+b');

			stream_copy_to_stream($fp, $output);

			fclose($fp);
			fclose($output);

			// ??
			flush();
		}
	}
}
