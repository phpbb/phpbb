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

namespace phpbb\install\helper\file_updater;

interface file_updater_interface
{
	/**
	 * Deletes a file
	 *
	 * @param string	$path_to_file	Path to the file to delete
	 */
	public function delete_file($path_to_file);

	/**
	 * Creates a new file
	 *
	 * @param string	$path_to_file_to_create	Path to the new file's location
	 * @param string	$source					Path to file to copy or string with the new file's content
	 * @param bool		$create_from_content	Whether or not to use $source as the content, false by default
	 */
	public function create_new_file($path_to_file_to_create, $source, $create_from_content = false);

	/**
	 * Update file
	 *
	 * @param string	$path_to_file_to_update	Path to the file's location
	 * @param string	$source					Path to file to copy or string with the new file's content
	 * @param bool		$create_from_content	Whether or not to use $source as the content, false by default
	 */
	public function update_file($path_to_file_to_update, $source, $create_from_content = false);

	/**
	 * Returns the name of the file updater method
	 *
	 * @return string
	 */
	public function get_method_name();
}
