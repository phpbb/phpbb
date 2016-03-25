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
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = 'php';
		$this->extensions = $extensions;
		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->container = $container;
	}
}
