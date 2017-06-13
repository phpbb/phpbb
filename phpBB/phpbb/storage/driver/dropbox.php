<?php
/**
*
* Cloud Storage extension for the phpBB Forum Software package.
*
* @copyright (c) 2017 Rubén Calvo <rubencm@gmail.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\storage\driver;

class dropbox extends adapter_common
{
	public function __construct($params)
	{
		$client = DropboxClient($params['token']);
		$adapter = DropboxAdapter($client);
		$flysystemfs = new Filesystem($adapter);

		$this->filesystem =  new \phpbb\storage\adapter\flysystem($flysystemfs);
	}

	public static function get_name()
	{
		return 'DROPBOX';
	}

	public static function get_params()
	{
		return array();
	}
}
