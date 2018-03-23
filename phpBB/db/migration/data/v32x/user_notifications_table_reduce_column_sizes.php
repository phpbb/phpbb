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

class user_notifications_table_reduce_column_sizes extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\user_notifications_table_index_p3',
		);
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'user_notifications'			=> array(
					'item_type'		=> array('VCHAR:165', ''),
					'method'		=> array('VCHAR:165', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'user_notifications'			=> array(
					'item_type'		=> array('VCHAR:255', ''),
					'method'		=> array('VCHAR:255', ''),
				),
			),
		);
	}
}
