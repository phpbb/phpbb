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

class storage extends abstract_storage
{
	protected $driver;

	public function __construct()
	{
		$this->driver = new \phpbb\storage\driver\local();
	}

	public function set_driver($driver, $params)
	{
		$this->driver = $driver($params);
	}

	public function put_contents($path, $content)
	{
		$this->driver->put_contents($path, $contents);
	}

	public function get_contents($path)
	{
		$this->driver->put_contents($path);
	}

	public function exists($path)
	{
		$this->driver->exists($path);
	}

	public function delete($path)
	{
		$this->driver->delete($path);
	}

	public function rename($path_orig, $path_dest)
	{
		$this->driver->rename($path_orig, $path_dest);
	}

	public function copy($path_orig, $path_dest)
	{
		$this->driver->copy($path_orig, $path_dest);
	}

	public function create_dir($path)
	{
		$this->driver->create_dir($path);
	}

	public function delete_dir($path)
	{
		$this->driver->delete_dir($path);
	}
}
