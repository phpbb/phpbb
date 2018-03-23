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

class rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.1.0-RC1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\beta4',
			'\phpbb\db\migration\data\v310\contact_admin_acp_module',
			'\phpbb\db\migration\data\v310\contact_admin_form',
			'\phpbb\db\migration\data\v310\passwords_convert_p2',
			'\phpbb\db\migration\data\v310\profilefield_facebook',
			'\phpbb\db\migration\data\v310\profilefield_googleplus',
			'\phpbb\db\migration\data\v310\profilefield_skype',
			'\phpbb\db\migration\data\v310\profilefield_twitter',
			'\phpbb\db\migration\data\v310\profilefield_youtube',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-RC1')),
		);
	}
}
