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

class live_searches_config extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['allow_live_searches']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('allow_live_searches', '1')),
		);
	}
}
