<?php
/**
*
* @package notifications
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	/** @var string */
	protected $notification_types_table;

	/** @var string */
	protected $notifications_table;

	/** @var string */
	protected $user_notifications_table;

	/**
	* Notification Method Board Constructor
	*
	* @param \phpbb\user_loader $user_loader
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\cache\driver\driver_interface $cache
	* @param \phpbb\user $user
	* @param \phpbb\auth\auth $auth
	* @param \phpbb\config\config $config
	* @param string $phpbb_root_path
	* @param string $php_ext
	* @param string $notification_types_table
	* @param string $notifications_table
	* @param string $user_notifications_table
	* @return \phpbb\notification\method\board
	*/
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, $phpbb_root_path, $php_ext, $notification_types_table, $notifications_table, $user_notifications_table)
	{
		$this->user_loader = $user_loader;
		$this->db = $db;
		$this->cache = $cache;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->notification_types_table = $notification_types_table;
		$this->notifications_table = $notifications_table;
		$this->user_notifications_table = $user_notifications_table;
	}

	/**
	* Add a notification to the queue
	*
	* @param \phpbb\notification\type\type_interface $notification
	*/
	public function add_to_queue(\phpbb\notification\type\type_interface $notification)
	{
		$this->queue[] = $notification;
	}

	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'board';
	}

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available()
	{
		return $this->config['allow_board_notifications'];
	}

	/**
	* Parse the queue and notify the users
	*/
	public function notify()
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->notifications_table);

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
	* Is the method enable by default?
	*
	* @return bool
	*/
	public function is_enabled_by_default()
	{
		return true;
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
}
