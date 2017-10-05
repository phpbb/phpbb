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

class v320a1 extends \phpbb\db\migration\container_aware_migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.2.0-a1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\dev',
			'\phpbb\db\migration\data\v320\allowed_schemes_links',
			'\phpbb\db\migration\data\v320\announce_global_permission',
			'\phpbb\db\migration\data\v320\remove_profilefield_wlm',
			'\phpbb\db\migration\data\v320\font_awesome_update',
			'\phpbb\db\migration\data\v320\icons_alt',
			'\phpbb\db\migration\data\v320\log_post_id',
			'\phpbb\db\migration\data\v320\remove_outdated_media',
			'\phpbb\db\migration\data\v320\notifications_board',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.2.0-dev')),
		);
	}
}
