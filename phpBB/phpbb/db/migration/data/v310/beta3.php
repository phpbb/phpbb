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

class beta3 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.1.0-b3', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\beta2',
			'\phpbb\db\migration\data\v310\auth_provider_oauth2',
			'\phpbb\db\migration\data\v310\board_contact_name',
			'\phpbb\db\migration\data\v310\jquery_update2',
			'\phpbb\db\migration\data\v310\live_searches_config',
			'\phpbb\db\migration\data\v310\prune_shadow_topics',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-b3')),
		);
	}
}
