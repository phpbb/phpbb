<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_notifications extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'notifications');
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_310_dev');
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
						'notification_id'  				=> array('UINT', NULL, 'auto_increment'),
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
			array('custom', array(array($this, 'convert_notifications'))),
		);
	}

	public function convert_notifications()
	{
		$insert_table = $this->table_prefix . 'user_notifications';

		$sql = 'SELECT user_id, user_notify_type, user_notify_pm
			FROM ' . USERS_TABLE;
		$result = $this->db->sql_query($sql);

		$sql_insert_data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$notification_methods = array();

			// In-board notification
			$notification_methods[] = '';

			if ($row['user_notify_type'] == NOTIFY_EMAIL || $row['user_notify_type'] == NOTIFY_BOTH)
			{
				$notification_methods[] = 'email';
			}

			if ($row['user_notify_type'] == NOTIFY_IM || $row['user_notify_type'] == NOTIFY_BOTH)
			{
				$notification_methods[] = 'jabber';
			}

			// Notifications for  posts
			foreach (array('post', 'topic') as $item_type)
			{
				$sql_insert_data = $this->add_method_rows(
					$sql_insert_data,
					$item_type,
					0,
					$row['user_id'],
					$notification_methods
				);
			}

			if ($row['user_notify_pm'])
			{
				// Notifications for private messages
				// User either gets all methods or no method
				$sql_insert_data = $this->add_method_rows(
					$sql_insert_data,
					'pm',
					0,
					$row['user_id'],
					$notification_methods
				);
			}

			if (sizeof($sql_insert_data) > 500)
			{
				$db->sql_multi_insert($insert_table, $sql_insert_data);
				$sql_insert_data = array();
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($sql_insert_data))
		{
			$db->sql_multi_insert($insert_table, $sql_insert_data);
		}
	}

	protected function add_method_rows(array $sql_insert_data, $item_type, $item_id, $user_id, array $methods)
	{
		$row_base = array(
			'item_type'		=> $item_type,
			'item_id'		=> (int) $item_id,
			'user_id'		=> (int) $user_id,
		);

		foreach ($methods as $method)
		{
			$row_base['method'] = $method;
			$sql_insert_data[] = $row_base;
		}

		return $sql_insert_data;
	}
}
