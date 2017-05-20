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

class storage implements storage_interface
{
	protected $adapter;
	public function exists($path)
	{
		try
		{
			$this->adapter->exists($path);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	//public function is_file, is_dir

	public function putContents($path, $content) // write
	{
		try
		{
			$this->adapter->putContents($path, $content);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function getContents($path) // read
	{
		try
		{
			$this->adapter->getContents($path);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function rename($path_orig, $path_dest)
	{
		try
		{
			$this->adapter->rename($path_orig, $path_dest);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function copy($path_orig, $path_dest)
	{
		try
		{
			$this->adapter->copy($path_orig, $path_dest);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function delete($path)
	{
		try
		{
			$this->adapter->delete($path);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function createDir($path)
	{
		try
		{
			$this->adapter->createDir($path);
		} catch (\Exception $e) {
			throw $e;
		}
	}

	public function deleteDir($path, $recursive = true)
	{
		try
		{
			$this->adapter->deleteDir($path, $recursive);
		} catch (\Exception $e) {
			throw $e;
		}
	}
}
