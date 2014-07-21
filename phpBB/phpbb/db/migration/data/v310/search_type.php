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

namespace phpbb\db\migration\data\v310;

class search_type extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
		);
	}

	public function update_data()
	{
		return array(
			array('if', array(
				(is_file($this->phpbb_root_path . 'phpbb/search/' . $this->config['search_type'] . $this->php_ext)),
				array('config.update', array('search_type', '\\phpbb\\search\\' . $this->config['search_type'])),
			)),
		);
	}
}
