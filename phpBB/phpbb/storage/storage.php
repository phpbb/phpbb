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

use phpbb\cache\driver\driver_interface as cache;
use phpbb\db\driver\driver_interface as db;
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
	 * @var db
	 */
	protected $db;

	/**
	 * Cache driver
	 * @var cache
	 */
	protected $cache;

	/**
	 * @var adapter_factory
	 */
	protected $factory;

	/**
	 * @var string
	 */
	protected $storage_name;

	/**
	 * @var string
	 */
	protected $storage_table;

	/**
	 * Constructor
	 *
	 * @param db								$db
	 * @param cache								$cache
	 * @param adapter_factory 					$factory
	 * @param string							$storage_name
	 * @param string							$storage_table
	 */
	public function __construct(db $db, cache $cache, adapter_factory $factory, string $storage_name, string $storage_table)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->factory = $factory;
		$this->storage_name = $storage_name;
		$this->storage_table = $storage_table;
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
	 * Dumps content into a file
	 *
	 * @param string	$path		The file to be written to.
	 * @param string	$content		The data to write into the file.
	 *
	 * @throws storage_exception	When the file already exists
	 * 						When the file cannot be written
	 */
	public function put_contents(string $path, string $content): void
	{
		if ($this->exists($path))
		{
			throw new storage_exception('STORAGE_FILE_EXISTS', $path);
		}

		$this->get_adapter()->put_contents($path, $content);
		$this->track_file($path);
	}

	/**
	 * Read the contents of a file
	 *
	 * @param string	$path	The file to read
	 *
	 * @return string    Returns file contents
	 *
	 * @throws storage_exception	When the file doesn't exist
	 * 						When cannot read file contents
	 *
	 */
	public function get_contents(string $path): string
	{
		if (!$this->exists($path))
		{
			throw new storage_exception('STORAGE_FILE_NO_EXIST', $path);
		}

		return $this->get_adapter()->get_contents($path);
	}

	/**
	 * Checks the existence of files or directories
	 *
	 * @param string	$path		file/directory to check
	 * @param bool		$full_check	check in the filesystem too
	 *
	 * @return bool	Returns true if the file/directory exist, false otherwise
	 */
	public function exists(string $path, bool $full_check = false): bool
	{
		return ($this->is_tracked($path) && (!$full_check || $this->get_adapter()->exists($path)));
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
		$this->untrack_file($path);
	}

	/**
	 * Rename a file or a directory
	 *
	 * @param string $path_orig	The original file/direcotry
	 * @param string $path_dest	The target file/directory
	 *
	 * @throws storage_exception    When the file doesn't exist
	 *						When target exists
	 * 						When file/directory cannot be renamed
	 */
	public function rename(string $path_orig, string $path_dest): void
	{
		if (!$this->exists($path_orig))
		{
			throw new storage_exception('STORAGE_FILE_NO_EXIST', $path_orig);
		}

		if ($this->exists($path_dest))
		{
			throw new storage_exception('STORAGE_FILE_EXISTS', $path_dest);
		}

		$this->get_adapter()->rename($path_orig, $path_dest);
		$this->track_rename($path_orig, $path_dest);
	}

	/**
	 * Copies a file
	 *
	 * @param string $path_orig	The original filename
	 * @param string $path_dest	The target filename
	 *
	 * @throws storage_exception    When the file doesn't exist
	 *						When target exists
	 * 						When the file cannot be copied
	 */
	public function copy(string $path_orig, string $path_dest): void
	{
		if (!$this->exists($path_orig))
		{
			throw new storage_exception('STORAGE_FILE_NO_EXIST', $path_orig);
		}

		if ($this->exists($path_dest))
		{
			throw new storage_exception('STORAGE_FILE_EXISTS', $path_dest);
		}

		$this->get_adapter()->copy($path_orig, $path_dest);
		$this->track_file($path_dest);
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
	public function read_stream(string $path)
	{
		if (!$this->exists($path))
		{
			throw new storage_exception('STORAGE_FILE_NO_EXIST', $path);
		}

		$stream = null;
		$adapter = $this->get_adapter();

		if ($adapter instanceof stream_interface)
		{
			$stream = $adapter->read_stream($path);
		}
		else
		{
			// Simulate the stream
			$stream = fopen('php://temp', 'w+b');
			fwrite($stream, $adapter->get_contents($path));
			rewind($stream);
		}

		return $stream;
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
	public function write_stream(string $path, $resource): void
	{
		if ($this->exists($path))
		{
			throw new storage_exception('STORAGE_FILE_EXISTS', $path);
		}

		if (!is_resource($resource))
		{
			throw new storage_exception('STORAGE_INVALID_RESOURCE');
		}

		$adapter = $this->get_adapter();

		if ($adapter instanceof stream_interface)
		{
			$adapter->write_stream($path, $resource);
			$this->track_file($path);
		}
		else
		{
			// Simulate the stream
			$adapter->put_contents($path, stream_get_contents($resource));
		}
	}

	/**
	 * Track file in database
	 *
	 * @param string $path		The target file
	 * @param bool $update		Update file size when already tracked
	 */
	public function track_file(string $path, bool $update = false): void
	{
		if (!$this->get_adapter()->exists($path))
		{
			throw new storage_exception('STORAGE_FILE_NO_EXIST', $path);
		}

		$sql_ary = array(
			'file_path'		=> $path,
			'storage'		=> $this->get_name(),
		);

		// Get file, if exist update filesize, if not add new record
		$sql = 'SELECT * FROM ' .  $this->storage_table . '
				WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			$sql_ary['filesize'] = $this->get_adapter()->file_size($path);

			$sql = 'INSERT INTO ' . $this->storage_table . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);
		}
		else if ($update)
		{
			$sql = 'UPDATE ' . $this->storage_table . '
				SET filesize = ' . $this->get_adapter()->file_size($path) . '
				WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
			$this->db->sql_query($sql);
		}

		$this->cache->destroy('_storage_' . $this->get_name() . '_totalsize');
		$this->cache->destroy('_storage_' . $this->get_name() . '_numfiles');
	}

	/**
	 * Untrack file
	 *
	 * @param string	$path		The target file
	 */
	public function untrack_file($path)
	{
		$sql_ary = array(
			'file_path'		=> $path,
			'storage'		=> $this->get_name(),
		);

		$sql = 'DELETE FROM ' . $this->storage_table . '
			WHERE ' . $this->db->sql_build_array('DELETE', $sql_ary);
		$this->db->sql_query($sql);

		$this->cache->destroy('_storage_' . $this->get_name() . '_totalsize');
		$this->cache->destroy('_storage_' . $this->get_name() . '_numfiles');
	}

	/**
	 * Check if a file is tracked
	 *
	 * @param string $path	The file
	 *
	 * @return bool	True if file is tracked
	 */
	public function is_tracked(string $path): bool
	{
		$sql_ary = array(
			'file_path'		=> $path,
			'storage'		=> $this->get_name(),
		);

		$sql = 'SELECT file_id FROM ' .  $this->storage_table . '
				WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row !== false;
	}

	/**
	 * Rename tracked file
	 *
	 * @param string $path_orig	The original file/direcotry
	 * @param string $path_dest	The target file/directory
	 */
	protected function track_rename(string $path_orig, string $path_dest): void
	{
		$sql = 'UPDATE ' . $this->storage_table . "
			SET file_path = '" . $this->db->sql_escape($path_dest) . "'
			WHERE file_path = '" . $this->db->sql_escape($path_orig) . "'
				AND storage = '" . $this->db->sql_escape($this->get_name()) . "'";
		$this->db->sql_query($sql);
	}

	/**
	 * Get file size in bytes
	 *
	 * @param string $path The file
	 *
	 * @return int Size in bytes.
	 *
	 * @throws storage_exception When unable to retrieve file size
	 */
	public function file_size(string $path): int
	{
		$sql_ary = array(
			'file_path'		=> $path,
			'storage'		=> $this->get_name(),
		);

		$sql = 'SELECT filesize FROM ' .  $this->storage_table . '
				WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row !== false && !empty($row['filesize']) ? $row['filesize'] : $this->get_adapter()->file_size($path);
	}

	/**
	 * Get total storage size
	 *
	 * @return int	Size in bytes
	 */
	public function get_size(): int
	{
		$total_size = $this->cache->get('_storage_' . $this->get_name() . '_totalsize');

		if ($total_size === false)
		{
			$sql = 'SELECT SUM(filesize) AS totalsize
				FROM ' .  $this->storage_table . "
				WHERE storage = '" . $this->db->sql_escape($this->get_name()) . "'";
			$result = $this->db->sql_query($sql);

			$total_size = (int) $this->db->sql_fetchfield('totalsize');
			$this->cache->put('_storage_' . $this->get_name() . '_totalsize', $total_size);

			$this->db->sql_freeresult($result);
		}

		return (int) $total_size;
	}

	/**
	 * Get number of storage files
	 *
	 * @return int	Number of files
	 */
	public function get_num_files(): int
	{
		$number_files = $this->cache->get('_storage_' . $this->get_name() . '_numfiles');

		if ($number_files === false)
		{
			$sql = 'SELECT COUNT(file_id) AS numfiles
				FROM ' .  $this->storage_table . "
				WHERE storage = '" . $this->db->sql_escape($this->get_name()) . "'";
			$result = $this->db->sql_query($sql);

			$number_files = (int) $this->db->sql_fetchfield('numfiles');
			$this->cache->put('_storage_' . $this->get_name() . '_numfiles', $number_files);

			$this->db->sql_freeresult($result);
		}

		return (int) $number_files;
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
