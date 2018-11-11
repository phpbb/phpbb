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

namespace phpbb\db\migration\data\v32x;

class v322rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.2.2-RC1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\v321',
			'\phpbb\db\migration\data\v32x\fix_user_styles',
			'\phpbb\db\migration\data\v32x\update_prosilver_bitfield',
			'\phpbb\db\migration\data\v32x\email_force_sender',
			'\phpbb\db\migration\data\v32x\f_list_topics_permission_add',
			'\phpbb\db\migration\data\v32x\merge_duplicate_bbcodes',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.2.2-RC1')),
		);
	}
}
