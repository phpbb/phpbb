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

use phpbb\storage\exception\storage_exception;
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem;
use phpbb\filesystem\helper as filesystem_helper;

/**
 * Experimental
 */
class local implements adapter_interface
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
	 *
	 *
	 */
	public function configure(array $options): void
	{
		$this->root_path = filesystem_helper::realpath($this->phpbb_root_path . $options['path']) . DIRECTORY_SEPARATOR;
	}

	/**
	 * {@inheritdoc}
	 */
	public function read(string $path)
	{
		$full_path = $this->resolve_path($path);
		$stream = @fopen($full_path, 'rb');

		if (!$stream)
		{
			throw new storage_exception('STORAGE_CANNOT_OPEN_FILE', $path);
		}

		return $stream;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write(string $path, $resource): int
	{
		$full_path = $this->resolve_path($path);
		$stream = @fopen($full_path, 'w+b');

		if (!$stream)
		{
			throw new storage_exception('STORAGE_CANNOT_CREATE_FILE', $path);
		}

		if (($size = stream_copy_to_stream($resource, $stream)) === false)
		{
			fclose($stream);
			throw new storage_exception('STORAGE_CANNOT_COPY_RESOURCE');
		}

		fclose($stream);

		return $size;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $path): void
	{
		$full_path = $this->resolve_path($path);

		try
		{
			$this->filesystem->remove($full_path);
		}
		catch (filesystem_exception $e)
		{
			throw new storage_exception('STORAGE_CANNOT_DELETE', $path, array(), $e);
		}
	}

	/**
	 * Resolves and validates that the file is directly within the storage root directory
	 *
	 * @param string $path Path relative to storage root
	 *
	 * @return string Full path within the storage directory
	 * @throws storage_exception When path traverses outside storage root, contains subdirectories, or is invalid
	 */
	protected function resolve_path(string $path): string
	{
		if ($path === ''
			|| $path === '.'
			|| str_contains($path, '/')
			|| str_contains($path, '\\')
			|| str_contains($path, '..')
			|| str_contains($path, "\0"))
		{
			throw new storage_exception('STORAGE_INVALID_PATH', $path);
		}

		return $this->root_path . $path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function free_space(): float
	{
		if (!function_exists('disk_free_space') || ($free_space = @disk_free_space($this->root_path)) === false)
		{
			throw new storage_exception('STORAGE_CANNOT_GET_FREE_SPACE');
		}

		return $free_space;
	}

}
