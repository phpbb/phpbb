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

class notification_options_reconvert extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\notifications_schema_fix');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'purge_notifications'))),
			array('custom', array(array($this, 'convert_notifications'))),
		);
	}

	public function purge_notifications()
	{
		$sql = 'DELETE FROM ' . $this->table_prefix . 'user_notifications';
		$this->sql_query($sql);
	}

	public function convert_notifications($start)
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->table_prefix . 'user_notifications');

		return $this->perform_conversion($insert_buffer, $start);
	}

	/**
	* Perform the conversion (separate for testability)
	*
	* @param \phpbb\db\sql_insert_buffer		$insert_buffer
	* @param int			$start		Start of staggering step
	* @return		mixed		int start of the next step, null if the end was reached
	*/
	public function perform_conversion(\phpbb\db\sql_insert_buffer $insert_buffer, $start)
	{
		$limit = 250;
		$converted_users = 0;

		$sql = 'SELECT user_id, user_notify_type, user_notify_pm
			FROM ' . $this->table_prefix . 'users
			ORDER BY user_id';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$converted_users++;
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

		if ($converted_users < $limit)
		{
			// No more users left, we are done...
			return;
		}

		return $start + $limit;
	}

	/**
	* Insert method rows to DB
	*
	* @param \phpbb\db\sql_insert_buffer $insert_buffer
	* @param string $item_type
	* @param int $item_id
	* @param int $user_id
	* @param string $methods
	*/
	protected function add_method_rows(\phpbb\db\sql_insert_buffer $insert_buffer, $item_type, $item_id, $user_id, array $methods)
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
