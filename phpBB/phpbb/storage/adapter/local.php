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

use phpbb\storage\stream_interface;
use phpbb\storage\exception\storage_exception;
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem;
use phpbb\filesystem\helper as filesystem_helper;

/**
 * Experimental
 */
class local implements adapter_interface, stream_interface
{
	/**
	 * Filesystem component
	 *
	 * @var filesystem
	 */
	protected $filesystem;

	/**
	 * @var string path
	 */
	protected $phpbb_root_path;

	/**
	 * Absolute path to the storage folder
	 * Always finish with DIRECTORY_SEPARATOR
	 * Example:
	 * - /var/www/phpBB/images/avatar/upload/
	 * - C:\phpBB\images\avatars\upload\
	 *
	 * @var string path
	 */
	protected $root_path;

	/**
	 * Relative path from $phpbb_root_path to the storage folder
	 * Always finish with slash (/) character
	 * Example:
	 * - images/avatars/upload/
	 *
	 * @var string path
	 */
	protected $path;

	/**
	 * Constructor
	 *
	 * @param filesystem $filesystem
	 * @param string $phpbb_root_path
	 */
	public function __construct(filesystem $filesystem, string $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configure(array $options): void
	{
		$this->path = $options['path'];

		if (substr($this->path, -1, 1) !== '/')
		{
			$this->path = $this->path . '/';
		}

		$this->root_path = filesystem_helper::realpath($this->phpbb_root_path . $options['path']) . DIRECTORY_SEPARATOR;
	}

	/**
	 * {@inheritdoc}
	 */
	public function put_contents(string $path, string $content): void
	{
		$this->ensure_directory_exists($path);

		try
		{
			$this->filesystem->dump_file($this->root_path . $this->get_path($path) . $this->get_filename($path), $content);
		}
		catch (filesystem_exception $e)
		{
			throw new storage_exception('STORAGE_CANNOT_WRITE_FILE', $path, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_contents(string $path): string
	{
		$content = @file_get_contents($this->root_path . $this->get_path($path) . $this->get_filename($path));

		if ($content === false)
		{
			throw new storage_exception('STORAGE_CANNOT_READ_FILE', $path);
		}

		return $content;
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists(string $path): bool
	{
		return $this->filesystem->exists($this->root_path . $this->get_path($path) . $this->get_filename($path));
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $path): void
	{
		try
		{
			$this->filesystem->remove($this->root_path . $this->get_path($path) . $this->get_filename($path));
		}
		catch (filesystem_exception $e)
		{
			throw new storage_exception('STORAGE_CANNOT_DELETE', $path, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rename(string $path_orig, string $path_dest): void
	{
		$this->ensure_directory_exists($path_dest);

		try
		{
			$this->filesystem->rename($this->root_path . $this->get_path($path_orig) . $this->get_filename($path_orig), $this->root_path . $this->get_path($path_dest) . $this->get_filename($path_dest), false);
		}
		catch (filesystem_exception $e)
		{
			throw new storage_exception('STORAGE_CANNOT_RENAME', $path_orig, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function copy(string $path_orig, string $path_dest): void
	{
		$this->ensure_directory_exists($path_dest);

		try
		{
			$this->filesystem->copy($this->root_path . $this->get_path($path_orig) . $this->get_filename($path_orig), $this->root_path . $this->get_path($path_dest) . $this->get_filename($path_dest), false);
		}
		catch (filesystem_exception $e)
		{
			throw new storage_exception('STORAGE_CANNOT_COPY', $path_orig, array(), $e);
		}
	}

	/**
	 * Creates a directory recursively.
	 *
	 * @param string	$path	The directory path
	 *
	 * @throws storage_exception	On any directory creation failure
	 */
	protected function create_dir(string $path): void
	{
		try
		{
			$this->filesystem->mkdir($this->root_path . $path);
		}
		catch (filesystem_exception $e)
		{
			throw new storage_exception('STORAGE_CANNOT_CREATE_DIR', $path, array(), $e);
		}
	}

	/**
	 * Ensures that the directory of a file exists.
	 *
	 * @param string	$path	The file path
	 *
	 * @throws storage_exception	On any directory creation failure
	 */
	protected function ensure_directory_exists(string $path): void
	{
		$path = dirname($this->root_path . $this->get_path($path) . $this->get_filename($path));
		$path = filesystem_helper::make_path_relative($path, $this->root_path);

		if (!$this->exists($path))
		{
			$this->create_dir($path);
		}
	}

	/**
	 * Get the path to the file
	 *
	 * @param string $path The file path
	 * @return string
	 */
	protected function get_path(string $path): string
	{
		$dirname = dirname($path);
		$dirname = ($dirname != '.') ? $dirname . DIRECTORY_SEPARATOR : '';

		return $dirname;
	}

	/**
	 * To be used in other PR
	 *
	 * @param string $path The file path
	 * @return string
	 */
	protected function get_filename(string $path): string
	{
		return basename($path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function read_stream(string $path)
	{
		$stream = @fopen($this->root_path . $this->get_path($path) . $this->get_filename($path), 'rb');

		if (!$stream)
		{
			throw new storage_exception('STORAGE_CANNOT_OPEN_FILE', $path);
		}

		return $stream;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write_stream(string $path, $resource): void
	{
		$this->ensure_directory_exists($path);

		$stream = @fopen($this->root_path . $this->get_path($path) . $this->get_filename($path), 'w+b');

		if (!$stream)
		{
			throw new storage_exception('STORAGE_CANNOT_CREATE_FILE', $path);
		}

		if (stream_copy_to_stream($resource, $stream) === false)
		{
			fclose($stream);
			throw new storage_exception('STORAGE_CANNOT_COPY_RESOURCE');
		}

		fclose($stream);
	}

	/**
	 * {@inheritdoc}
	 */
	public function file_size(string $path): int
	{
		$size = @filesize($this->root_path . $this->get_path($path) . $this->get_filename($path));

		if ($size === null)
		{
			throw new storage_exception('STORAGE_CANNOT_GET_FILESIZE');
		}

		return $size;
	}

	/**
	 * {@inheritdoc}
	 */
	public function free_space(): float
	{
		if (function_exists('disk_free_space'))
		{
			$free_space = @disk_free_space($this->root_path);

			if ($free_space === false)
			{
				throw new storage_exception('STORAGE_CANNOT_GET_FREE_SPACE');
			}
		}
		else
		{
			throw new storage_exception('STORAGE_CANNOT_GET_FREE_SPACE');
		}

		return $free_space;
	}
}
