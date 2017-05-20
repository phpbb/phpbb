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

abstract class abstract_flysystes implements storage_interface
{
	protected $filesystem;

	abstract public function __construct();

	public function exists($path)
	{
		$this->filesystem->has($path);
	}

	//public function is_file, is_dir

	public function putContents($path, $content) // todo: difference between write, update, put?
	{
		$this->filesystem->put($path, $contents);
	}

	public function getContents($path) // read
	{
		$this->filesystem->read($path);
	}

	public function rename($path_orig, $path_dest)
	{
		$this->filesystem->rename($path_orig, $path_dest);
	}

	public function copy($path_orig, $path_dest)
	{
		$this->filesystem->copy($path_orig, $path_dest);
	}

	public function delete($path)
	{
		$this->filesystem->delete($path);
	}

	public function createDir($path)
	{
		$this->filesystem->createDir($path);
	}

	public function deleteDir($path)
	{
		$this->filesystem->deleteDir($path);
	}
}
