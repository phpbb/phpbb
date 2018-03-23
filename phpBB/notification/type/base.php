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

namespace phpbb\notification\type;

/**
* Base notifications class
*/
abstract class base implements \phpbb\notification\type\type_interface
{
	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $user_notifications_table;

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use its default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	static public $notification_option = false;

	/**
	* The notification_type_id, set upon creation of the class
	* This is the notification_type_id from the notification_types table
	*
	* @var int
	*/
	protected $notification_type_id;

	/**
	* Identification data
	* notification_type_id	- ID of the item type (auto generated, from notification types table)
	* item_id				- ID of the item (e.g. post_id, msg_id)
	* item_parent_id		- Parent item id (ex: for topic => forum_id, for post => topic_id, etc)
	* user_id
	* notification_read
	* notification_time
	* notification_data (special serialized field that each notification type can use to store stuff)
	*
	* @var array $data Notification row from the database
	* 		This must be private, all interaction should use __get(), __set(), get_data(), set_data()
	*/
	private $data = array();

	/**
	 * Notification Type Base Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\language\language          $language
	 * @param \phpbb\user                       $user
	 * @param \phpbb\auth\auth                  $auth
	 * @param string                            $phpbb_root_path
	 * @param string                            $php_ext
	 * @param string                            $user_notifications_table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\user $user, \phpbb\auth\auth $auth, $phpbb_root_path, $php_ext, $user_notifications_table)
	{
		$this->db = $db;
		$this->language = $language;
		$this->user = $user;
		$this->auth = $auth;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->user_notifications_table = $user_notifications_table;
	}

	/**
	* Set notification manager (required)
	*
	* @param \phpbb\notification\manager $notification_manager
	*/
	public function set_notification_manager(\phpbb\notification\manager $notification_manager)
	{
		$this->notification_manager = $notification_manager;

		$this->notification_type_id = $this->notification_manager->get_notification_type_id($this->get_type());
	}

	/**
	* Set initial data from the database
	*
	* @param array $data Row directly from the database
	*/
	public function set_initial_data($data = array())
	{
		// The row from the database (unless this is a new notification we're going to add)
		$this->data = $data;
		$this->data['notification_data'] = (isset($this->data['notification_data'])) ? unserialize($this->data['notification_data']) : array();
	}

	/**
	* Magic method to get data from this notification
	*
	* @param mixed $name
	* @return mixed
	*/
	public function __get($name)
	{
		return (!isset($this->data[$name])) ? null : $this->data[$name];
	}


	/**
	* Magic method to set data on this notification
	*
	* @param mixed $name
	* @param mixed $value
	*
	* @return null
	*/
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}


	/**
	* Magic method to get a string of this notification
	*
	* Primarily for testing
	*
	* @return mixed
	*/
	public function __toString()
	{
		return (!empty($this->data)) ? var_export($this->data, true) : $this->get_type();
	}

	/**
	* Get special data (only important for the classes that extend this)
	*
	* @param string $name Name of the variable to get
	* @return mixed
	*/
	protected function get_data($name)
	{
		return ($name === false) ? $this->data['notification_data'] : ((isset($this->data['notification_data'][$name])) ? $this->data['notification_data'][$name] : null);
	}

	/**
	* Set special data (only important for the classes that extend this)
	*
	* @param string $name Name of the variable to set
	* @param mixed $value Value to set to the variable
	* @return mixed
	*/
	protected function set_data($name, $value)
	{
		$this->data['notification_data'][$name] = $value;
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($type_data, $pre_create_data = array())
	{
		// Defaults
		$this->data = array_merge(array(
			'item_id'				=> static::get_item_id($type_data),
			'notification_type_id'	=> $this->notification_type_id,
			'item_parent_id'		=> static::get_item_parent_id($type_data),

			'notification_time'		=> time(),
			'notification_read'		=> false,

			'notification_data'		=> array(),
		), $this->data);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_insert_array()
	{
		$data = $this->data;

		$data['notification_data'] = serialize($data['notification_data']);

		return $data;
	}

	/**
	* Function for preparing the data for update in an SQL query
	* (The service handles insertion)
	*
	* @param array $type_data Data unique to this notification type
	* @return array Array of data ready to be updated in the database
	*/
	public function create_update_array($type_data)
	{
		$this->create_insert_array($type_data);
		$data = $this->get_insert_array();

		// Unset data unique to each row
		unset(
			$data['notification_time'], // Also unsetting time, since it always tries to change the time to current (if you actually need to change the time, over-ride this function)
			$data['notification_id'],
			$data['notification_read'],
			$data['user_id']
		);

		return $data;
	}

	/**
	* Mark this item read
	*
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string|null If $return is False, nothing will be returned, else the sql code to update this item
	*/
	public function mark_read($return = false)
	{
		return $this->mark(false, $return);
	}

	/**
	* Mark this item unread
	*
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string|null If $return is False, nothing will be returned, else the sql code to update this item
	*/
	public function mark_unread($return = false)
	{
		return $this->mark(true, $return);
	}

	/**
	* {inheritDoc}
	*/
	public function get_redirect_url()
	{
		return $this->get_url();
	}

	/**
	* Prepare to output the notification to the template
	*
	* @return array Template variables
	*/
	public function prepare_for_display()
	{
		$mark_hash = generate_link_hash('mark_notification_read');

		if ($this->get_url())
		{
			$u_mark_read = append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, 'mark_notification=' . $this->notification_id . '&amp;hash=' . $mark_hash);
		}
		else
		{
			$redirect = (($this->user->page['page_dir']) ? $this->user->page['page_dir'] . '/' : '') . $this->user->page['page_name'] . (($this->user->page['query_string']) ? '?' . $this->user->page['query_string'] : '');

			$u_mark_read = append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, 'mark_notification=' . $this->notification_id . '&amp;hash=' . $mark_hash . '&amp;redirect=' . urlencode($redirect));
		}

		return array(
			'NOTIFICATION_ID'	=> $this->notification_id,
			'STYLING'			=> $this->get_style_class(),
			'AVATAR'			=> $this->get_avatar(),
			'FORMATTED_TITLE'	=> $this->get_title(),
			'REFERENCE'			=> $this->get_reference(),
			'FORUM'				=> $this->get_forum(),
			'REASON'			=> $this->get_reason(),
			'URL'				=> $this->get_url(),
			'TIME'	   			=> $this->user->format_date($this->notification_time),
			'UNREAD'			=> !$this->notification_read,
			'U_MARK_READ'		=> (!$this->notification_read) ? $u_mark_read : '',
		);
	}

	/**
	* -------------- Fall back functions -------------------
	*/

	/**
	* URL to unsubscribe to this notification (fall back)
	*
	* @param string|bool $method Method name to unsubscribe from (email|jabber|etc), False to unsubscribe from all notifications for this item
	* @return false
	*/
	public function get_unsubscribe_url($method = false)
	{
		return false;
	}

	/**
	* Get the CSS style class of the notification (fall back)
	*
	* @return string
	*/
	public function get_style_class()
	{
		return '';
	}

	/**
	* Get the user's avatar (fall back)
	*
	* @return string
	*/
	public function get_avatar()
	{
		return '';
	}

	/**
	* Get the reference of the notifcation (fall back)
	*
	* @return string
	*/
	public function get_reference()
	{
		return '';
	}

	/**
	* Get the forum of the notification reference (fall back)
	*
	* @return string
	*/
	public function get_forum()
	{
		return '';
	}

	/**
	* Get the reason for the notifcation (fall back)
	*
	* @return string
	*/
	public function get_reason()
	{
		return '';
	}

	/**
	* Get the special items to load (fall back)
	*
	* @return array
	*/
	public function get_load_special()
	{
		return array();
	}

	/**
	 * Load the special items (fall back)
	 *
	 * @param array $data
	 * @param array $notifications
	 */
	public function load_special($data, $notifications)
	{
		return;
	}

	/**
	* Is available (fall back)
	*
	* @return bool
	*/
	public function is_available()
	{
		return true;
	}

	/**
	 * Pre create insert array function (fall back)
	 *
	 * @param array $type_data
	 * @param array $notify_users
	 * @return array
	 */
	public function pre_create_insert_array($type_data, $notify_users)
	{
		return array();
	}

	/**
	* -------------- Helper functions -------------------
	*/

	/**
	 * Find the users who want to receive notifications (helper)
	 *
	 * @param array|bool $user_ids User IDs to check if they want to receive notifications
	 *                             (Bool False to check all users besides anonymous and bots (USER_IGNORE))
	 * @param array      $options
	 * @return array
	 */
	protected function check_user_notification_options($user_ids = false, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
			'item_type'			=> $this->get_type(),
			'item_id'			=> 0, // Global by default
		), $options);

		if ($user_ids === false)
		{
			$user_ids = array();

			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE user_id <> ' . ANONYMOUS . '
					AND user_type <> ' . USER_IGNORE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_ids[] = $row['user_id'];
			}
			$this->db->sql_freeresult($result);
		}

		if (empty($user_ids))
		{
			return array();
		}

		$rowset = $output = array();

		$sql = 'SELECT user_id, method, notify
			FROM ' . $this->user_notifications_table . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids) . "
				AND item_type = '" . $this->db->sql_escape($options['item_type']) . "'
				AND item_id = " . (int) $options['item_id'];
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (isset($options['ignore_users'][$row['user_id']]) && in_array($row['method'], $options['ignore_users'][$row['user_id']]))
			{
				continue;
			}

			if (!isset($rowset[$row['user_id']]))
			{
				$rowset[$row['user_id']] = array();
			}
			$rowset[$row['user_id']][$row['method']] = $row['notify'];

			if (!isset($output[$row['user_id']]))
			{
				$output[$row['user_id']] = array();
			}
			if ($row['notify'])
			{
				$output[$row['user_id']][] = $row['method'];
			}
		}

		$this->db->sql_freeresult($result);

		$default_methods = $this->notification_manager->get_default_methods();

		foreach ($user_ids as $user_id)
		{
			if (isset($options['ignore_users'][$user_id]))
			{
				continue;
			}
			if (!array_key_exists($user_id, $rowset))
			{
				// No rows at all for this user, use the default methods
				$output[$user_id] = $default_methods;
			}
			else
			{
				foreach ($default_methods as $default_method)
				{
					if (!array_key_exists($default_method, $rowset[$user_id]))
					{
						// No user preference for this type recorded, but it should be enabled by default.
						$output[$user_id][] = $default_method;
					}
				}
			}
		}

		return $output;
	}

	/**
	* Mark this item read/unread helper
	*
	* @param bool $unread Unread (True/False) (Default: False)
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string|null If $return is False, nothing will be returned, else the sql code to update this item
	*/
	protected function mark($unread = true, $return = false)
	{
		$this->notification_read = (bool) !$unread;

		if ($return)
		{
			$where = array(
				'notification_type_id = ' . (int) $this->notification_type_id,
				'item_id = ' . (int) $this->item_id,
				'user_id = ' . (int) $this->user_id,
			);

			$where = implode(' AND ', $where);
			return $where;
		}
		else
		{
			$this->notification_manager->mark_notifications($this->get_type(), (int) $this->item_id, (int) $this->user_id, false, $this->notification_read);
		}

		return null;
	}

	/**
	 * Get a list of users that are authorised to receive notifications
	 *
	 * @param array $users Array of users that have subscribed to a notification
	 * @param int $forum_id Forum ID of the forum
	 * @param array $options Array of notification options
	 * @param bool $sort Whether the users array should be sorted. Default: false
	 * @return array Array of users that are authorised recipients
	 */
	protected function get_authorised_recipients($users, $forum_id, $options, $sort = false)
	{
		if (empty($users))
		{
			return array();
		}

		$users = array_unique($users);

		if ($sort)
		{
			sort($users);
		}

		$auth_read = $this->auth->acl_get_list($users, 'f_read', $forum_id);

		if (empty($auth_read))
		{
			return array();
		}

		return $this->check_user_notification_options($auth_read[$forum_id]['f_read'], $options);
	}
}
