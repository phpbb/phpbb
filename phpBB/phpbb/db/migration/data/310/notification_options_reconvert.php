<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_db_migration_data_310_notification_options_reconvert extends phpbb_db_migration
{
	static public function depends_on()
	{
		return array('phpbb_db_migration_data_310_notifications_schema_fix');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'convert_notifications'))),
		);
	}

	public function convert_notifications()
	{
		$insert_table = $this->table_prefix . 'user_notifications';
		$insert_buffer = new phpbb_db_sql_insert_buffer($this->db, $insert_table);

		$this->perform_conversion($insert_buffer, $insert_table);
	}

	/**
	* Perform the conversion (separate for testability)
	*
	* @param phpbb_db_sql_insert_buffer $insert_buffer
	* @param string $insert_table
	*/
	public function perform_conversion(phpbb_db_sql_insert_buffer $insert_buffer, $insert_table)
	{
		$sql = 'DELETE FROM ' . $insert_table;
		$this->db->sql_query($sql);

		$sql = 'SELECT user_id, user_notify_type, user_notify_pm
			FROM ' . USERS_TABLE;
		$result = $this->db->sql_query($sql);

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

			// Notifications for posts
			foreach (array('post', 'topic') as $item_type)
			{
				$this->add_method_rows(
					$insert_buffer,
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
				$this->add_method_rows(
					$insert_buffer,
					'pm',
					0,
					$row['user_id'],
					$notification_methods
				);
			}
		}
		$this->db->sql_freeresult($result);

		$insert_buffer->flush();
	}

	/**
	* Insert method rows to DB
	*
	* @param phpbb_db_sql_insert_buffer $insert_buffer
	* @param string $item_type
	* @param int $item_id
	* @param int $user_id
	* @param string $methods
	*/
	protected function add_method_rows(phpbb_db_sql_insert_buffer $insert_buffer, $item_type, $item_id, $user_id, array $methods)
	{
		$row_base = array(
			'item_type'		=> $item_type,
			'item_id'		=> (int) $item_id,
			'user_id'		=> (int) $user_id,
			'notify'		=> 1
		);

		foreach ($methods as $method)
		{
			$row_base['method'] = $method;
			$insert_buffer->insert($row_base);
		}
	}
}
