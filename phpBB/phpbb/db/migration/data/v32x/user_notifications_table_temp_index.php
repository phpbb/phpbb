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

class user_notifications_table_temp_index extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\user_notifications_table_reduce_column_sizes',
		);
	}

	public function update_schema()
	{
		return array(
			'add_index' => array(
				$this->table_prefix . 'user_notifications' => array(
					'itm_usr_mthd'	=> array('item_type', 'item_id', 'user_id', 'method'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_keys' => array(
				$this->table_prefix . 'user_notifications' => array(
					'itm_usr_mthd',
				),
			),
		);
	}
}
