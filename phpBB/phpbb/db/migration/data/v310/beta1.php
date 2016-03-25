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

class beta1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\alpha3',
			'\phpbb\db\migration\data\v310\passwords_p2',
			'\phpbb\db\migration\data\v310\postgres_fulltext_drop',
			'\phpbb\db\migration\data\v310\profilefield_change_load_settings',
			'\phpbb\db\migration\data\v310\profilefield_location',
			'\phpbb\db\migration\data\v310\soft_delete_mod_convert2',
			'\phpbb\db\migration\data\v310\ucp_popuppm_module',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-b1')),
		);
	}
}
