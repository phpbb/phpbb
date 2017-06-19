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

namespace phpbb\storage\adapter;

use phpbb\storage\exception\exception;

class local implements adapter_interface
{
	protected $filesystem;

	public function __construct()
	{
		$this->filesystem = new \phpbb\filesystem\filesystem();
	}

	public function put_contents($path, $content)
	{
		try
		{
			if ($this->exists($path))
			{
				throw new exception('CANNOT_OPEN_FILE', $path);
			}

			$this->filesystem->dump_file($path, $content);
		}
		catch (\phpbb\filesystem\filesystem_exception $e)
		{
			throw new exception('CANNOT_DUMP_FILE', $path, array(), $e);
		}
	}

	public function get_contents($path)
	{
		$stream = $this->read_stream($path);
		$contents = stream_get_contents($stream);
		fclose($stream);

		return $stream;
	}

	public function exists($path)
	{
		return $this->filesystem->exists($path);
	}

	public function delete($path)
	{
		try
		{
			$this->filesystem->remove($path);
		}
		catch (\phpbb\filesystem\filesystem_exception $e)
		{
			throw new exception('CANNOT_DELETE_FILES', $path, array(), $e);
		}
	}

	public function rename($path_orig, $path_dest)
	{
		try
		{
			$this->filesystem->rename($path_orig, $path_dest, false);
		}
		catch (\phpbb\filesystem\filesystem_exception $e)
		{
			throw new exception('CANNOT_RENAME_FILE', $path_orig, array(), $e);
		}
	}

	public function copy($path_orig, $path_dest)
	{
		try
		{
			$this->filesystem->copy($path_orig, $path_dest, false);
		}
		catch (\phpbb\filesystem\filesystem_exception $e)
		{
			throw new exception('CANNOT_COPY_FILES', '', array(), $e);
		}
	}

	public function create_dir($path)
	{
		try
		{
			$this->filesystem->mkdir($path, 0777);
		}
		catch (\phpbb\filesystem\filesystem_exception $e)
		{
			throw new exception('CANNOT_CREATE_DIRECTORY', $path, array(), $e);
		}
	}

	public function delete_dir($path)
	{
		$this->delete($path);
	}

	public function read_stream($path)
	{
		$stream = @fopen($path, 'rb');

		if (!$stream)
		{
			throw new exception('CANNOT_OPEN_FILE', $path);
		}

		return $stream;
	}

	public function write_stream($path, $resource)
	{
		if ($this->exists($path))
		{
			throw new exception('CANNOT_OPEN_FILE', $path);
		}

		$stream = @fopen($path, 'w+b');

		if (!$stream)
		{
			throw new exception('CANNOT_OPEN_FILE', $path);
		}

		stream_copy_to_stream($resource, $stream);
	}
}
