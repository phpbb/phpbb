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
	public function put_contents($path, $content);
	public function get_contents($path);
	public function exists($path);
	public function delete($path);
	public function rename($path_orig, $path_dest);
	public function copy($path_orig, $path_dest);
	public function create_dir($path);
	public function delete_dir($path);
	public function read_stream($path);
	public function write_stream($path, $resource);
}
