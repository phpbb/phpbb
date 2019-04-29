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

use phpbb\db\migration\migration;

class disable_remote_avatar extends migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\v325',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('allow_avatar_remote', '0')),
			array('config.update', array('allow_avatar_remote_upload', '0')),
		);
	}
}
