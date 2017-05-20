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

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Ftp as FtpAdapter;

class ftp extends abstract_flysystem
{
	public function __construct($params)
	{
		$this->filesystem = new Filesystem(new FtpAdapter([
			'host' => '',
			'username' => '',
			'password' => '',

			// Optional
			//'port' => 21,
			//'root' => '',
			//'passive' => true,
			//'ssl' => true,
			//'timeout' => 30,
		]));
	}
}
