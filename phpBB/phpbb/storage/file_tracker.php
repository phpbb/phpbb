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
use phpbb\storage\exception\storage_exception;

class file_tracker
{
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
	 * @var string
	 */
	protected $storage_table;

	/**
	 * Constructor
	 *
	 * @param db								$db
	 * @param cache								$cache
	 * @param string							$storage_table
	 */
	public function __construct(db $db, cache $cache, string $storage_table)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->storage_table = $storage_table;
	}

	/**
	 * Track file in database
	 *
	 * @param string $storage	Storage name
	 * @param string $path		The target file
	 * @param int $size			Size in bytes
	 */
	public function track_file(string $storage, string $path, int $size): void
	{
		$sql_ary = [
			'file_path'		=> $path,
			'storage'		=> $storage,
			'filesize'		=> $size,
		];

		$sql = 'INSERT INTO ' . $this->storage_table . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$this->cache->destroy('_storage_' . $storage . '_totalsize');
		$this->cache->destroy('_storage_' . $storage . '_numfiles');
	}

	/**
	 * Untrack file
	 *
	 * @param string	$storage		Storage name
	 * @param string	$path			The target file
	 */
	public function untrack_file(string $storage, $path): void
	{
		$sql_ary = [
			'file_path'		=> $path,
			'storage'		=> $storage,
		];

		$sql = 'DELETE FROM ' . $this->storage_table . '
			WHERE ' . $this->db->sql_build_array('DELETE', $sql_ary);
		$this->db->sql_query($sql);

		$this->cache->destroy('_storage_' . $storage . '_totalsize');
		$this->cache->destroy('_storage_' . $storage . '_numfiles');
	}

	/**
	 * Check if a file is tracked
	 *
	 * @param string $storage	Storage name
	 * @param string $path		The file
	 *
	 * @return bool	True if file is tracked
	 */
	public function is_tracked(string $storage, string $path): bool
	{
		$sql_ary = [
			'file_path'		=> $path,
			'storage'		=> $storage,
		];

		$sql = 'SELECT file_id
			FROM ' .  $this->storage_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row !== false;
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
	public function file_size(string $storage, string $path): int
	{
		$sql_ary = [
			'file_path'		=> $path,
			'storage'		=> $storage,
		];

		$sql = 'SELECT filesize
			FROM ' .  $this->storage_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (int) $row['filesize'];
	}

	/**
	 * Get number of tracked storage files for a storage
	 *
	 * @param string $storage Storage name
	 *
	 * @return int	Number of files
	 */
	public function total_files(string $storage): int
	{
		$number_files = $this->cache->get('_storage_' . $storage. '_numfiles');

		if ($number_files === false)
		{
			$sql = 'SELECT COUNT(file_id) AS numfiles
				FROM ' .  $this->storage_table . "
				WHERE storage = '" . $this->db->sql_escape($storage) . "'";
			$result = $this->db->sql_query($sql);

			$number_files = $this->db->sql_fetchfield('numfiles');
			$this->cache->put('_storage_' . $storage . '_numfiles', $number_files);

			$this->db->sql_freeresult($result);
		}

		return (int) $number_files;
	}

	/**
	 * Get total storage size
	 *
	 * @param string $storage Storage name
	 *
	 * @return float	Size in bytes
	 */
	public function total_size(string $storage): float
	{
		$total_size = $this->cache->get('_storage_' . $storage . '_totalsize');

		if ($total_size === false)
		{
			$sql = 'SELECT SUM(filesize) AS totalsize
				FROM ' .  $this->storage_table . "
				WHERE storage = '" . $this->db->sql_escape($storage) . "'";
			$result = $this->db->sql_query($sql);

			$total_size = $this->db->sql_fetchfield('totalsize');
			$this->cache->put('_storage_' . $storage . '_totalsize', $total_size);

			$this->db->sql_freeresult($result);
		}

		return (float) $total_size;
	}
}
