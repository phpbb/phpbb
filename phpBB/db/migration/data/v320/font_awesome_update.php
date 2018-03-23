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

namespace phpbb\db\migration\data\v320;

class font_awesome_update extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\dev',
		);
	}

	public function effectively_installed()
	{
		return isset($this->config['load_font_awesome_url']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('load_font_awesome_url', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css')),
		);
	}
}
