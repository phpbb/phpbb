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

class phpbb_mock_file_downloader extends \phpbb\file_downloader
{
	public $data;

	public function set($data)
	{
		$this->data = $data;
	}

	public function get($host, $directory, $filename, $port = 80, $timeout = 6)
	{
		return $this->data;
	}
}
