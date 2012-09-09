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
	*				user_id		User id to load notifications for (Default: $user->data['user_id'])
	*				order_by	Order by (Default: time)
	*				order_dir	Order direction (Default: DESC)
	* 				limit		Number of notifications to load (Default: 5)
	* 				start		Notifications offset (Default: 0)
	*/
	public function load_notifications($options = array())
	{
		$user = $this->phpbb_container->get('user');

		// Merge default options
		$options = array_merge(array(
			'user_id'		=> $user->data['user_id'],
			'limit'			=> 5,
			'start'			=> 0,
			'order_by'		=> 'time',
			'order_dir'		=> 'DESC',
		), $options);

		$notifications = $user_ids = array();

		$sql = 'SELECT * FROM ' . NOTIFICATIONS_TABLE . '
			WHERE user_id = ' . (int) $options['user_id'] . '
				ORDER BY ' . $this->db->sql_escape($options['order_by']) . ' ' . $this->db->sql_escape($options['order_dir']);
		$result = $this->db->sql_query_limit($sql, $options['limit'], $options['start']);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$item_type_class_name = $this->get_item_type_class_name($row['item_type'], true);

			$notification = new $item_type_class_name($this->phpbb_container, $row);

			$user_ids = array_merge($user_ids, $notification->users_to_query());

			$notifications[] = $notification;
		}
		$this->db->sql_freeresult($result);

		$this->load_users($user_ids);

		return $notifications;
	}

	/**
	* Add a notification
	*
	* @param string $item_type Type identifier
	* @param int $item_id Identifier within the type
	* @param array $data Data specific for this type that will be inserted
	*/
	public function add_notifications($item_type, $data)
	{
		$item_type_class_name = $this->get_item_type_class_name($item_type);

		$item_id = $item_type_class_name::get_item_id($data);

		// Update any existing notifications for this item
		$this->update_notifications($item_type, $item_id, $data);

		$notify_users = $user_ids = array();
		$notification_objects = $notification_methods = array();
		$new_rows = array();

		// find out which users want to receive this type of notification
		$notify_users = $item_type_class_name::find_users_for_notification($this->phpbb_container, $data);

		// Never send notifications to the anonymous user or the current user!
		$notify_users = array_diff($notify_users, array(ANONYMOUS, $this->phpbb_container->get('user')->data['user_id']));

		// Make sure not to send new notifications to users who've already been notified about this item
		// This may happen when an item was added, but now new users are able to see the item
		$sql = 'SELECT user_id FROM ' . NOTIFICATIONS_TABLE . "
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
				if ($method)
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
			$method->run_queue();
		}
	}

	/**
	* Update a notification
	*
	* @param string $item_type Type identifier
	* @param array $data Data specific for this type that will be updated
	*/
	public function update_notifications($item_type, $data)
	{
		$item_type_class_name = $this->get_item_type_class_name($item_type);

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
	* @param string $item_type Type identifier
	* @param int|array $item_id Identifier within the type (or array of ids)
	* @param array $data Data specific for this type that will be updated
	*/
	public function delete_notifications($item_type, $item_id)
	{
		$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND " . (is_array($item_id) ? $this->db->sql_in_set('item_id', $item_id) : 'item_id = ' . (int) $item_id);
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
			$sql = 'SELECT * FROM ' . USERS_TABLE . '
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
			$item_type = preg_replace('#[^a-z]#', '', $item_type);
		}

		return 'phpbb_notifications_type_' . $item_type;
	}
}
