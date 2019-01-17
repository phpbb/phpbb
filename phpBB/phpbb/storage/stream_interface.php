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

interface stream_interface
{
	/**
	 * Reads a file as a stream
	 *
	 * @param string	$path	File to read
	 *
	 * @throws \phpbb\storage\exception\exception		When unable to open file
	 *
	 * @return resource	Returns a file pointer
	 */
	public function read_stream($path);

	/**
	 * Writes a new file using a stream
	 *
	 * @param string	$path		The target file
	 * @param resource	$resource	The resource
	 *
	 * @throws \phpbb\storage\exception\exception		When target file exists
	 * 													When target file cannot be created
	 */
	public function write_stream($path, $resource);
}
