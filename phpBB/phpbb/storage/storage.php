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

namespace phpbb\storage;

use phpbb\storage\adapter\adapter_interface;
use phpbb\storage\exception\storage_exception;

/**
 * Experimental
 */
class storage
{
	/**
	 * @var adapter_interface
	 */
	protected $adapter;

	/**
	 * @var adapter_factory
	 */
	protected $factory;

	/**
	 * @var file_tracker
	 */
	protected $file_tracker;

	/**
	 * @var string
	 */
	protected $storage_name;

	/**
	 * Constructor
	 *
	 * @param adapter_factory 					$factory
	 * @param file_tracker						$file_tracker
	 * @param string							$storage_name
	 */
	public function __construct(adapter_factory $factory, file_tracker $file_tracker, string $storage_name)
	{
		$this->factory = $factory;
		$this->file_tracker = $file_tracker;
		$this->storage_name = $storage_name;
	}

	/**
	 * Returns storage name
	 *
	 * @return string
	 */
	public function get_name(): string
	{
		return $this->storage_name;
	}

	/**
	 * Returns an adapter instance
	 *
	 * @return adapter_interface
	 */
	protected function get_adapter(): mixed
	{
		if ($this->adapter === null)
		{
			$this->adapter = $this->factory->get($this->storage_name);
		}

		return $this->adapter;
	}

	/**
	 * Reads a file as a stream
	 *
	 * @param string $path	File to read
	 *
	 * @return resource    Returns a file pointer
	 * @throws storage_exception	When the file doesn't exist
	 *						When unable to open file
	 *
	 */
	public function read(string $path)
	{
		if (!$this->exists($path))
		{
			throw new storage_exception('STORAGE_FILE_NO_EXIST', $path);
		}

		return $this->get_adapter()->read($path);
	}

	/**
	 * Writes a new file using a stream
	 *
	 * @param string $path		The target file
	 * @param resource	$resource	The resource
	 *
	 * @throws storage_exception    When the file exist
	 *						When target file cannot be created
	 */
	public function write(string $path, $resource): void
	{
		if ($this->exists($path))
		{
			throw new storage_exception('STORAGE_FILE_EXISTS', $path);
		}

		if (!is_resource($resource))
		{
			throw new storage_exception('STORAGE_INVALID_RESOURCE');
		}

		$size = $this->get_adapter()->write($path, $resource);
		$this->file_tracker->track_file($this->storage_name, $path, $size);
	}

	/**
	 * Removes files or directories
	 *
	 * @param string $path	file/directory to remove
	 *
	 * @throws storage_exception    When removal fails
	 *						When the file doesn't exist
	 */
	public function delete(string $path): void
	{
		if (!$this->exists($path))
		{
			throw new storage_exception('STORAGE_FILE_NO_EXIST', $path);
		}

		$this->get_adapter()->delete($path);
		$this->file_tracker->untrack_file($this->get_name(), $path);
	}

	/**
	 * Checks the existence of files or directories
	 *
	 * @param string	$path		file/directory to check
	 *
	 * @return bool	Returns true if the file/directory exist, false otherwise
	 */
	public function exists(string $path): bool
	{
		return $this->file_tracker->is_tracked($this->get_name(), $path);
	}

	/**
	 * Get file size in bytes
	 *
	 * @param string $path The file
	 *
	 * @return int Size in bytes.
	 */
	public function file_size(string $path): int
	{
		return $this->file_tracker->file_size($this->get_name(), $path);
	}

	/**
	 * Return the number of files stored in this storage
	 *
	 * @return int Number of files.
	 */
	public function total_files(): int
	{
		return $this->file_tracker->total_files($this->get_name());
	}

	/**
	 * Get total storage size
	 *
	 * @return float	Size in bytes
	 */
	public function total_size(): float
	{
		return $this->file_tracker->total_size($this->get_name());
	}

	/**
	 * Get space available in bytes
	 *
	 * @return float    Returns available space
	 * @throws storage_exception		When unable to retrieve available storage space
	 *
	 */
	public function free_space()
	{
		return $this->get_adapter()->free_space();
	}

}
