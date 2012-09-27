<?php
/**
*
* @package notifications
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Notifications service class
* @package notifications
*/
class phpbb_notifications_service
{
	protected $phpbb_container;
	protected $db;

	/**
	* Users loaded from the DB
	*
	* @var array Array of user data that we've loaded from the DB
	*/
	protected $users = array();

	public function __construct(ContainerBuilder $phpbb_container)
	{
		$this->phpbb_container = $phpbb_container;

		// Some common things we're going to use
		$this->db = $phpbb_container->get('dbal.conn');
	}

	/**
	* Load the user's notifications
	*
	* @param array $options Optional options to control what notifications are loaded
	*				notification_id		Notification id to load (or array of notification ids)
	*				user_id				User id to load notifications for (Default: $user->data['user_id'])
	*				order_by			Order by (Default: time)
	*				order_dir			Order direction (Default: DESC)
	* 				limit				Number of notifications to load (Default: 5)
	* 				start				Notifications offset (Default: 0)
	* 				all_unread			Load all unread messages? If set to true, count_unread is set to true (Default: false)
	* 				count_unread		Count all unread messages? (Default: false)
	*/
	public function load_notifications($options = array())
	{
		$user = $this->phpbb_container->get('user');

		// Merge default options
		$options = array_merge(array(
			'notification_id'	=> false,
			'user_id'			=> $user->data['user_id'],
			'order_by'			=> 'time',
			'order_dir'			=> 'DESC',
			'limit'				=> 0,
			'start'				=> 0,
			'all_unread'		=> false,
			'count_unread'		=> false,
		), $options);

		// If all_unread, count_unread mus be true
		$options['count_unread'] = ($options['all_unread']) ? true : $options['count_unread'];

		// Anonymous users and bots never receive notifications
		if ($options['user_id'] == $user->data['user_id'] && ($user->data['user_id'] == ANONYMOUS || $user->data['user_type'] == USER_IGNORE))
		{
			return array(
				'notifications'		=> array(),
				'unread_count'		=> 0,
			);
		}

		$notifications = $user_ids = array();
		$load_special = array();
		$count = 0;

		if ($options['count_unread'])
		{
			// Get the total number of unread notifications
			$sql = 'SELECT COUNT(*) AS count
				FROM ' . NOTIFICATIONS_TABLE . '
				WHERE user_id = ' . (int) $options['user_id'] . '
					AND unread = 1';
			$result = $this->db->sql_query($sql);
			$count = (int) $this->db->sql_fetchfield('count', $result);
			$this->db->sql_freeresult($result);
		}

		$rowset = array();

		// Get the main notifications
		$sql = 'SELECT *
			FROM ' . NOTIFICATIONS_TABLE . '
			WHERE user_id = ' . (int) $options['user_id'] .
				(($options['notification_id']) ? ((is_array($options['notification_id'])) ? ' AND ' . $this->db->sql_in_set('notification_id', $options['notification_id']) : ' AND notification_id = ' . (int) $options['notification_id']) : '') . '
				ORDER BY ' . $this->db->sql_escape($options['order_by']) . ' ' . $this->db->sql_escape($options['order_dir']);
		$result = $this->db->sql_query_limit($sql, $options['limit'], $options['start']);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[$row['notification_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		// Get all unread notifications
		if ($count && $options['all_unread'] && !empty($rowset))
		{
			$sql = 'SELECT *
				FROM ' . NOTIFICATIONS_TABLE . '
				WHERE user_id = ' . (int) $options['user_id'] . '
					AND unread = 1
					AND ' . $this->db->sql_in_set('notification_id', array_keys($rowset), true) . '
					ORDER BY ' . $this->db->sql_escape($options['order_by']) . ' ' . $this->db->sql_escape($options['order_dir']);
			$result = $this->db->sql_query_limit($sql, $options['limit'], $options['start']);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$rowset[$row['notification_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		foreach ($rowset as $row)
		{
			$item_type_class_name = $this->get_item_type_class_name($row['item_type'], true);

			$notification = new $item_type_class_name($this->phpbb_container, $row);

			// Array of user_ids to query all at once
			$user_ids = array_merge($user_ids, $notification->users_to_query());

			// Some notification types also require querying additional tables themselves
			if (!isset($load_special[$row['item_type']]))
			{
				$load_special[$row['item_type']] = array();
			}
			$load_special[$row['item_type']] = array_merge($load_special[$row['item_type']], $notification->get_load_special());

			$notifications[] = $notification;
		}

		$this->load_users($user_ids);

		// Allow each type to load it's own special items
		foreach ($load_special as $item_type => $data)
		{
			$item_type_class_name = $this->get_item_type_class_name($item_type, true);

			$item_type_class_name::load_special($this->phpbb_container, $data, $notifications);
		}

		return array(
			'notifications'		=> $notifications,
			'unread_count'		=> $count,
		);
	}

	/**
	* Mark notifications read
	*
	* @param string|array $item_type Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	* @param bool|int|array $item_id Item id or array of item ids. False to mark read for all item ids
	* @param bool|int|array $user_id User id or array of user ids. False to mark read for all user ids
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	*/
	public function mark_notifications_read($item_type, $item_id, $user_id, $time = false)
	{
		if (is_array($item_type))
		{
			foreach ($item_type as $type)
			{
				$this->mark_notifications_read($type, $item_id, $user_id, $time);
			}

			return;
		}

		$time = ($time) ?: time();

		$this->get_item_type_class_name($item_type);

		$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . "
			SET unread = 0
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND time <= " . $time .
				(($item_id !== false) ? ' AND ' . (is_array($item_id) ? $this->db->sql_in_set('item_id', $item_id) : 'item_id = ' . (int) $item_id) : '') .
				(($user_id !== false) ? ' AND ' . (is_array($user_id) ? $this->db->sql_in_set('user_id', $user_id) : 'user_id = ' . (int) $user_id) : '');
		$this->db->sql_query($sql);
	}

	/**
	* Mark notifications read from a parent identifier
	*
	* @param string|array $item_type Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	* @param bool|int|array $item_parent_id Item parent id or array of item parent ids. False to mark read for all item parent ids
	* @param bool|int|array $user_id User id or array of user ids. False to mark read for all user ids
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	*/
	public function mark_notifications_read_by_parent($item_type, $item_parent_id, $user_id, $time = false)
	{
		if (is_array($item_type))
		{
			foreach ($item_type as $type)
			{
				$this->mark_notifications_read_by_parent($type, $item_parent_id, $user_id, $time);
			}

			return;
		}

		$time = ($time) ?: time();

		$item_type_class_name = $this->get_item_type_class_name($item_type);

		$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . "
			SET unread = 0
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND time <= " . $time .
				(($item_parent_id !== false) ? ' AND ' . (is_array($item_parent_id) ? $this->db->sql_in_set('item_parent_id', $item_parent_id) : 'item_parent_id = ' . (int) $item_parent_id) : '') .
				(($user_id !== false) ? ' AND ' . (is_array($user_id) ? $this->db->sql_in_set('user_id', $user_id) : 'user_id = ' . (int) $user_id) : '');
		$this->db->sql_query($sql);
	}

	/**
	* Mark notifications read
	*
	* @param int|array $notification_id Notification id or array of notification ids.
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	*/
	public function mark_notifications_read_by_id($notification_id, $time = false)
	{
		$time = ($time) ?: time();

		$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . "
			SET unread = 0
			WHERE time <= " . $time . '
				AND ' . ((is_array($notification_id)) ? $this->db->sql_in_set('notification_id', $notification_id) : 'notification_id = ' . (int) $notification_id);
		$this->db->sql_query($sql);
	}

	/**
	* Add a notification
	*
	* @param string|array $item_type Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	* @param array $data Data specific for this type that will be inserted
	*/
	public function add_notifications($item_type, $data)
	{
		if (is_array($item_type))
		{
			foreach ($item_type as $type)
			{
				$this->add_notifications($type, $data);
			}

			return;
		}

		$item_type_class_name = $this->get_item_type_class_name($item_type);

		$item_id = $item_type_class_name::get_item_id($data);

		// find out which users want to receive this type of notification
		$notify_users = $item_type_class_name::find_users_for_notification($this->phpbb_container, $data);

		$this->add_notifications_for_users($item_type, $data, $notify_users);
	}

	/**
	* Add a notification for specific users
	*
	* @param string|array $item_type Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	* @param array $data Data specific for this type that will be inserted
	* @param array $notify_users User list to notify
	*/
	public function add_notifications_for_users($item_type, $data, $notify_users)
	{
		if (is_array($item_type))
		{
			foreach ($item_type as $type)
			{
				$this->add_notifications_for_users($type, $data, $notify_users);
			}

			return;
		}

		$item_type_class_name = $this->get_item_type_class_name($item_type);

		$item_id = $item_type_class_name::get_item_id($data);

		$user_ids = array();
		$notification_objects = $notification_methods = array();
		$new_rows = array();

		// Never send notifications to the anonymous user!
		unset($notify_users[ANONYMOUS]);

		// Make sure not to send new notifications to users who've already been notified about this item
		// This may happen when an item was added, but now new users are able to see the item
		// todo Users should not receive notifications from multiple events from the same item (ex: for a topic reply with a quote including your username)
		//		Probably should be handled within each type?
		$sql = 'SELECT user_id
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND item_id = " . (int) $item_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			unset($notify_users[$row['user_id']]);
		}
		$this->db->sql_freeresult($result);

		if (!sizeof($notify_users))
		{
			return;
		}

		// Go through each user so we can insert a row in the DB and then notify them by their desired means
		foreach ($notify_users as $user => $methods)
		{
			$notification = new $item_type_class_name($this->phpbb_container);

			$notification->user_id = (int) $user;

			// Store the creation array in our new rows that will be inserted later
			$new_rows[] = $notification->create_insert_array($data);

			// Users are needed to send notifications
			$user_ids = array_merge($user_ids, $notification->users_to_query());

			foreach ($methods as $method)
			{
				// setup the notification methods and add the notification to the queue
				if ($method) // blank means we just insert it as a notification, but do not notify them by any other means
				{
					if (!isset($notification_methods[$method]))
					{
						$method_class_name = 'phpbb_notifications_method_' . $method;
						$notification_methods[$method] = new $method_class_name($this->phpbb_container);
					}

					$notification_methods[$method]->add_to_queue($notification);
				}
			}
		}

		// insert into the db
		$this->db->sql_multi_insert(NOTIFICATIONS_TABLE, $new_rows);

		// We need to load all of the users to send notifications
		$this->load_users($user_ids);

		// run the queue for each method to send notifications
		foreach ($notification_methods as $method)
		{
			$method->notify();
		}
	}

	/**
	* Update a notification
	*
	* @param string|array $item_type Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	* @param array $data Data specific for this type that will be updated
	*/
	public function update_notifications($item_type, $data)
	{
		if (is_array($item_type))
		{
			foreach ($item_type as $type)
			{
				$this->update_notifications($type, $data);
			}

			return;
		}

		$item_type_class_name = $this->get_item_type_class_name($item_type);

		// Allow the notifications class to over-ride the update_notifications functionality
		if (method_exists($item_type_class_name, 'update_notifications'))
		{
			// Return False to over-ride the rest of the update
			if ($item_type_class_name::update_notifications($this->phpbb_container, $data) === false)
			{
				return;
			}
		}

		$item_id = $item_type_class_name::get_item_id($data);

		$notification = new $item_type_class_name($this->phpbb_container);
		$update_array = $notification->create_update_array($data);

		$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $update_array) . "
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND item_id = " . (int) $item_id;
		$this->db->sql_query($sql);
	}

	/**
	* Delete a notification
	*
	* @param string|array $item_type Type identifier or array of item types (only acceptable if the $item_id is identical for the specified types)
	* @param int|array $item_id Identifier within the type (or array of ids)
	* @param array $data Data specific for this type that will be updated
	*/
	public function delete_notifications($item_type, $item_id)
	{
		if (is_array($item_type))
		{
			foreach ($item_type as $type)
			{
				$this->delete_notifications($type, $item_id);
			}

			return;
		}

		$this->get_item_type_class_name($item_type);

		$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND " . (is_array($item_id) ? $this->db->sql_in_set('item_id', $item_id) : 'item_id = ' . (int) $item_id);
		$this->db->sql_query($sql);
	}

	/**
	* Get all of the subscription types
	*
	* @return array Array of item types
	*/
	public function get_subscription_types()
	{
		$subscription_types = array();

		foreach ($this->get_subscription_files('notifications/type/') as $class => $file)
		{
			$class = $this->get_item_type_class_name($class);

			if (!class_exists($class))
			{
				include($file);
			}

			if (method_exists($class, 'get_item_type'))
			{
				$subscription_types[] = $class::get_item_type();
			}
		}

		return $subscription_types;
	}

	/**
	* Get all of the subscription methods
	*
	* @return array Array of methods
	*/
	public function get_subscription_methods()
	{
		$subscription_methods = array();

		foreach ($this->get_subscription_files('notifications/method/') as $method_name => $file)
		{
			$class_name = 'phpbb_notifications_method_' . $method_name;

			if (!class_exists($class_name))
			{
				include($file);
			}

			$method = new $class_name($this->phpbb_container);

			if ($method->is_available())
			{
				$subscription_methods[] = $method_name;
			}
		}

		return $subscription_methods;
	}

	/**
	* Add a subscription
	*
	* @param string $item_type Type identifier of the subscription
	* @param int $item_id The id of the item
	* @param string $method The method of the notification e.g. '', 'email', or 'jabber'
	* @param bool|int $user_id The user_id to add the subscription for (bool false for current user)
	*/
	public function add_subscription($item_type, $item_id, $method = '', $user_id = false)
	{
		$this->get_item_type_class_name($item_type);

		$user_id = ($user_id === false) ? $this->phpbb_container->get('user')->data['user_id'] : $user_id;

		$sql = 'INSERT INTO ' . USER_NOTIFICATIONS_TABLE . ' ' .
			$this->db->sql_build_array('INSERT', array(
				'item_type'		=> $item_type,
				'item_id'		=> (int) $item_id,
				'user_id'		=> (int) $user_id,
				'method'		=> $method,
			));
		$this->db->sql_query($sql);
	}

	/**
	* Delete a subscription
	*
	* @param string $item_type Type identifier of the subscription
	* @param int $item_id The id of the item
	* @param string $method The method of the notification e.g. '', 'email', or 'jabber'
	* @param bool|int $user_id The user_id to add the subscription for (bool false for current user)
	*/
	public function delete_subscription($item_type, $item_id, $method = '', $user_id = false)
	{
		$this->get_item_type_class_name($item_type);

		$user_id = ($user_id === false) ? $this->phpbb_container->get('user')->data['user_id'] : $user_id;

		$sql = 'DELETE FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND item_id = " . (int) $item_id . '
				AND user_id = ' .(int) $user_id . "
				AND method = '" . $this->db->sql_escape($method) . "'";
		$this->db->sql_query($sql);
	}

	/**
	* Load user helper
	*
	* @param array $user_ids
	*/
	public function load_users($user_ids)
	{
		// Load the users
		$user_ids = array_unique($user_ids);

		// Do not load users we already have in $this->users
		$user_ids = array_diff($user_ids, array_keys($this->users));

		if (sizeof($user_ids))
		{
			$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->users[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}
	}

	/**
	* Get a user row from our users cache
	*
	* @param int $user_id
	* @return array
	*/
	public function get_user($user_id)
	{
		return $this->users[$user_id];
	}

	/**
	* Helper to get the notifications item type class name and clean it if unsafe
	*/
	private function get_item_type_class_name(&$item_type, $safe = false)
	{
		if (!$safe)
		{
			$item_type = preg_replace('#[^a-z_]#', '', $item_type);
		}

		return 'phpbb_notifications_type_' . $item_type;
	}

	/**
	* Helper to get subscription related files with the finder
	*/
	private function get_subscription_files($path)
	{
		$ext_manager = $this->phpbb_container->get('ext.manager');
		$php_ext = $this->phpbb_container->getParameter('core.php_ext');

		$finder = $ext_manager->get_finder();

		$subscription_files = array();

		$files = $finder
			->core_path('includes/' . $path)
			->extension_directory($path)
			->get_files();
		foreach ($files as $file)
		{
			$class = substr($file, strrpos($file, '/'));
			$class = substr($class, 1, (strpos($class, '.' . $php_ext) - 1));

			if ($class == 'interface' || $class == 'base')
			{
				continue;
			}

			$subscription_files[$class] = $file;
		}

		return $subscription_files;
	}
}
