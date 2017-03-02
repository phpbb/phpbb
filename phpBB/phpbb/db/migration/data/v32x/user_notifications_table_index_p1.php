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

class user_notifications_table_index_p1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\cookie_notice_p2',
		);
	}

	public function update_schema()
	{
		return array(
			'add_index' => array(
				$this->table_prefix . 'user_notifications' => array(
					'user_id'							=> array('user_id'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_keys' => array(
				$this->table_prefix . 'user_notifications' => array(
					'user_id',
				),
			),
		);
	}
}
