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

namespace phpbb\db\migration\data\v31x;

class update_hashes extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v31x\v3110',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('enable_update_hashes', '1')),
			array('config.add', array('update_hashes_lock', '')),
			array('config.add', array('update_hashes_last_cron', '0'))
		);
	}
}
