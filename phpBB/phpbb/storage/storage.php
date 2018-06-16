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
	 * @var string
	 */
	protected $storage_name;

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
	 * @param \phpbb\storage\adapter_factory	$factory
	 * @param string							$storage_name
	 */
	public function __construct(adapter_factory $factory, $storage_name)
	{
		$this->factory = $factory;
		$this->storage_name = $storage_name;
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
		$this->get_adapter()->put_contents($path, $content);
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
		$this->get_adapter()->delete($path);
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
		$this->get_adapter()->rename($path_orig, $path_dest);
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
		$this->get_adapter()->copy($path_orig, $path_dest);
	}

	/**
	 * Reads a file as a stream.
	 *
	 * @param string	$path	File to read
	 *
	 * @throws \phpbb\storage\exception\exception		When unable to open file

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
	 *										When target file cannot be created
	 */
	public function write_stream($path, $resource)
	{
		$adapter = $this->get_adapter();

		if ($adapter instanceof stream_interface)
		{
			$adapter->write_stream($path, $resource);
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
}
