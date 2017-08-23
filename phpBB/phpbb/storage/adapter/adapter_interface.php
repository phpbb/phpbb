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

interface adapter_interface
{
	/**
	 * Set adapter parameters
	 *
	 * @param array	options		Storage-specific options.
	 */
	public function configure($options);

	/**
	 * Dumps content into a file.
	 *
	 * @param string	path		The file to be written to.
	 * @param string	content		The data to write into the file.
	 *
	 * @throws \phpbb\storage\exception\exception		When the file already exists
	 * 													When the file cannot be written
	 * @throws \phpbb\storage\exception\not_implemented	When the adapter doesnt implement the method
	 */
	public function put_contents($path, $content);

	/**
	 * Read the contents of a file
	 *
	 * @param string	$path	The file to read
	 *
	 * @throws \phpbb\storage\exception\exception		When the file dont exists
	 * 													When cannot read file contents
	 * @throws \phpbb\storage\exception\not_implemented	When the adapter doesnt implement the method
	 *
	 * @return string	Returns file contents
	 *
	 */
	public function get_contents($path);

	/**
	 * Checks the existence of files or directories.
	 *
	 * @param string	$path	file/directory to check
	 *
	 * @throws \phpbb\storage\exception\not_implemented	When the adapter doesnt implement the method
	 *
	 * @return bool	Returns true if the file/directory exist, false otherwise.
	 */
	public function exists($path);

	/**
	 * Removes files or directories.
	 *
	 * @param string	$path	file/directory to remove
	 *
	 * @throws \phpbb\storage\exception\exception		When removal fails.
	 * @throws \phpbb\storage\exception\not_implemented	When the adapter doesnt implement the method
	 */
	public function delete($path);

	/**
	 * Rename a file or a directory.
	 *
	 * @param string	$path_orig	The original file/direcotry
	 * @param string	$path_dest	The target file/directory
	 *
	 * @throws \phpbb\storage\exception\exception		When target exists
	 * 													When file/directory cannot be renamed
	 * @throws \phpbb\storage\exception\not_implemented	When the adapter doesnt implement the method
	 */
	public function rename($path_orig, $path_dest);

	/**
	 * Copies a file.
	 *
	 * @param string	$path_orig	The original filename
	 * @param string	$path_dest	The target filename
	 *
	 * @throws \phpbb\storage\exception\exception		When target exists
	 * 													When the file cannot be copied
	 * @throws \phpbb\storage\exception\not_implemented	When the adapter doesnt implement the method
	 */
	public function copy($path_orig, $path_dest);
}
