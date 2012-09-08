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
	protected $users;

	/**
	* Desired notifications
	* unique by (type, type_id, user_id, method)
	* if multiple methods are desired, multiple rows will exist.
	*
	* method of "none" will over-ride any other options
	*
	* type
	* type_id
	* user_id
	* method
	* 	none (will never receive notifications)
	* 	standard (listed in notifications window
	* 	popup?
	* 	email
	* 	jabber
	*	sms?
	*/

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
		), $options);

		$notifications = $user_ids = array();

		$sql = 'SELECT * FROM ' . NOTIFICATIONS_TABLE . '
			WHERE user_id = ' . (int) $options['user_id'];
		$result = $this->db->sql_query_limit($sql, $options['limit'], $options['start']);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$type_class_name = $this->get_type_class_name($row['type'], true);

			$notification = new $type_class_name($this->phpbb_container, $row);
			$notification->users($this->users);

			$user_ids = array_merge($user_ids, $notification->users_to_query());

			$notifications[] = $notification();
		}
		$this->db->sql_freeresult($result);

		// Load the users
		$user_ids = array_unique($user_ids);

		// @todo do not load users we already have in $this->users

		if (sizeof($user_ids))
		{
			// @todo do not select everything
			$sql = 'SELECT * FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->users[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		return $notifications;
	}

	/**
	* Add a notification
	*
	* @param string $type Type identifier
	* @param int $type_id Identifier within the type
	* @param array $data Data specific for this type that will be inserted
	*/
	public function add_notifications($type, $data)
	{
		$type_class_name = $this->get_type_class_name($type);

		$notify_users = array();
		$notification_objects = $notification_methods = array();
		$new_rows = array();

		// find out which users want to receive this type of notification
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_id', array(2));
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!isset($notify_users[$row['user_id']]))
			{
				$notify_users[$row['user_id']] = array();
			}

			$notify_users[$row['user_id']][] = '';
		}
		$this->db->sql_freeresult($result);

		// Go through each user so we can insert a row in the DB and then notify them by their desired means
		foreach ($notify_users as $user => $methods)
		{
			$notification = new $type_class_name($this->phpbb_container);

			$notification->user_id = (int) $user;

			$new_rows[] = $notification->create_insert_array($data);

			foreach ($methods as $method)
			{
				// setup the notification methods and add the notification to the queue
				if ($row['method'])
				{
					if (!isset($notification_methods[$row['method']]))
					{
						$method_class_name = 'phpbb_notifications_method_' . $row['method'];
						$notification_methods[$row['method']] = new $method_class_name();
					}

					$notification_methods[$row['method']]->add_to_queue($notification);
				}
			}
		}

		// insert into the db
		$this->db->sql_multi_insert(NOTIFICATIONS_TABLE, $new_rows);

		// run the queue for each method to send notifications
		foreach ($notification_methods as $method)
		{
			$method->run_queue();
		}
	}

	/**
	* Update a notification
	*
	* @param string $type Type identifier
	* @param int $type_id Identifier within the type
	* @param array $data Data specific for this type that will be updated
	*/
	public function update_notifications($type, $type_id, $data)
	{
		$type_class_name = $this->get_type_class_name($type);

		$notification = new $type_class_name($this->phpbb_container);
		$update_array = $notification->create_update_array($data);

		$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $update_array) . "
			WHERE item_type = '" . $this->db->sql_escape($type) . "'
				AND item_id = " . (int) $type_id;
		$this->db->sql_query($sql);
	}

	/**
	* Helper to get the notifications type class name and clean it if unsafe
	*/
	private function get_type_class_name(&$type, $safe = false)
	{
		if (!$safe)
		{
			$type = preg_replace('#[^a-z]#', '', $type);
		}

		return 'phpbb_notifications_type_' . $type;
	}
}
