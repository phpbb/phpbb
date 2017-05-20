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

interface storage_interface
{
	public function exists($path);
	//public function is_file, is_dir

	public function putContents($path, $content); // write
	public function getContents($path); // read

	public function rename($path_orig, $path_dest);
	public function copy($path_orig, $path_dest);
	public function delete($path);
	
	public function createDir($path);
	public function deleteDir($path, $recursive = true);
}
