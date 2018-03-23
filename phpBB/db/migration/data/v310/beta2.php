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

class beta2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.1.0-b2', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\beta1',
			'\phpbb\db\migration\data\v310\acp_prune_users_module',
			'\phpbb\db\migration\data\v310\profilefield_location_cleanup',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-b2')),
		);
	}
}
