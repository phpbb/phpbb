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

	public function __construct(Symfony\Component\DependencyInjection\ContainerBuilder $phpbb_container)
	{
		$this->phpbb_container = $phpbb_container;

		// Some common things we're going to use
		$this->db = $phpbb_container->get('dbal.conn');
	}

	private function get_type_class_name(&$type, $safe = false)
	{
		if (!$safe)
		{
			$type = preg_replace('#[^a-z]#', '', $type);
		}

		return 'phpbb_notifications_type_' . $type;
	}

	/**
	* Load the user's notifications
	*
	* @param array $options Optional options to control what notifications are loaded
	*					user_id		User id to load notifications for (Default: $user->data['user_id'])
	* 					limit		Number of notifications to load (Default: 5)
	* 					start		Notifications offset (Default: 0)
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

	public function add_notifications($type, $data)
	{
		$type_class_name = $this->get_type_class_name($type);

		$notification_objects = array(); // 'user_id'	=> object
		$methods = $new_rows = array();

		// find out which users want to receive this type of notification
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_id', array(2));
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['method'] = '';

			$notification = new $type_class_name($this->phpbb_container);

			$notification->user_id = $row['user_id'];

			$new_rows[] = $notification->create_insert_array($data);

			// setup the notification methods and add the notification to the queue
			if ($row['method'])
			{
				if (!isset($methods[$row['method']]))
				{
					$method_class_name = 'phpbb_notifications_method_' . $row['method'];
					$methods[$row['method']] = new $$method_class_name();
				}

				$methods[$row['method']]->add_to_queue($notification);
			}
		}

		// insert into the db
		$this->db->sql_multi_insert(NOTIFICATIONS_TABLE, $new_rows);

		// run the queue for each method to send notifications
		foreach ($methods as $method)
		{
			$method->run_queue();
		}
	}

	public function update_notifications($type, $type_id, $data)
	{
		$type_class_name = $this->get_type_class_name($type);

		$object = new $$type_class($this->phpbb_container);
		$update = $object->update($data);

		$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $update) . "
			WHERE type = '" . $this->db->sql_escape($type) . "'
				AND type_id = " . (int) $type_id;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$object = new $type_class_name($this->phpbb_container, $row);
			$object->update($data);

			$update_rows[] = $object->getForUpdate();
		}
	}
}
