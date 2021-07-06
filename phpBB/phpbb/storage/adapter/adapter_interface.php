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

use phpbb\storage\exception\exception;

interface adapter_interface
{
	/**
	 * Set adapter parameters
	 *
	 * @param array	options	Storage-specific options.
	 */
	public function configure(array $options): void;

	/**
	 * Dumps content into a file
	 *
	 * @param string $path
	 * @param string $content
	 * @throws exception When the file cannot be written
	 */
	public function put_contents(string $path, string $content): void;

	/**
	 * Read the contents of a file
	 *
	 * @param string $path The file to read
	 *
	 * @return string Returns file contents
	 * @throws exception When cannot read file contents
	 */
	public function get_contents(string $path): string;

	/**
	 * Checks the existence of files or directories
	 *
	 * @param string $path file/directory to check
	 *
	 * @return bool Returns true if the file/directory exist, false otherwise.
	 */
	public function exists(string $path): bool;

	/**
	 * Removes files or directories
	 *
	 * @param string $path file/directory to remove
	 *
	 * @throws exception When removal fails.
	 */
	public function delete(string $path): void;

	/**
	 * Rename a file or a directory
	 *
	 * @param string $path_orig The original file/direcotry
	 * @param string $path_dest The target file/directory
	 *
	 * @throws exception When file/directory cannot be renamed
	 */
	public function rename(string $path_orig, string $path_dest): void;

	/**
	 * Copies a file
	 *
	 * @param string $path_orig The original filename
	 * @param string $path_dest The target filename
	 *
	 * @throws exception When the file cannot be copied
	 */
	public function copy(string $path_orig, string $path_dest): void;

	/**
	 * Get direct link
	 *
	 * @param string $path The file
	 *
	 * @return string Returns link.
	 */
	public function get_link(string $path): string;

	/**
	 * Get space available in bytes
	 *
	 * @return float Returns available space
	 * @throws exception When unable to retrieve available storage space
	 */
	public function free_space(): float;
}
