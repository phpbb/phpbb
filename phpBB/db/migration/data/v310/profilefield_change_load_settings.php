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

class profilefield_change_load_settings extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\profilefield_aol_cleanup',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('load_cpf_memberlist', '1')),
			array('config.update', array('load_cpf_pm', '1')),
			array('config.update', array('load_cpf_viewprofile', '1')),
			array('config.update', array('load_cpf_viewtopic', '1')),
		);
	}
}
