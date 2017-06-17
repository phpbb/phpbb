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

namespace phpbb\storage\driver;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Ftp as Adapter;

class ftp implements adapter_interface
{
	protected $filesystem;

	public function __construct($params)
	{
		$adapter = FTP([
			'host' => $params['host'],
			'username' => $params['username'],
			'password' => $params['password'],

			'port' => $params['port'],
			'root' => $params['root'],
			'passive' => true,
			'ssl' => $params['ssl'],
			'timeout' => $params['timeout'],
		]);

		$flysystemfs = new Filesystem($adapter);

		$this->filesystem =  new \phpbb\storage\adapter\flysystem($flysystemfs);
	}

	public function put_contents($path, $content)
	{
		$this->filesystem->put_contents($path, $contents);
	}

	public function get_contents($path)
	{
		$this->filesystem->get_contents($path);
	}

	public function exists($path)
	{
		$this->filesystem->exists($path);
	}

	public function delete($path)
	{
		$this->filesystem->delete($path);
	}

	public function rename($path_orig, $path_dest)
	{
		$this->filesystem->rename($path_orig, $path_dest);
	}

	public function copy($path_orig, $path_dest)
	{
		$this->filesystem->copy($path_orig, $path_dest);
	}

	public function create_dir($path)
	{
		$this->filesystem->create_dir($path);
	}

	public function delete_dir($path)
	{
		$this->filesystem->delete_dir($path);
	}

	public function read_stream($path)
	{
		$this->filesystem->read_stream($path);
	}

	public function write_stream($path, $resource)
	{
		$this->filesystem->write_stream($path, $resource);
	}

}
