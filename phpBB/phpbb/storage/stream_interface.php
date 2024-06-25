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

use phpbb\storage\exception\storage_exception;

interface stream_interface
{
	/**
	 * Reads a file as a stream
	 *
	 * @param string $path File to read
	 *
	 * @return resource Returns a file pointer
	 * @throws storage_exception When unable to open file
	 */
	public function read_stream(string $path);

	/**
	 * Writes a new file using a stream
	 *
	 * @param string $path The target file
	 * @param resource $resource The resource
	 *
	 * @return void
	 * @throws storage_exception When target file exists
	 * When target file cannot be created
	 */
	public function write_stream(string $path, $resource): void;
}
