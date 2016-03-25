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

class notifications extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'notifications');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
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
				$this->table_prefix . 'user_notifications'	=> array(
					'COLUMNS'			=> array(
						'item_type'			=> array('VCHAR:255', ''),
						'item_id'			=> array('UINT', 0),
						'user_id'			=> array('UINT', 0),
						'method'			=> array('VCHAR:255', ''),
						'notify'			=> array('BOOL', 1),
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
				$this->table_prefix . 'user_notifications',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'ucp',
				'UCP_MAIN',
				array(
					'module_basename'	=> 'ucp_notifications',
					'modes'				=> array('notification_list'),
				),
			)),
			array('module.add', array(
				'ucp',
				'UCP_PREFS',
				array(
					'module_basename'	=> 'ucp_notifications',
					'modes'				=> array('notification_options'),
				),
			)),
			array('config.add', array('load_notifications', 1)),
		);
	}
}
