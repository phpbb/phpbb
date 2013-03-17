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
						'notification_id'  				=> array('UINT:10', NULL, 'auto_increment'),
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
		$convert_notifications = array(
			array(
				'check'			=> ($this->config['allow_topic_notify']),
				'item_type'		=> 'post',
			),
			array(
				'check'			=> ($this->config['allow_forum_notify']),
				'item_type'		=> 'topic',
			),
			array(
				'check'			=> ($this->config['allow_bookmarks']),
				'item_type'		=> 'bookmark',
			),
			array(
				'check'			=> ($this->config['allow_privmsg']),
				'item_type'		=> 'pm',
			),
		);

		foreach ($convert_notifications as $convert_data)
		{
			if ($convert_data['check'])
			{
				$sql = 'SELECT user_id, user_notify_type
					FROM ' . USERS_TABLE . '
						WHERE user_notify = 1';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->sql_query('INSERT INTO ' . $this->table_prefix . 'user_notifications ' . $this->db->sql_build_array('INSERT', array(
						'item_type'		=> $convert_data['item_type'],
						'item_id'		=> 0,
						'user_id'		=> $row['user_id'],
						'method'		=> '',
					)));

					if ($row['user_notify_type'] == NOTIFY_EMAIL || $row['user_notify_type'] == NOTIFY_BOTH)
					{
						$this->sql_query('INSERT INTO ' . $this->table_prefix . 'user_notifications ' . $this->db->sql_build_array('INSERT', array(
							'item_type'		=> $convert_data['item_type'],
							'item_id'		=> 0,
							'user_id'		=> $row['user_id'],
							'method'		=> 'email',
						)));
					}

					if ($row['user_notify_type'] == NOTIFY_IM || $row['user_notify_type'] == NOTIFY_BOTH)
					{
						$this->sql_query('INSERT INTO ' . $this->table_prefix . 'user_notifications ' . $this->db->sql_build_array('INSERT', array(
							'item_type'		=> $convert_data['item_type'],
							'item_id'		=> 0,
							'user_id'		=> $row['user_id'],
							'method'		=> 'jabber',
						)));
					}
				}
				$this->db->sql_freeresult($result);
			}
		}
	}
}
