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

class add_email_notifications_table extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\v323',
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'email_notifications' => array(
					'COLUMNS'	=> array(
						'notification_type_id'	=> array('USINT', 0),
						'item_id'				=> array('UINT', 0),
						'item_parent_id'		=> array('UINT', 0),
						'user_id'				=> array('UINT', 0),
					),
					'PRIMARY_KEY' => array('notification_type_id', 'item_id', 'item_parent_id', 'user_id'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'email_notifications',
			),
		);
	}
}
