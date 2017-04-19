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

namespace phpbb\notification;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Notifications service class
*/
class manager
{
	/** @var array */
	protected $notification_types;

	/** @var array */
	protected $subscription_types;

	/** @var method\method_interface[] */
	protected $notification_methods;

	/** @var ContainerInterface */
	protected $phpbb_container;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\event\dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $notification_types_table;

	/** @var string */
	protected $user_notifications_table;

	/**
	* Notification Constructor
	*
	* @param array $notification_types
	* @param array $notification_methods
	* @param ContainerInterface $phpbb_container
	* @param \phpbb\user_loader $user_loader
	* @param \phpbb\event\dispatcher_interface $phpbb_dispatcher
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\cache\service $cache
	* @param \phpbb\language\language $language
	* @param \phpbb\user $user
	* @param string $notification_types_table
	* @param string $user_notifications_table
	*
	* @return \phpbb\notification\manager
	*/
	public function __construct($notification_types, $notification_methods, ContainerInterface $phpbb_container, \phpbb\user_loader $user_loader, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\db\driver\driver_interface $db, \phpbb\cache\service $cache, \phpbb\language\language $language, \phpbb\user $user, $notification_types_table, $user_notifications_table)
	{
		$this->notification_types = $notification_types;
		$this->notification_methods = $notification_methods;
		$this->phpbb_container = $phpbb_container;

		$this->user_loader = $user_loader;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->db = $db;
		$this->cache = $cache;
		$this->language = $language;
		$this->user = $user;

		$this->notification_types_table = $notification_types_table;
		$this->user_notifications_table = $user_notifications_table;
	}

	/**
	* Load the user's notifications for a given method
	*
	* @param string $method_name
	* @param array $options Optional options to control what notifications are loaded
	*				notification_id		Notification id to load (or array of notification ids)
	*				user_id				User id to load notifications for (Default: $user->data['user_id'])
	*				order_by			Order by (Default: notification_time)
	*				order_dir			Order direction (Default: DESC)
	* 				limit				Number of notifications to load (Default: 5)
	* 				start				Notifications offset (Default: 0)
	* 				all_unread			Load all unread notifications? If set to true, count_unread is set to true (Default: false)
	* 				count_unread		Count all unread notifications? (Default: false)
	* 				count_total			Count all notifications? (Default: false)
	* @return array Array of information based on the request with keys:
	*	'notifications'		array of notification type objects
	*	'unread_count'		number of unread notifications the user has if count_unread is true in the options
	*	'total_count'		number of notifications the user has if count_total is true in the options
	* @throws \phpbb\notification\exception when the method doesn't refer to a class extending \phpbb\notification\method\method_interface
	*/
	public function load_notifications($method_name, array $options = array())
	{
		$method = $this->get_method_class($method_name);

		if (! $method instanceof \phpbb\notification\method\method_interface)
		{
			throw new \phpbb\notification\exception($this->language->lang('NOTIFICATION_METHOD_INVALID', $method_name));
		}
		else if ($method->is_available())
		{
			return $method->load_notifications($options);
		}
		else
		{
			return array(
				'notifications'		=> array(),
				'unread_count'		=> 0,
				'total_count'		=> 0,
			);
		}
	}

	/**
	 * Mark notifications read or unread for all available methods
	 *
	 * @param bool|string|array $notification_type_name Type identifier or array of item types (only acceptable if the $data is identical for the specified types). False to mark read for all item types
	 * @param bool|int|array $item_id Item id or array of item ids. False to mark read for all item ids
	 * @param bool|int|array $user_id User id or array of user ids. False to mark read for all user ids
	 * @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	 *
	 * @deprecated since 3.2
	 */
	public function mark_notifications_read($notification_type_name, $item_id, $user_id, $time = false)
	{
		$this->mark_notifications($notification_type_name, $item_id, $user_id, $time);
	}

	/**
	* Mark notifications read or unread for all available methods
	*
	* @param bool|string|array $notification_type_name Type identifier or array of item types (only acceptable if the $data is identical for the specified types). False to mark read for all item types
	* @param bool|int|array $item_id Item id or array of item ids. False to mark read for all item ids
	* @param bool|int|array $user_id User id or array of user ids. False to mark read for all user ids
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	* @param bool $mark_read Define if the notification as to be set to True or False. (Default: True)
	*/
	public function mark_notifications($notification_type_name, $item_id, $user_id, $time = false, $mark_read = true)
	{
		if (is_array($notification_type_name))
		{
			$notification_type_id = $this->get_notification_type_ids($notification_type_name);
		}
		else if ($notification_type_name !== false)
		{
			$notification_type_id = $this->get_notification_type_id($notification_type_name);
		}
		else
		{
			$notification_type_id = false;
		}

		/** @var method\method_interface $method */
		foreach ($this->get_available_subscription_methods() as $method)
		{
			$method->mark_notifications($notification_type_id, $item_id, $user_id, $time, $mark_read);
		}
	}

	/**
	 * Mark notifications read or unread from a parent identifier for all available methods
	 *
	 * @param string|array $notification_type_name Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	 * @param bool|int|array $item_parent_id Item parent id or array of item parent ids. False to mark read for all item parent ids
	 * @param bool|int|array $user_id User id or array of user ids. False to mark read for all user ids
	 * @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	 *
	 * @deprecated since 3.2
	 */
	public function mark_notifications_read_by_parent($notification_type_name, $item_parent_id, $user_id, $time = false)
	{
		$this->mark_notifications_by_parent($notification_type_name, $item_parent_id, $user_id, $time);
	}

	/**
	* Mark notifications read or unread from a parent identifier for all available methods
	*
	* @param string|array $notification_type_name Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	* @param bool|int|array $item_parent_id Item parent id or array of item parent ids. False to mark read for all item parent ids
	* @param bool|int|array $user_id User id or array of user ids. False to mark read for all user ids
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	* @param bool $mark_read Define if the notification as to be set to True or False. (Default: True)
	*/
	public function mark_notifications_by_parent($notification_type_name, $item_parent_id, $user_id, $time = false, $mark_read = true)
	{
		if (is_array($notification_type_name))
		{
			$notification_type_id = $this->get_notification_type_ids($notification_type_name);
		}
		else
		{
			$notification_type_id = $this->get_notification_type_id($notification_type_name);
		}

		/** @var method\method_interface $method */
		foreach ($this->get_available_subscription_methods() as $method)
		{
			$method->mark_notifications_by_parent($notification_type_id, $item_parent_id, $user_id, $time, $mark_read);
		}
	}

	/**
	* Mark notifications read or unread for a given method
	*
	* @param string $method_name
	* @param int|array $notification_id Notification id or array of notification ids.
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	* @param bool $mark_read Define if the notification as to be set to True or False. (Default: True)
	*/
	public function mark_notifications_by_id($method_name, $notification_id, $time = false, $mark_read = true)
	{
		$method = $this->get_method_class($method_name);

		if ($method instanceof \phpbb\notification\method\method_interface && $method->is_available())
		{
			$method->mark_notifications_by_id($notification_id, $time, $mark_read);
		}
	}

	/**
	* Add a notification
	*
	* @param string|array $notification_type_name Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	*			Note: If you send an array of types, any user who could receive multiple notifications from this single item will only receive
	* 			a single notification. If they MUST receive multiple notifications, call this function multiple times instead of sending an array
	* @param array $data Data specific for this type that will be inserted
	* @param array $options Optional options to control what notifications are loaded
	* 			ignore_users	array of data to specify which users should not receive certain types of notifications
	* @return array Information about what users were notified and how they were notified
	*/
	public function add_notifications($notification_type_name, $data, array $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		if (is_array($notification_type_name))
		{
			$notified_users = array();
			$temp_options = $options;

			foreach ($notification_type_name as $type)
			{
				$temp_options['ignore_users'] = $options['ignore_users'] + $notified_users;
				$notified_users += $this->add_notifications($type, $data, $temp_options);
			}

			return $notified_users;
		}

		// find out which users want to receive this type of notification
		$notify_users = $this->get_item_type_class($notification_type_name)->find_users_for_notification($data, $options);

		/**
		* Allow filtering the notify_users array for a notification that is about to be sent.
		* Here, $notify_users is already filtered by f_read and the ignored list included in the options variable
		*
		* @event core.notification_manager_add_notifications
		* @var	string	notification_type_name		The forum id from where the topic belongs
		* @var	array 	data						Data specific for the notification_type_name used will be inserted
		* @var	array 	notify_users				The array of userid that are going to be notified for this notification. Set to array() to cancel.
		* @var	array 	options						The options that were used when this method was called (read only)
		*
		* @since 3.1.3-RC1
		*/
		$vars = array(
			'notification_type_name',
			'data',
			'notify_users',
			'options',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.notification_manager_add_notifications', compact($vars)));

		$this->add_notifications_for_users($notification_type_name, $data, $notify_users);

		return $notify_users;
	}

	/**
	* Add a notification for specific users
	*
	* @param string|array $notification_type_name Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	* @param array $data Data specific for this type that will be inserted
	* @param array $notify_users User list to notify
	*/
	public function add_notifications_for_users($notification_type_name, $data, $notify_users)
	{
		if (is_array($notification_type_name))
		{
			foreach ($notification_type_name as $type)
			{
				$this->add_notifications_for_users($type, $data, $notify_users);
			}

			return;
		}

		$notification_type_id = $this->get_notification_type_id($notification_type_name);

		$item_id = $this->get_item_type_class($notification_type_name)->get_item_id($data);

		$user_ids = array();
		$notification_methods = array();

		// Never send notifications to the anonymous user!
		unset($notify_users[ANONYMOUS]);

		// Make sure not to send new notifications to users who've already been notified about this item
		// This may happen when an item was added, but now new users are able to see the item
		// We remove each user which was already notified by at least one method.
		/** @var method\method_interface $method */
		foreach ($this->get_subscription_methods_instances() as $method)
		{
			$notified_users = $method->get_notified_users($notification_type_id, array('item_id' => $item_id));
			foreach ($notified_users as $user => $notifications)
			{
				unset($notify_users[$user]);
			}
		}

		if (!sizeof($notify_users))
		{
			return;
		}

		// Allow notifications to perform actions before creating the insert array (such as run a query to cache some data needed for all notifications)
		$notification = $this->get_item_type_class($notification_type_name);
		$pre_create_data = $notification->pre_create_insert_array($data, $notify_users);
		unset($notification);

		// Go through each user so we can insert a row in the DB and then notify them by their desired means
		foreach ($notify_users as $user => $methods)
		{
			$notification = $this->get_item_type_class($notification_type_name);

			$notification->user_id = (int) $user;

			// Generate the insert_array
			$notification->create_insert_array($data, $pre_create_data);

			// Users are needed to send notifications
			$user_ids = array_merge($user_ids, $notification->users_to_query());

			foreach ($methods as $method)
			{
				// setup the notification methods and add the notification to the queue
				if (!isset($notification_methods[$method]))
				{
					$notification_methods[$method] = $this->get_method_class($method);
				}

				$notification_methods[$method]->add_to_queue($notification);
			}
		}

		// We need to load all of the users to send notifications
		$this->user_loader->load_users($user_ids);

		// run the queue for each method to send notifications
		foreach ($notification_methods as $method)
		{
			$method->notify();
		}
	}

	/**
	* Update notification
	*
	* @param string|array $notification_type_name Type identifier or array of item types (only acceptable if the $data is identical for the specified types)
	* @param array $data Data specific for this type that will be updated
	* @param array $options
	*/
	public function update_notifications($notification_type_name, array $data, array $options = array())
	{
		if (is_array($notification_type_name))
		{
			foreach ($notification_type_name as $type)
			{
				$this->update_notifications($type, $data);
			}

			return;
		}

		$this->update_notification($this->get_item_type_class($notification_type_name), $data, $options);
	}

	/**
	* Update a notification
	*
	* @param \phpbb\notification\type\type_interface $notification The notification
	* @param array $data Data specific for this type that will be updated
	* @param array $options
	*/
	public function update_notification(\phpbb\notification\type\type_interface $notification, array $data, array $options = array())
	{
		if (empty($options))
		{
			$options['item_id'] = $notification->get_item_id($data);
		}

		/** @var method\method_interface $method */
		foreach ($this->get_available_subscription_methods() as $method)
		{
			$method->update_notification($notification, $data, $options);
		}
	}

	/**
	* Delete a notification
	*
	* @param string|array $notification_type_name Type identifier or array of item types (only acceptable if the $item_id is identical for the specified types)
	* @param int|array $item_id Identifier within the type (or array of ids)
	* @param mixed $parent_id Parent identifier within the type (or array of ids), used in combination with item_id if specified (Default: false; not checked)
	* @param mixed $user_id User id (Default: false; not checked)
	*/
	public function delete_notifications($notification_type_name, $item_id, $parent_id = false, $user_id = false)
	{
		if (is_array($notification_type_name))
		{
			foreach ($notification_type_name as $type)
			{
				$this->delete_notifications($type, $item_id, $parent_id, $user_id);
			}

			return;
		}

		$notification_type_id = $this->get_notification_type_id($notification_type_name);

		/** @var method\method_interface $method */
		foreach ($this->get_available_subscription_methods() as $method)
		{
			$method->delete_notifications($notification_type_id, $item_id, $parent_id, $user_id);
		}
	}

	/**
	* Get all of the subscription types
	*
	* @return array Array of item types
	*/
	public function get_subscription_types()
	{
		if ($this->subscription_types === null)
		{
			$this->subscription_types = array();

			foreach ($this->notification_types as $type_name => $data)
			{
				/** @var type\base $type */
				$type = $this->get_item_type_class($type_name);

				if ($type instanceof \phpbb\notification\type\type_interface && $type->is_available())
				{
					$options = array_merge(array(
						'id' => $type->get_type(),
						'lang' => 'NOTIFICATION_TYPE_' . strtoupper($type->get_type()),
						'group' => 'NOTIFICATION_GROUP_MISCELLANEOUS',
					), (($type::$notification_option !== false) ? $type::$notification_option : array()));

					$this->subscription_types[$options['group']][$options['id']] = $options;
				}
			}

			// Move Miscellaneous to the very last section
			if (isset($this->subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS']))
			{
				$miscellaneous = $this->subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS'];
				unset($this->subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS']);
				$this->subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS'] = $miscellaneous;
			}
		}

		return $this->subscription_types;
	}

	/**
	* Get all of the subscription methods
	*
	* @return array Array of methods
	*/
	public function get_subscription_methods()
	{
		$subscription_methods = array();

		/** @var method\method_interface $method */
		foreach ($this->get_available_subscription_methods() as $method_name => $method)
		{
			$subscription_methods[$method_name] = array(
				'id'		=> $method->get_type(),
				'lang'		=> str_replace('.', '_', strtoupper($method->get_type())),
			);
		}

		return $subscription_methods;
	}

	/**
	* Get all of the subscription methods
	*
	* @return array Array of method's instances
	*/
	private function get_subscription_methods_instances()
	{
		$subscription_methods = array();

		foreach ($this->notification_methods as $method_name => $data)
		{
			$method = $this->get_method_class($method_name);

			if ($method instanceof \phpbb\notification\method\method_interface)
			{
				$subscription_methods[$method_name] = $method;
			}
		}

		return $subscription_methods;
	}

	/**
	* Get all of the available subscription methods
	*
	* @return array Array of method's instances
	*/
	private function get_available_subscription_methods()
	{
		$subscription_methods = array();

		/** @var method\method_interface $method */
		foreach ($this->get_subscription_methods_instances() as $method_name => $method)
		{
			if ($method->is_available())
			{
				$subscription_methods[$method_name] = $method;
			}
		}

		return $subscription_methods;
	}


	/**
	* Get user's notification data
	*
	* @param int $user_id The user_id of the user to get the notifications for
	*
	* @return array User's notification
	*/
	protected function get_user_notifications($user_id)
	{
		$sql = 'SELECT method, notify, item_type
				FROM ' . $this->user_notifications_table . '
				WHERE user_id = ' . (int) $user_id . '
					AND item_id = 0';

		$result = $this->db->sql_query($sql);
		$user_notifications = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_notifications[$row['item_type']][] = $row;
		}

		$this->db->sql_freeresult($result);

		return $user_notifications;
	}

	/**
	* Get global subscriptions (item_id = 0)
	*
	* @param bool|int $user_id The user_id to add the subscription for (bool false for current user)
	*
	* @return array Subscriptions
	*/
	public function get_global_subscriptions($user_id = false)
	{
		$user_id = $user_id ?: $this->user->data['user_id'];

		$subscriptions = array();
		$default_methods = $this->get_default_methods();

		$user_notifications = $this->get_user_notifications($user_id);

		foreach ($this->get_subscription_types() as $types)
		{
			foreach ($types as $id => $type)
			{
				$type_subscriptions = $default_methods;
				if (!empty($user_notifications[$id]))
				{
					foreach ($user_notifications[$id] as $user_notification)
					{
						$key = array_search($user_notification['method'], $type_subscriptions, true);
						if (!$user_notification['notify'])
						{
							if ($key !== false)
							{
								unset($type_subscriptions[$key]);
							}

							continue;
						}
						else if ($key === false)
						{
							$type_subscriptions[] = $user_notification['method'];
						}
					}
				}

				if (!empty($type_subscriptions))
				{
					$subscriptions[$id] = $type_subscriptions;
				}
			}
		}

		return $subscriptions;
	}

	/**
	* Add a subscription
	*
	* @param string $item_type Type identifier of the subscription
	* @param int $item_id The id of the item
	* @param string $method The method of the notification e.g. 'board', 'email', or 'jabber'
	*                       (if null a subscription will be added for all the defaults methods)
	* @param bool|int $user_id The user_id to add the subscription for (bool false for current user)
	*/
	public function add_subscription($item_type, $item_id = 0, $method = null, $user_id = false)
	{
		if ($method === null)
		{
			foreach ($this->get_default_methods() as $method_name)
			{
				$this->add_subscription($item_type, $item_id, $method_name, $user_id);
			}

			return;
		}

		$user_id = ($user_id === false) ? $this->user->data['user_id'] : $user_id;

		$sql = 'SELECT notify
			FROM ' . $this->user_notifications_table . "
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND item_id = " . (int) $item_id . '
				AND user_id = ' .(int) $user_id . "
				AND method = '" . $this->db->sql_escape($method) . "'";
		$this->db->sql_query($sql);
		$current = $this->db->sql_fetchfield('notify');
		$this->db->sql_freeresult();

		if ($current === false)
		{
			$sql = 'INSERT INTO ' . $this->user_notifications_table . ' ' .
				$this->db->sql_build_array('INSERT', array(
					'item_type'		=> $item_type,
					'item_id'		=> (int) $item_id,
					'user_id'		=> (int) $user_id,
					'method'		=> $method,
					'notify'		=> 1,
				));
			$this->db->sql_query($sql);
		}
		else if (!$current)
		{
			$sql = 'UPDATE ' . $this->user_notifications_table . "
				SET notify = 1
				WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
					AND item_id = " . (int) $item_id . '
					AND user_id = ' .(int) $user_id . "
					AND method = '" . $this->db->sql_escape($method) . "'";
			$this->db->sql_query($sql);
		}
	}

	/**
	* Delete a subscription
	*
	* @param string $item_type Type identifier of the subscription
	* @param int $item_id The id of the item
	* @param string $method The method of the notification e.g. 'board', 'email', or 'jabber'
	* @param bool|int $user_id The user_id to add the subscription for (bool false for current user)
	*/
	public function delete_subscription($item_type, $item_id = 0, $method = null, $user_id = false)
	{
		if ($method === null)
		{
			foreach ($this->get_default_methods() as $method_name)
			{
				$this->delete_subscription($item_type, $item_id, $method_name, $user_id);
			}

			return;
		}

		$user_id = $user_id ?: $this->user->data['user_id'];

		$sql = 'UPDATE ' . $this->user_notifications_table . "
			SET notify = 0
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND item_id = " . (int) $item_id . '
				AND user_id = ' .(int) $user_id . "
				AND method = '" . $this->db->sql_escape($method) . "'";
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$sql = 'INSERT INTO ' . $this->user_notifications_table . ' ' .
				$this->db->sql_build_array('INSERT', array(
					'item_type'		=> $item_type,
					'item_id'		=> (int) $item_id,
					'user_id'		=> (int) $user_id,
					'method'		=> $method,
					'notify'		=> 0,
				));
			$this->db->sql_query($sql);
		}
	}

	/**
	* Disable all notifications of a certain type
	*
	* This should be called when an extension which has notification types
	* is disabled so that all those notifications are hidden and do not
	* cause errors
	*
	* @param string $notification_type_name Type identifier of the subscription
	*/
	public function disable_notifications($notification_type_name)
	{
		$sql = 'UPDATE ' . $this->notification_types_table . "
			SET notification_type_enabled = 0
			WHERE notification_type_name = '" . $this->db->sql_escape($notification_type_name) . "'";
		$this->db->sql_query($sql);
	}

	/**
	* Purge all notifications of a certain type
	*
	* This should be called when an extension which has notification types
	* is purged so that all those notifications are removed
	*
	* @param string $notification_type_name Type identifier of the subscription
	*/
	public function purge_notifications($notification_type_name)
	{
		// If a notification is never used, its type will not be added to the database
		// nor its id cached. If this method is called by an extension during the
		// purge step, and that extension never used its notifications,
		// get_notification_type_id() will throw an exception. However,
		// because no notification type was added to the database,
		// there is nothing to delete, so we can silently drop the exception.
		try
		{
			$notification_type_id = $this->get_notification_type_id($notification_type_name);

			/** @var method\method_interface $method */
			foreach ($this->get_available_subscription_methods() as $method)
			{
				$method->purge_notifications($notification_type_id);
			}

		}
		catch (\phpbb\notification\exception $e)
		{
			// Continue
		}
	}

	/**
	* Enable all notifications of a certain type
	*
	* This should be called when an extension which has notification types
	* that was disabled is re-enabled so that all those notifications that
	* were hidden are shown again
	*
	* @param string $notification_type_name Type identifier of the subscription
	*/
	public function enable_notifications($notification_type_name)
	{
		$sql = 'UPDATE ' . $this->notification_types_table . "
			SET notification_type_enabled = 1
			WHERE notification_type_name = '" . $this->db->sql_escape($notification_type_name) . "'";
		$this->db->sql_query($sql);
	}

	/**
	* Delete all notifications older than a certain time
	*
	* @param int $timestamp Unix timestamp to delete all notifications that were created before
	* @param bool $only_read True (default) to only prune read notifications
	*/
	public function prune_notifications($timestamp, $only_read = true)
	{
		/** @var method\method_interface $method */
		foreach ($this->get_available_subscription_methods() as $method)
		{
			$method->prune_notifications($timestamp, $only_read);
		}
	}

	/**
	 * Helper to get the list of methods enabled by default
	 *
	 * @return method\method_interface[]
	 */
	public function get_default_methods()
	{
		$default_methods = array();

		foreach ($this->notification_methods as $method)
		{
			if ($method->is_enabled_by_default() && $method->is_available())
			{
				$default_methods[] = $method->get_type();
			}
		}

		return $default_methods;
	}

	/**
	 * Helper to get the notifications item type class and set it up
	 *
	 * @param string $notification_type_name
	 * @param array  $data
	 * @return type\type_interface
	 */
	public function get_item_type_class($notification_type_name, $data = array())
	{
		$item = $this->load_object($notification_type_name);

		$item->set_initial_data($data);

		return $item;
	}

	/**
	 * Helper to get the notifications method class and set it up
	 *
	 * @param string $method_name
	 * @return method\method_interface
	 */
	public function get_method_class($method_name)
	{
		return $this->load_object($method_name);
	}

	/**
	 * Helper to load objects (notification types/methods)
	 *
	 * @param string $object_name
	 * @return method\method_interface|type\type_interface
	 */
	protected function load_object($object_name)
	{
		$object = $this->phpbb_container->get($object_name);

		if (method_exists($object, 'set_notification_manager'))
		{
			$object->set_notification_manager($this);
		}

		return $object;
	}

	/**
	* Get the notification type id from the name
	*
	* @param string $notification_type_name The name
	* @return int the notification_type_id
	* @throws \phpbb\notification\exception
	*/
	public function get_notification_type_id($notification_type_name)
	{
		$notification_type_ids = $this->cache->get('notification_type_ids');

		$this->db->sql_transaction('begin');

		if ($notification_type_ids === false)
		{
			$notification_type_ids = array();

			$sql = 'SELECT notification_type_id, notification_type_name
				FROM ' . $this->notification_types_table;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$notification_type_ids[$row['notification_type_name']] = (int) $row['notification_type_id'];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('notification_type_ids', $notification_type_ids);
		}

		if (!isset($notification_type_ids[$notification_type_name]))
		{
			if (!isset($this->notification_types[$notification_type_name]) && !isset($this->notification_types['notification.type.' . $notification_type_name]))
			{
				$this->db->sql_transaction('rollback');

				throw new \phpbb\notification\exception('NOTIFICATION_TYPE_NOT_EXIST', array($notification_type_name));
			}

			$sql = 'INSERT INTO ' . $this->notification_types_table . ' ' . $this->db->sql_build_array('INSERT', array(
				'notification_type_name'		=> $notification_type_name,
				'notification_type_enabled'		=> 1,
			));
			$this->db->sql_query($sql);

			$notification_type_ids[$notification_type_name] = (int) $this->db->sql_nextid();

			$this->cache->put('notification_type_ids', $notification_type_ids);
		}

		$this->db->sql_transaction('commit');

		return $notification_type_ids[$notification_type_name];
	}

	/**
	* Get notification type ids (as an array)
	*
	* @param string|array $notification_type_names Notification type names
	* @return array Array of integers
	*/
	public function get_notification_type_ids($notification_type_names)
	{
		if (!is_array($notification_type_names))
		{
			$notification_type_names = array($notification_type_names);
		}

		$notification_type_ids = array();

		foreach ($notification_type_names as $name)
		{
			$notification_type_ids[$name] = $this->get_notification_type_id($name);
		}

		return $notification_type_ids;
	}

	/**
	* Find the users which are already notified
	*
	* @param bool|string|array $notification_type_name Type identifier or array of item types (only acceptable if the $data is identical for the specified types). False to retrieve all item types
	* @param array $options
	* @return array The list of the notified users
	*/
	public function get_notified_users($notification_type_name, array $options)
	{
		$notification_type_id = $this->get_notification_type_id($notification_type_name);

		$notified_users = array();

		/** @var method\method_interface $method */
		foreach ($this->get_available_subscription_methods() as $method)
		{
			$notified_users = $notified_users + $method->get_notified_users($notification_type_id, $options);
		}

		return $notified_users;
	}
}
