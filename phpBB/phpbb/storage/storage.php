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

use phpbb\db\driver\driver_interface;

/**
 * @internal Experimental
 */
class storage
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $storage_name;

	/**
	 * @var string
	 */
	protected $storage_table;

	/**
	 * @var \phpbb\storage\adapter_factory
	 */
	protected $factory;

	/**
	 * @var \phpbb\storage\adapter\adapter_interface
	 */
	protected $adapter;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface	$db
	 * @param \phpbb\storage\adapter_factory	$factory
	 * @param string							$storage_name
	 */
	public function __construct(driver_interface $db, adapter_factory $factory, $storage_name, $storage_table)
	{
		$this->db = $db;
		$this->factory = $factory;
		$this->storage_name = $storage_name;
		$this->storage_table = $storage_table;
	}

	/**
	 * Returns storage name
	 *
	 * @return string
	 */
	public function get_name()
	{
		return $this->storage_name;
	}

	/**
	 * Returns an adapter instance
	 *
	 * @return \phpbb\storage\adapter\adapter_interface
	 */
	protected function get_adapter()
	{
		if ($this->adapter === null)
		{
			$this->adapter = $this->factory->get($this->storage_name);
		}

		return $this->adapter;
	}

	/**
	 * Dumps content into a file.
	 *
	 * @param string	path		The file to be written to.
	 * @param string	content		The data to write into the file.
	 *
	 * @throws \phpbb\storage\exception\exception		When the file already exists
	 * 													When the file cannot be written
	 */
	public function put_contents($path, $content)
	{
		try
		{
			$this->get_adapter()->put_contents($path, $content);

			$sql_ary = array(
				'file_path'		=> $path,
				'storage'		=> $this->get_name(),
				'filesize'		=> strlen($content),
			);

			$sql = 'INSERT INTO ' . $this->storage_table . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Read the contents of a file
	 *
	 * @param string	$path	The file to read
	 *
	 * @throws \phpbb\storage\exception\exception	When the file doesn't exist
	 * 													When cannot read file contents
	 *
	 * @return string	Returns file contents
	 *
	 */
	public function get_contents($path)
	{
		return $this->get_adapter()->get_contents($path);
	}

	/**
	 * Checks the existence of files or directories.
	 *
	 * @param string	$path	file/directory to check
	 *
	 * @return bool	Returns true if the file/directory exist, false otherwise.
	 */
	public function exists($path)
	{
		return $this->get_adapter()->exists($path);
	}

	/**
	 * Removes files or directories.
	 *
	 * @param string	$path	file/directory to remove
	 *
	 * @throws \phpbb\storage\exception\exception		When removal fails.
	 */
	public function delete($path)
	{
		try
		{
			$this->get_adapter()->delete($path);

			$sql_ary = array(
				'file_path'		=> $path,
				'storage'		=> $this->get_name(),
			);

			$sql = 'DELETE FROM ' . $this->storage_table . '
				WHERE ' . $this->db->sql_build_array('DELETE', $sql_ary);
			$this->db->sql_query($sql);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Rename a file or a directory.
	 *
	 * @param string	$path_orig	The original file/direcotry
	 * @param string	$path_dest	The target file/directory
	 *
	 * @throws \phpbb\storage\exception\exception		When target exists
	 * 													When file/directory cannot be renamed
	 */
	public function rename($path_orig, $path_dest)
	{
		try
		{
			$this->get_adapter()->rename($path_orig, $path_dest);

			$sql_ary1 = array(
				'file_path'		=> $path_dest,
			);

			$sql_ary2 = array(
				'file_path'		=> $path_orig,
				'storage'		=> $this->get_name(),
			);

			$sql = 'UPDATE ' . $this->storage_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary1) . '
				WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary2);
			$this->db->sql_query($sql);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Copies a file.
	 *
	 * @param string	$path_orig	The original filename
	 * @param string	$path_dest	The target filename
	 *
	 * @throws \phpbb\storage\exception\exception		When target exists
	 * 													When the file cannot be copied
	 */
	public function copy($path_orig, $path_dest)
	{
		try
		{
			$this->get_adapter()->copy($path_orig, $path_dest);

			$sql_ary = array(
				'file_path'		=> $path_orig,
				'storage'		=> $this->get_name(),
			);

			$sql = 'SELECT filesize FROM ' . $this->storage_table . '
				WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$sql_ary = array(
				'file_path'		=> $path_dest,
				'storage'		=> $this->get_name(),
				'filesize'		=> (int) $row['filesize'],
			);

			$sql = 'INSERT INTO ' . $this->storage_table . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Reads a file as a stream.
	 *
	 * @param string	$path	File to read
	 *
	 * @throws \phpbb\storage\exception\exception		When unable to open file
	 *
	 * @return resource	Returns a file pointer
	 */
	public function read_stream($path)
	{
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
	 * Writes a new file using a stream.
	 *
	 * @param string	$path		The target file
	 * @param resource	$resource	The resource
	 *
	 * @throws \phpbb\storage\exception\exception		When target file cannot be created
	 */
	public function write_stream($path, $resource)
	{
		$adapter = $this->get_adapter();

		if ($adapter instanceof stream_interface)
		{
			$adapter->write_stream($path, $resource);

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

			if ($row)
			{
				//$sql = 'UPDATE ' . $this->storage_table . '
				//	SET filesize = filesize + ' . strlen($content) . '
				//	WHERE ' . $this->db->sql_build_array('SELECT', $sql_ary);
				//$this->db->sql_query($sql);
			}
			else
			{
				//$sql_ary['filesize'] = strlen($content);
				$sql_ary['filesize'] = 0;

				$sql = 'INSERT INTO ' . $this->storage_table . $this->db->sql_build_array('INSERT', $sql_ary);
				$this->db->sql_query($sql);
			}

		}
		else
		{
			// Simulate the stream
			$adapter->put_contents($path, stream_get_contents($resource));
		}
	}

	/**
	 * Get file info.
	 *
	 * @param string	$path	The file
	 *
	 * @throws \phpbb\storage\exception\not_implemented	When the adapter doesnt implement the method
	 *
	 * @return \phpbb\storage\file_info	Returns file_info object
	 */
	public function file_info($path)
	{
		return new file_info($this->adapter, $path);
	}

	/**
	 * Get direct link
	 *
	 * @param string	$path	The file
	 *
	 * @return string	Returns link.
	 *
	 */
	public function get_link($path)
	{
		return $this->get_adapter()->get_link($path);
	}

	/**
	 * Get total storage size.
	 *
	 * @param string	$path	The file
	 *
	 * @return int	Size in bytes
	 */
	public function get_size()
	{
		$sql = 'SELECT SUM(filesize) AS total
			FROM ' .  $this->storage_table . "
			WHERE storage = '" . $this->get_name() . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row['total'];
	}
}
