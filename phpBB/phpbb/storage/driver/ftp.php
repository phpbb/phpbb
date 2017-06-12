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

class aws_s3 extends driver
{
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

	public function get_name()
	{
		return 'FTP';
	}

	public function get_params()
	{
		return array();
	}
}
