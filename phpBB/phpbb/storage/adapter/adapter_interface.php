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

interface adapter_interface
{
	/**
	 * Set adapter parameters
	 *
	 * @param array $options options	Storage-specific options.
	 */
	public function configure(array $options): void;

	/**
	 * Reads a file as a stream
	 *
	 * @param string $path File to read
	 *
	 * @return resource Returns a file pointer
	 * @throws storage_exception When unable to open file
	 */
	public function read(string $path);

	/**
	 * Writes a new file using a stream
	 *
	 * @param string $path The target file
	 * @param resource $resource The resource
	 *
	 * @return int Returns the number of bytes written
	 * @throws storage_exception When target file exists
	 * When target file cannot be created
	 */
	public function write(string $path, $resource): int;

	/**
	 * Removes files or directories
	 *
	 * @param string $path file/directory to remove
	 *
	 * @throws storage_exception When removal fails.
	 */
	public function delete(string $path): void;

	/**
	 * Get space available in bytes
	 *
	 * @return float Returns available space
	 * @throws storage_exception When unable to retrieve available storage space
	 */
	public function free_space(): float;
}
