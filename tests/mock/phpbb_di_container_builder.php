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

class phpbb_mock_phpbb_di_container_builder extends \phpbb\di\container_builder
{
	protected function get_container_filename()
	{
		return $this->phpbb_root_path . '../../tmp/container.' . $this->php_ext;
	}
}
