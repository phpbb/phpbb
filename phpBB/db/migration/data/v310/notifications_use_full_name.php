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

class notifications_use_full_name extends \phpbb\db\migration\migration
{
	protected $notification_types = array(
		'admin_activate_user',
		'approve_post',
		'approve_topic',
		'bookmark',
		'disapprove_post',
		'disapprove_topic',
		'group_request',
		'group_request_approved',
		'pm',
		'post',
		'post_in_queue',
		'quote',
		'report_pm',
		'report_pm_closed',
		'report_post',
		'report_post_closed',
		'topic',
		'topic_in_queue');

	protected $notification_methods = array(
		'email',
		'jabber',
	);

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\rc3');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_notifications_name'))),
			array('custom', array(array($this, 'update_notifications_method_name'))),
		);
	}

	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'revert_notifications_name'))),
			array('custom', array(array($this, 'revert_notifications_method_name'))),
		);
	}

	public function update_notifications_method_name()
	{
		foreach ($this->notification_methods as $notification_method)
		{
			$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
				SET method = 'notification.method.{$notification_method}'
				WHERE method = '{$notification_method}'";
			$this->db->sql_query($sql);
		}
	}

	public function revert_notifications_method_name()
	{
		foreach ($this->notification_methods as $notification_method)
		{
			$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
				SET method = '{$notification_method}'
				WHERE method = 'notification.method.{$notification_method}'";
			$this->db->sql_query($sql);
		}
	}

	public function update_notifications_name()
	{
		$sql = 'UPDATE ' . NOTIFICATION_TYPES_TABLE . '
			SET notification_type_enabled = 0
			WHERE ' . $this->db->sql_in_set('notification_type_name', $this->notification_types, true);
		$this->db->sql_query($sql);

		foreach ($this->notification_types as $notification_type)
		{
			$sql = 'SELECT notification_type_id
				FROM ' . NOTIFICATION_TYPES_TABLE . "
				WHERE notification_type_name = 'notification.type.{$notification_type}'";
			$result = $this->db->sql_query($sql);
			$new_type_id = (int) $this->db->sql_fetchfield('notification_type_id');
			$this->db->sql_freeresult($result);

			if ($new_type_id)
			{
				// New type name already exists,
				// so we delete the old type and update the type id of existing entries.
				$sql = 'SELECT notification_type_id
					FROM ' . NOTIFICATION_TYPES_TABLE . "
					WHERE notification_type_name = '{$notification_type}'";
				$result = $this->db->sql_query($sql);
				$old_type_id = (int) $this->db->sql_fetchfield('notification_type_id');
				$this->db->sql_freeresult($result);

				$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . '
					SET notification_type_id = ' . (int) $new_type_id . '
					WHERE notification_type_id = ' . (int) $old_type_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . NOTIFICATION_TYPES_TABLE . "
					WHERE notification_type_name = '{$notification_type}'";
				$this->db->sql_query($sql);
			}
			else
			{
				// Otherwise we just update the name
				$sql = 'UPDATE ' . NOTIFICATION_TYPES_TABLE . "
					SET notification_type_name = 'notification.type.{$notification_type}'
					WHERE notification_type_name = '{$notification_type}'";
				$this->db->sql_query($sql);
			}

			$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
				SET item_type = 'notification.type.{$notification_type}'
				WHERE item_type = '{$notification_type}'";
			$this->db->sql_query($sql);
		}
	}

	public function revert_notifications_name()
	{
		foreach ($this->notification_types as $notification_type)
		{
			$sql = 'SELECT notification_type_id
				FROM ' . NOTIFICATION_TYPES_TABLE . "
				WHERE notification_type_name = '{$notification_type}'";
			$result = $this->db->sql_query($sql);
			$new_type_id = (int) $this->db->sql_fetchfield('notification_type_id');
			$this->db->sql_freeresult($result);

			if ($new_type_id)
			{
				// New type name already exists,
				// so we delete the old type and update the type id of existing entries.
				$sql = 'SELECT notification_type_id
					FROM ' . NOTIFICATION_TYPES_TABLE . "
					WHERE notification_type_name = 'notification.type.{$notification_type}'";
				$result = $this->db->sql_query($sql);
				$old_type_id = (int) $this->db->sql_fetchfield('notification_type_id');
				$this->db->sql_freeresult($result);

				$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . '
					SET notification_type_id = ' . (int) $new_type_id . '
					WHERE notification_type_id = ' . (int) $old_type_id;
				$this->db->sql_query($sql);

				$sql = 'DELETE FROM ' . NOTIFICATION_TYPES_TABLE . "
					WHERE notification_type_name = 'notification.type.{$notification_type}'";
				$this->db->sql_query($sql);
			}
			else
			{
				// Otherwise we just update the name
				$sql = 'UPDATE ' . NOTIFICATION_TYPES_TABLE . "
					SET notification_type_name = '{$notification_type}'
					WHERE notification_type_name = 'notification.type.{$notification_type}'";
				$this->db->sql_query($sql);
			}

			$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
				SET item_type = '{$notification_type}'
				WHERE item_type = 'notification.type.{$notification_type}'";
			$this->db->sql_query($sql);
		}
	}
}
