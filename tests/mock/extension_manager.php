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


class phpbb_mock_extension_manager extends \phpbb\extension\manager
{
	public function __construct($phpbb_root_path, $extensions = array(), $container = null)
	{
		global $phpEx;

		$lang = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->phpbb_root_path = $phpbb_root_path;
		$this->extensions = $extensions;
		$this->container = $container;
		$this->config = new \phpbb\config\config(array());
		$this->finder_factory = new \phpbb\finder\factory(null, false, $this->phpbb_root_path, $phpEx);
	}
}
