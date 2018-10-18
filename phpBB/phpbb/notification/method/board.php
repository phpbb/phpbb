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

namespace phpbb\notification\method;

/**
* In Board notification method class
* This class handles in board notifications. This method is enabled by default.
*
* @package notifications
*/
class board extends \phpbb\notification\method\base
{
	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var string */
	protected $notification_types_table;

	/** @var string */
	protected $notifications_table;

	/**
	* Notification Method Board Constructor
	*
	* @param \phpbb\user_loader $user_loader
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\cache\driver\driver_interface $cache
	* @param \phpbb\user $user
	* @param \phpbb\config\config $config
	* @param string $notification_types_table
	* @param string $notifications_table
	*/
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\user $user, \phpbb\config\config $config, $notification_types_table, $notifications_table)
	{
		$this->user_loader = $user_loader;
		$this->db = $db;
		$this->cache = $cache;
		$this->user = $user;
		$this->config = $config;
		$this->notification_types_table = $notification_types_table;
		$this->notifications_table = $notifications_table;

	}

	/**
	* {@inheritdoc}
	*/
	public function add_to_queue(\phpbb\notification\type\type_interface $notification)
	{
		$this->queue[] = $notification;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'notification.method.board';
	}

	/**
	* {@inheritdoc}
	*/
	public function is_available()
	{
		return $this->config['allow_board_notifications'];
	}

	/**
	* {@inheritdoc}
	*/
	public function is_enabled_by_default()
	{
		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_notified_users($notification_type_id, array $options)
	{
		$notified_users = array();
		$sql = 'SELECT n.*
			FROM ' . $this->notifications_table . ' n, ' . $this->notification_types_table . ' nt
			WHERE n.notification_type_id = ' . (int) $notification_type_id .
			(isset($options['item_id']) ? ' AND n.item_id = ' . (int) $options['item_id'] : '') .
			(isset($options['item_parent_id']) ? ' AND n.item_parent_id = ' . (int) $options['item_parent_id'] : '') .
			(isset($options['user_id']) ? ' AND n.user_id = ' . (int) $options['user_id'] : '') .
			(isset($options['read']) ? ' AND n.notification_read = ' . (int) $options['read'] : '') .'
				AND nt.notification_type_id = n.notification_type_id
				AND nt.notification_type_enabled = 1';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$notified_users[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $notified_users;
	}

	/**
	* {@inheritdoc}
	*/
	public function load_notifications(array $options = array())
	{
		// Merge default options
		$options = array_merge(array(
			'notification_id'	=> false,
			'user_id'			=> $this->user->data['user_id'],
			'order_by'			=> 'notification_time',
			'order_dir'			=> 'DESC',
			'limit'				=> 0,
			'start'				=> 0,
			'all_unread'		=> false,
			'count_unread'		=> false,
			'count_total'		=> false,
		), $options);

		// If all_unread, count_unread must be true
		$options['count_unread'] = ($options['all_unread']) ? true : $options['count_unread'];

		// Anonymous users and bots never receive notifications
		if ($options['user_id'] == $this->user->data['user_id'] && ($this->user->data['user_id'] == ANONYMOUS || $this->user->data['user_type'] == USER_IGNORE))
		{
			return array(
				'notifications'		=> array(),
				'unread_count'		=> 0,
				'total_count'		=> 0,
			);
		}

		$notifications = $user_ids = array();
		$load_special = array();
		$total_count = $unread_count = 0;

		if ($options['count_unread'])
		{
			// Get the total number of unread notifications
			$sql = 'SELECT COUNT(n.notification_id) AS unread_count
				FROM ' . $this->notifications_table . ' n, ' . $this->notification_types_table . ' nt
				WHERE n.user_id = ' . (int) $options['user_id'] . '
					AND n.notification_read = 0
					AND nt.notification_type_id = n.notification_type_id
					AND nt.notification_type_enabled = 1';
			$result = $this->db->sql_query($sql);
			$unread_count = (int) $this->db->sql_fetchfield('unread_count');
			$this->db->sql_freeresult($result);
		}

		if ($options['count_total'])
		{
			// Get the total number of notifications
			$sql = 'SELECT COUNT(n.notification_id) AS total_count
				FROM ' . $this->notifications_table . ' n, ' . $this->notification_types_table . ' nt
				WHERE n.user_id = ' . (int) $options['user_id'] . '
					AND nt.notification_type_id = n.notification_type_id
					AND nt.notification_type_enabled = 1';
			$result = $this->db->sql_query($sql);
			$total_count = (int) $this->db->sql_fetchfield('total_count');
			$this->db->sql_freeresult($result);
		}

		if (!$options['count_total'] || $total_count)
		{
			$rowset = array();

			// Get the main notifications
			$sql = 'SELECT n.*, nt.notification_type_name
				FROM ' . $this->notifications_table . ' n, ' . $this->notification_types_table . ' nt
				WHERE n.user_id = ' . (int) $options['user_id'] .
				(($options['notification_id']) ? ((is_array($options['notification_id'])) ? ' AND ' . $this->db->sql_in_set('n.notification_id', $options['notification_id']) : ' AND n.notification_id = ' . (int) $options['notification_id']) : '') . '
					AND nt.notification_type_id = n.notification_type_id
					AND nt.notification_type_enabled = 1
				ORDER BY n.' . $this->db->sql_escape($options['order_by']) . ' ' . $this->db->sql_escape($options['order_dir']);
			$result = $this->db->sql_query_limit($sql, $options['limit'], $options['start']);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$rowset[$row['notification_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			// Get all unread notifications
			if ($unread_count && $options['all_unread'] && !empty($rowset))
			{
				$sql = 'SELECT n.*, nt.notification_type_name
				FROM ' . $this->notifications_table . ' n, ' . $this->notification_types_table . ' nt
					WHERE n.user_id = ' . (int) $options['user_id'] . '
						AND n.notification_read = 0
						AND ' . $this->db->sql_in_set('n.notification_id', array_keys($rowset), true) . '
						AND nt.notification_type_id = n.notification_type_id
						AND nt.notification_type_enabled = 1
					ORDER BY n.' . $this->db->sql_escape($options['order_by']) . ' ' . $this->db->sql_escape($options['order_dir']);
				$result = $this->db->sql_query_limit($sql, $options['limit'], $options['start']);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$rowset[$row['notification_id']] = $row;
				}
				$this->db->sql_freeresult($result);
			}

			foreach ($rowset as $row)
			{
				$notification = $this->notification_manager->get_item_type_class($row['notification_type_name'], $row);

				// Array of user_ids to query all at once
				$user_ids = array_merge($user_ids, $notification->users_to_query());

				// Some notification types also require querying additional tables themselves
				if (!isset($load_special[$row['notification_type_name']]))
				{
					$load_special[$row['notification_type_name']] = array();
				}
				$load_special[$row['notification_type_name']] = array_merge($load_special[$row['notification_type_name']], $notification->get_load_special());

				$notifications[$row['notification_id']] = $notification;
			}

			$this->user_loader->load_users($user_ids);

			// Allow each type to load its own special items
			foreach ($load_special as $item_type => $data)
			{
				$item_class = $this->notification_manager->get_item_type_class($item_type);

				$item_class->load_special($data, $notifications);
			}
		}

		return array(
			'notifications'		=> $notifications,
			'unread_count'		=> $unread_count,
			'total_count'		=> $total_count,
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function notify()
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->notifications_table);

		/** @var \phpbb\notification\type\type_interface $notification */
		foreach ($this->queue as $notification)
		{
			$data = $notification->get_insert_array();
			$insert_buffer->insert($data);
		}

		$insert_buffer->flush();

		// We're done, empty the queue
		$this->empty_queue();
	}

	/**
	* {@inheritdoc}
	*/
	public function update_notification($notification, array $data, array $options)
	{
		// Allow the notifications class to over-ride the update_notifications functionality
		if (method_exists($notification, 'update_notifications'))
		{
			// Return False to over-ride the rest of the update
			if ($notification->update_notifications($data) === false)
			{
				return;
			}
		}

		$notification_type_id = $this->notification_manager->get_notification_type_id($notification->get_type());
		$update_array = $notification->create_update_array($data);

		$sql = 'UPDATE ' . $this->notifications_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $update_array) . '
			WHERE notification_type_id = ' . (int) $notification_type_id .
			(isset($options['item_id']) ? ' AND item_id = ' . (int) $options['item_id'] : '') .
			(isset($options['item_parent_id']) ? ' AND item_parent_id = ' . (int) $options['item_parent_id'] : '') .
			(isset($options['user_id']) ? ' AND user_id = ' . (int) $options['user_id'] : '') .
			(isset($options['read']) ? ' AND notification_read = ' . (int) $options['read'] : '');
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications($notification_type_id, $item_id, $user_id, $time = false, $mark_read = true)
	{
		$time = ($time !== false) ? $time : time();

		$sql = 'UPDATE ' . $this->notifications_table . '
			SET notification_read = ' . ($mark_read ? 1 : 0) . '
			WHERE notification_time <= ' . (int) $time .
			(($notification_type_id !== false) ? ' AND ' .
				(is_array($notification_type_id) ? $this->db->sql_in_set('notification_type_id', $notification_type_id) : 'notification_type_id = ' . $notification_type_id) : '') .
			(($user_id !== false) ? ' AND ' . (is_array($user_id) ? $this->db->sql_in_set('user_id', $user_id) : 'user_id = ' . (int) $user_id) : '') .
			(($item_id !== false) ? ' AND ' . (is_array($item_id) ? $this->db->sql_in_set('item_id', $item_id) : 'item_id = ' . (int) $item_id) : '');
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications_by_parent($notification_type_id, $item_parent_id, $user_id, $time = false, $mark_read = true)
	{
		$time = ($time !== false) ? $time : time();

		$sql = 'UPDATE ' . $this->notifications_table . '
			SET notification_read = ' . ($mark_read ? 1 : 0) . '
			WHERE notification_time <= ' . (int) $time .
			(($notification_type_id !== false) ? ' AND ' .
				(is_array($notification_type_id) ? $this->db->sql_in_set('notification_type_id', $notification_type_id) : 'notification_type_id = ' . $notification_type_id) : '') .
			(($item_parent_id !== false) ? ' AND ' . (is_array($item_parent_id) ? $this->db->sql_in_set('item_parent_id', $item_parent_id, false, true) : 'item_parent_id = ' . (int) $item_parent_id) : '') .
			(($user_id !== false) ? ' AND ' . (is_array($user_id) ? $this->db->sql_in_set('user_id', $user_id) : 'user_id = ' . (int) $user_id) : '');
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications_by_id($notification_id, $time = false, $mark_read = true)
	{
		$time = ($time !== false) ? $time : time();

		$sql = 'UPDATE ' . $this->notifications_table . '
			SET notification_read = ' . ($mark_read ? 1 : 0) . '
			WHERE notification_time <= ' . (int) $time . '
				AND ' . ((is_array($notification_id)) ? $this->db->sql_in_set('notification_id', $notification_id) : 'notification_id = ' . (int) $notification_id);
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function delete_notifications($notification_type_id, $item_id, $parent_id = false, $user_id = false)
	{
		$sql = 'DELETE FROM ' . $this->notifications_table . '
			WHERE notification_type_id = ' . (int) $notification_type_id . '
				AND ' . (is_array($item_id) ? $this->db->sql_in_set('item_id', $item_id) : 'item_id = ' . (int) $item_id) .
			(($parent_id !== false) ? ' AND ' . ((is_array($parent_id) ? $this->db->sql_in_set('item_parent_id', $parent_id) : 'item_parent_id = ' . (int) $parent_id)) : '') .
			(($user_id !== false) ? ' AND ' . ((is_array($user_id) ? $this->db->sql_in_set('user_id', $user_id) : 'user_id = ' . (int) $user_id)) : '');
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function prune_notifications($timestamp, $only_read = true)
	{
		$sql = 'DELETE FROM ' . $this->notifications_table . '
			WHERE notification_time < ' . (int) $timestamp .
			(($only_read) ? ' AND notification_read = 1' : '');
		$this->db->sql_query($sql);

		$this->config->set('read_notification_last_gc', time(), false);
	}

	/**
	* {@inheritdoc}
	*/
	public function purge_notifications($notification_type_id)
	{
		$sql = 'DELETE FROM ' . $this->notifications_table . '
			WHERE notification_type_id = ' . (int) $notification_type_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->notification_types_table . '
			WHERE notification_type_id = ' . (int) $notification_type_id;
		$this->db->sql_query($sql);

		$this->cache->destroy('sql', $this->notification_types_table);
	}
}
