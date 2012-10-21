<?php
/**
*
* @package notifications
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

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
class phpbb_notification_manager
{
	/** @var dbal */
	protected $db = null;

	/** @var phpbb_cache_service */
	protected $cache = null;

	/** @var phpbb_template */
	protected $template = null;

	/** @var phpbb_extension_manager */
	protected $extension_manager = null;

	/** @var phpbb_user */
	protected $user = null;

	/** @var phpbb_auth */
	protected $auth = null;

	/** @var phpbb_config */
	protected $config = null;

	/** @var string */
	protected $phpbb_root_path = null;

	/** @var string */
	protected $php_ext = null;

	/**
	* Users loaded from the DB
	*
	* @var array Array of user data that we've loaded from the DB
	*/
	protected $users = array();

	public function __construct(dbal $db, phpbb_cache_driver_interface $cache, phpbb_template $template, phpbb_extension_manager $extension_manager, $user, phpbb_auth $auth, phpbb_config $config, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->extension_manager = $extension_manager;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
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
		// Merge default options
		$options = array_merge(array(
			'notification_id'	=> false,
			'user_id'			=> $this->user->data['user_id'],
			'order_by'			=> 'time',
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
			$sql = 'SELECT COUNT(*) AS count
				FROM ' . NOTIFICATIONS_TABLE . '
				WHERE user_id = ' . (int) $options['user_id'] . '
					AND unread = 1
					AND is_enabled = 1';
			$result = $this->db->sql_query($sql);
			$unread_count = (int) $this->db->sql_fetchfield('count', $result);
			$this->db->sql_freeresult($result);
		}

		if ($options['count_total'])
		{
			// Get the total number of notifications
			$sql = 'SELECT COUNT(*) AS count
				FROM ' . NOTIFICATIONS_TABLE . '
				WHERE user_id = ' . (int) $options['user_id'] . '
					AND is_enabled = 1';
			$result = $this->db->sql_query($sql);
			$total_count = (int) $this->db->sql_fetchfield('count', $result);
			$this->db->sql_freeresult($result);
		}

		if (!$options['count_total'] || $total_count)
		{
			$rowset = array();

			// Get the main notifications
			$sql = 'SELECT *
				FROM ' . NOTIFICATIONS_TABLE . '
				WHERE user_id = ' . (int) $options['user_id'] .
					(($options['notification_id']) ? ((is_array($options['notification_id'])) ? ' AND ' . $this->db->sql_in_set('notification_id', $options['notification_id']) : ' AND notification_id = ' . (int) $options['notification_id']) : '') . '
			 		AND is_enabled = 1
				ORDER BY ' . $this->db->sql_escape($options['order_by']) . ' ' . $this->db->sql_escape($options['order_dir']);
			$result = $this->db->sql_query_limit($sql, $options['limit'], $options['start']);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$rowset[$row['notification_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			// Get all unread notifications
			if ($unread_count && $options['all_unread'] && !empty($rowset))
			{
				$sql = 'SELECT *
					FROM ' . NOTIFICATIONS_TABLE . '
					WHERE user_id = ' . (int) $options['user_id'] . '
						AND unread = 1
						AND ' . $this->db->sql_in_set('notification_id', array_keys($rowset), true) . '
						AND is_enabled = 1
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
				$notification = $this->get_item_type_class($row['item_type'], $row);

				// Array of user_ids to query all at once
				$user_ids = array_merge($user_ids, $notification->users_to_query());

				// Some notification types also require querying additional tables themselves
				if (!isset($load_special[$row['item_type']]))
				{
					$load_special[$row['item_type']] = array();
				}
				$load_special[$row['item_type']] = array_merge($load_special[$row['item_type']], $notification->get_load_special());

				$notifications[$row['notification_id']] = $notification;
			}

			$this->load_users($user_ids);

			// Allow each type to load its own special items
			foreach ($load_special as $item_type => $data)
			{
				$item_class = $this->get_item_type_class($item_type);

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
	* Mark notifications read
	*
	* @param bool|string|array $item_type Type identifier or array of item types (only acceptable if the $data is identical for the specified types). False to mark read for all item types
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

		$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . "
			SET unread = 0
			WHERE time <= " . $time .
				(($item_type !== false) ? ' AND ' . (is_array($item_type) ? $this->db->sql_in_set('item_type', $item_type) : " item_type = '" . $this->db->sql_escape($item_type) . "'") : '') .
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
	*			Note: If you send an array of types, any user who could receive multiple notifications from this single item will only receive
	* 			a single notification. If they MUST receive multiple notifications, call this function multiple times instead of sending an array
	* @param array $data Data specific for this type that will be inserted
	*/
	public function add_notifications($item_type, $data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		if (is_array($item_type))
		{
			$notified_users = array();
			$temp_options = $options;

			foreach ($item_type as $type)
			{
				$temp_options['ignore_users'] = $options['ignore_users'] + $notified_users;
				$notified_users += $this->add_notifications($type, $data, $temp_options);
			}

			return $notified_users;
		}

		$item_id = $item_type::get_item_id($data);

		// find out which users want to receive this type of notification
		$notify_users = $this->get_item_type_class($item_type)->find_users_for_notification($data, $options);

		$this->add_notifications_for_users($item_type, $data, $notify_users);

		return $notify_users;
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

		$item_id = $item_type::get_item_id($data);

		$user_ids = array();
		$notification_objects = $notification_methods = array();
		$new_rows = array();

		// Never send notifications to the anonymous user!
		unset($notify_users[ANONYMOUS]);

		// Make sure not to send new notifications to users who've already been notified about this item
		// This may happen when an item was added, but now new users are able to see the item
		$sql = 'SELECT user_id
			FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND item_id = " . (int) $item_id . '
				AND is_enabled = 1';
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

		// Allow notifications to perform actions before creating the insert array (such as run a query to cache some data needed for all notifications)
		$notification = $this->get_item_type_class($item_type);
		$pre_create_data = $notification->pre_create_insert_array($data, $notify_users);
		unset($notification);

		// Go through each user so we can insert a row in the DB and then notify them by their desired means
		foreach ($notify_users as $user => $methods)
		{
			$notification = $this->get_item_type_class($item_type);

			$notification->user_id = (int) $user;

			// Store the creation array in our new rows that will be inserted later
			$new_rows[] = $notification->create_insert_array($data, $pre_create_data);

			// Users are needed to send notifications
			$user_ids = array_merge($user_ids, $notification->users_to_query());

			foreach ($methods as $method)
			{
				// setup the notification methods and add the notification to the queue
				if ($method) // blank means we just insert it as a notification, but do not notify them by any other means
				{
					if (!isset($notification_methods[$method]))
					{
						$notification_methods[$method] = $this->get_method_class($method);
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

		$notification = $this->get_item_type_class($item_type);

		// Allow the notifications class to over-ride the update_notifications functionality
		if (method_exists($notification, 'update_notifications'))
		{
			// Return False to over-ride the rest of the update
			if ($notification->update_notifications($data) === false)
			{
				return;
			}
		}

		$item_id = $item_type::get_item_id($data);
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

		foreach ($this->get_subscription_files('notification/type/') as $class_name)
		{
			$class = $this->get_item_type_class($class_name);

			if ($class instanceof phpbb_notification_type_interface && $class->is_available())
			{
				$options = array_merge(array(
					'id'		=> $class_name,
					'lang'		=> 'NOTIFICATION_TYPE_' . strtoupper($class_name),
					'group'		=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
				), (($class_name::$notification_option !== false) ? $class_name::$notification_option : array()));

				$subscription_types[$options['group']][$options['id']] = $options;
			}
		}

		// Move Miscellaneous to the very last section
		if (isset($subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS']))
		{
			$miscellaneous = $subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS'];
			unset($subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS']);
			$subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS'] = $miscellaneous;
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

		foreach ($this->get_subscription_files('notification/method/') as $class_name)
		{
			$method = $this->get_method_class($class_name);

			if ($method instanceof phpbb_notification_method_interface && $method->is_available())
			{
				$subscription_methods[] = $class_name;
			}
		}

		return $subscription_methods;
	}

	/**
	* Get subscriptions
	*
	* @param bool|int $user_id The user_id to add the subscription for (bool false for current user)
	* @param bool $only_global True to select only global subscription options (item_id = 0)
	*
	* @return array Subscriptions
	*/
	public function get_subscriptions($user_id = false, $only_global = false)
	{
		$user_id = ($user_id === false) ? $this->user->data['user_id'] : $user_id;

		$subscriptions = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . '
			WHERE user_id = ' . (int) $user_id .
				(($only_global) ? ' AND item_id = 0' : '');
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($only_global)
			{
				if (!isset($subscriptions[$row['item_type']]))
				{
					$subscriptions[$row['item_type']] = array();
				}

				$subscriptions[$row['item_type']][] = $row['method'];
			}
			else
			{
				$subscriptions[] = $row;
			}
		}
		$this->db->sql_freeresult($result);

		return $subscriptions;
	}

	/**
	* Add a subscription
	*
	* @param string $item_type Type identifier of the subscription
	* @param int $item_id The id of the item
	* @param string $method The method of the notification e.g. '', 'email', or 'jabber'
	* @param bool|int $user_id The user_id to add the subscription for (bool false for current user)
	*/
	public function add_subscription($item_type, $item_id = 0, $method = '', $user_id = false)
	{
		$user_id = ($user_id === false) ? $this->user->data['user_id'] : $user_id;

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
	public function delete_subscription($item_type, $item_id = 0, $method = '', $user_id = false)
	{
		$user_id = ($user_id === false) ? $this->user->data['user_id'] : $user_id;

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
		$user_ids[] = ANONYMOUS;

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
		return (isset($this->users[$user_id])) ? $this->users[$user_id] : $this->users[ANONYMOUS];
	}

	/**
	* Helper to get the notifications item type class and set it up
	*/
	public function get_item_type_class($item_type, $data = array())
	{
		$item = new $item_type($this, $this->db, $this->cache, $this->template, $this->extension_manager, $this->user, $this->auth, $this->config, $this->phpbb_root_path, $this->php_ext);

		$item->set_initial_data($data);

		return $item;
	}

	/**
	* Helper to get the notifications method class and set it up
	*/
	public function get_method_class($method_name)
	{
		return new $method_name($this, $this->db, $this->cache, $this->template, $this->extension_manager, $this->user, $this->auth, $this->config, $this->phpbb_root_path, $this->php_ext);
	}

	/**
	* Helper to get subscription related files with the finder
	*/
	private function get_subscription_files($path)
	{
		$finder = $this->extension_manager->get_finder();

		$subscription_files = array();

		$classes = $finder
			->core_path('includes/' . $path)
			->extension_directory($path)
			->get_classes();

		unset($classes[array_search('phpbb_notification_type_interface', $classes)]);
		unset($classes[array_search('phpbb_notification_type_base', $classes)]);
		unset($classes[array_search('phpbb_notification_method_interface', $classes)]);
		unset($classes[array_search('phpbb_notification_method_base', $classes)]);

		return $classes;
	}
}
