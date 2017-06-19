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

use phpbb\storage\exception\exception;

class storage
{
	protected $adapter;

	public function __construct()
	{
		$this->adapter = new \phpbb\storage\adapter\local();
	}

	public function get_adapter()
	{
		return $this->adapter;
	}

	public function set_adapter($adapter, $params)
	{
		$this->adapter = new $adapter($params);
	}

	public function put_contents($path, $content)
	{
		$this->adapter->put_contents($path, $contents);
	}

	public function get_contents($path)
	{
		$this->adapter->put_contents($path);
	}

	public function exists($path)
	{
		$this->adapter->exists($path);
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

	public function read_stream($path)
	{
		$this->adapter->read_stream($path);
	}

	public function write_stream($path, $resource)
	{
		if (!is_resource($resource))
		{
			throw new exception('INVALID_RESOURCE');
		}

		$this->adapter->write_stream($path, $resource);
	}
}
