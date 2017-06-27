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

/**
 * @internal Experimental
 */
class storage
{
	protected $adapter;

	public function __construct($adapter)
	{
		$this->adapter = $adapter;
	}

	public function put_contents($path, $content)
	{
		$this->adapter->put_contents($path, $content);
	}

	public function get_contents($path)
	{
		$this->adapter->put_contents($path);
	}

	public function exists($path)
	{
		return $this->adapter->exists($path);
	}

	public function delete($path)
	{
		$this->adapter->delete($path);
	}

	public function rename($path_orig, $path_dest)
	{
		$this->adapter->rename($path_orig, $path_dest);
	}

	public function copy($path_orig, $path_dest)
	{
		$this->adapter->copy($path_orig, $path_dest);
	}

	public function create_dir($path)
	{
		$this->adapter->create_dir($path);
	}

	public function delete_dir($path)
	{
		$this->adapter->delete_dir($path);
	}
}
