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

class notifications_schema_fix extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\notifications');
	}

	public function update_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'notification_types',
				$this->table_prefix . 'notifications',
			),
			'add_tables'		=> array(
				$this->table_prefix . 'notification_types'	=> array(
					'COLUMNS'			=> array(
						'notification_type_id'		=> array('USINT', null, 'auto_increment'),
						'notification_type_name'	=> array('VCHAR:255', ''),
						'notification_type_enabled'	=> array('BOOL', 1),
					),
					'PRIMARY_KEY'		=> array('notification_type_id'),
					'KEYS'				=> array(
						'type'			=> array('UNIQUE', array('notification_type_name')),
					),
				),
				$this->table_prefix . 'notifications'		=> array(
					'COLUMNS'			=> array(
						'notification_id'				=> array('UINT:10', null, 'auto_increment'),
						'notification_type_id'			=> array('USINT', 0),
						'item_id'						=> array('UINT', 0),
						'item_parent_id'				=> array('UINT', 0),
						'user_id'						=> array('UINT', 0),
						'notification_read'				=> array('BOOL', 0),
						'notification_time'				=> array('TIMESTAMP', 1),
						'notification_data'				=> array('TEXT_UNI', ''),
					),
					'PRIMARY_KEY'		=> 'notification_id',
					'KEYS'				=> array(
						'item_ident'		=> array('INDEX', array('notification_type_id', 'item_id')),
						'user'				=> array('INDEX', array('user_id', 'notification_read')),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'notification_types',
				$this->table_prefix . 'notifications',
			),
			'add_tables'		=> array(
				$this->table_prefix . 'notification_types'	=> array(
					'COLUMNS'			=> array(
						'notification_type'			=> array('VCHAR:255', ''),
						'notification_type_enabled'	=> array('BOOL', 1),
					),
					'PRIMARY_KEY'		=> array('notification_type', 'notification_type_enabled'),
				),
				$this->table_prefix . 'notifications'		=> array(
					'COLUMNS'			=> array(
						'notification_id'  				=> array('UINT', null, 'auto_increment'),
						'item_type'			   			=> array('VCHAR:255', ''),
						'item_id'		  				=> array('UINT', 0),
						'item_parent_id'   				=> array('UINT', 0),
						'user_id'						=> array('UINT', 0),
						'notification_read'				=> array('BOOL', 0),
						'notification_time'				=> array('TIMESTAMP', 1),
						'notification_data'			   	=> array('TEXT_UNI', ''),
					),
					'PRIMARY_KEY'		=> 'notification_id',
					'KEYS'				=> array(
						'item_ident'		=> array('INDEX', array('item_type', 'item_id')),
						'user'				=> array('INDEX', array('user_id', 'notification_read')),
					),
				),
			),
		);
	}
}
