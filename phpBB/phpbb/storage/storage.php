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

/**
 * @internal Experimental
 */
class storage
{
	/**
	 * @var \phpbb\storage\adapter\adapter_interface
	 */
	protected $adapter;

	/**
	 * Constructor
	 *
	 * @param \phpbb\storage\adapter_factory	$factory
	 * @param string							$storage_name
	 */
	public function __construct(adapter_factory $factory, $storage_name)
	{
		$this->adapter = $factory->get($storage_name);
	}

	/**
	 * Dumps content into a file.
	 *
	 * @param string	path		The file to be written to.
	 * @param string	content		The data to write into the file.
	 *
	 * @throws \phpbb\storage\exception\exception	When the file already exists
	 * 												When the file cannot be written
	 */
	public function put_contents($path, $content)
	{
		$this->adapter->put_contents($path, $content);
	}

	/**
	 * Read the contents of a file
	 *
	 * @param string	$path	The file to read
	 *
	 * @throws \phpbb\storage\exception\exception	When the file dont exists
	 * 												When cannot read file contents
	 * @return string	Returns file contents
	 *
	 */
	public function get_contents($path)
	{
		return $this->adapter->get_contents($path);
	}

	/**
	 * Checks the existence of files or directories.
	 *
	 * @param string	$path	file/directory to check
	 *
	 * @return bool	Returns true if all files/directories exist, false otherwise
	 */
	public function exists($path)
	{
		return $this->adapter->exists($path);
	}

	/**
	 * Removes files or directories.
	 *
	 * @param string	$path	file/directory to remove
	 *
	 * @throws \phpbb\storage\exception\exception	When removal fails.
	 */
	public function delete($path)
	{
		$this->adapter->delete($path);
	}

	/**
	 * Rename a file or a directory.
	 *
	 * @param string	$path_orig	The original file/direcotry
	 * @param string	$path_dest	The target file/directory
	 *
	 * @throws \phpbb\storage\exception\exception	When target exists
	 * 												When file/directory cannot be renamed
	 */
	public function rename($path_orig, $path_dest)
	{
		$this->adapter->rename($path_orig, $path_dest);
	}

	/**
	 * Copies a file.
	 *
	 * @param string	$path_orig	The original filename
	 * @param string	$path_dest	The target filename
	 *
	 * @throws \phpbb\storage\exception\exception	When target exists
	 * 												When the file cannot be copied
	 */
	public function copy($path_orig, $path_dest)
	{
		$this->adapter->copy($path_orig, $path_dest);
	}

	/**
	 * Creates a directory recursively.
	 *
	 * @param string	$path	The directory path
	 *
	 * @throws \phpbb\storage\exception\exception	On any directory creation failure
	 */
	public function create_dir($path)
	{
		$this->adapter->create_dir($path);
	}
}
