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
* Base notifications class
* @package notifications
*/
abstract class phpbb_notification_type_base implements phpbb_notification_type_interface
{
	/** @var phpbb_notification_manager */
	protected $notification_manager = null;

	/** @var phpbb_user_loader */
	protected $user_loader = null;

	/** @var phpbb_db_driver */
	protected $db = null;

	/** @var phpbb_cache_service */
	protected $cache = null;

	/** @var phpbb_template */
	protected $template = null;

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

	/** @var string */
	protected $notifications_table = null;

	/** @var string */
	protected $user_notifications_table = null;

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use its default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = false;

	/**
	* Indentification data
	* item_type			- Type of the item (translates to the notification type)
	* item_id			- ID of the item (e.g. post_id, msg_id)
	* item_parent_id	- Parent item id (ex: for topic => forum_id, for post => topic_id, etc)
	* user_id
	* notification_read
	* notification_time
	* notification_data (special serialized field that each notification type can use to store stuff)
	*
	* @var array $data Notification row from the database
	* 		This must be private, all interaction should use __get(), __set(), get_data(), set_data()
	*/
	private $data = array();

	public function __construct(phpbb_user_loader $user_loader, phpbb_db_driver $db, phpbb_cache_driver_interface $cache, $user, phpbb_auth $auth, phpbb_config $config, $phpbb_root_path, $php_ext, $notifications_table, $user_notifications_table)
	{
		$this->user_loader = $user_loader;
		$this->db = $db;
		$this->cache = $cache;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->notifications_table = $notifications_table;
		$this->user_notifications_table = $user_notifications_table;
	}

	public function set_notification_manager(phpbb_notification_manager $notification_manager)
	{
		$this->notification_manager = $notification_manager;
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

	public function __get($name)
	{
		return (!isset($this->data[$name])) ? null : $this->data[$name];
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function __toString()
	{
		return (!empty($this->data)) ? var_export($this->data, true) : $this->get_type();
	}

	/**
	* Get special data (only important for the classes that extend this)
	*
	* @param string $name Name of the variable to get
	*
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
	*/
	protected function set_data($name, $value)
	{
		$this->data['notification_data'][$name] = $value;
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $type_data Data unique to this notification type
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($type_data, $pre_create_data = array())
	{
		// Defaults
		$this->data = array_merge(array(
			'item_id'				=> static::get_item_id($type_data),
			'item_type'	   			=> $this->get_type(),
			'item_parent_id'		=> static::get_item_parent_id($type_data),

			'notification_time'		=> time(),
			'notification_read'		=> false,

			'notification_data'					=> array(),
		), $this->data);

		$data = $this->data;

		$data['notification_data'] = serialize($data['notification_data']);

		return $data;
	}

	/**
	* Function for preparing the data for update in an SQL query
	* (The service handles insertion)
	*
	* @param array $type_data Data unique to this notification type
	*
	* @return array Array of data ready to be updated in the database
	*/
	public function create_update_array($type_data)
	{
		$data = $this->create_insert_array($type_data);

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
	* @return string
	*/
	public function mark_read($return = false)
	{
		return $this->mark(false, $return);
	}

	/**
	* Mark this item unread
	*
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string
	*/
	public function mark_unread($return = false)
	{
		return $this->mark(true, $return);
	}

	/**
	* Prepare to output the notification to the template
	*/
	public function prepare_for_display()
	{
		if ($this->get_url())
		{
			$u_mark_read = append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, 'mark_notification=' . $this->notification_id);
		}
		else
		{
			$redirect = (($this->user->page['page_dir']) ? $this->user->page['page_dir'] . '/' : '') . $this->user->page['page_name'] . (($this->user->page['query_string']) ? '?' . $this->user->page['query_string'] : '');

			$u_mark_read = append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, 'mark_notification=' . $this->notification_id . '&amp;redirect=' . urlencode($redirect));
		}

		return array(
			'NOTIFICATION_ID'	=> $this->notification_id,

			'AVATAR'			=> $this->get_avatar(),

			'FORMATTED_TITLE'	=> $this->get_title(),

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
	*/
	public function get_unsubscribe_url($method = false)
	{
		return false;
	}

	/**
	* Get the user's avatar (fall back)
	*/
	public function get_avatar()
	{
		return '';
	}

	/**
	* Get the special items to load (fall back)
	*/
	public function get_load_special()
	{
		return array();
	}

	/**
	* Load the special items (fall back)
	*/
	public function load_special($data, $notifications)
	{
		return;
	}

	/**
	* Is available (fall back)
	*/
	public function is_available()
	{
		return true;
	}

	/**
	* Pre create insert array function (fall back)
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
	* @param array $user_ids User IDs to check if they want to receive notifications
	* 		(Bool False to check all users besides anonymous and bots (USER_IGNORE))
	*
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

		$rowset = $resulting_user_ids = array();

		$sql = 'SELECT user_id, method, notify
			FROM ' . $this->user_notifications_table . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids) . "
				AND item_type = '" . $this->db->sql_escape($options['item_type']) . "'
				AND item_id = " . (int) $options['item_id'];
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$resulting_user_ids[] = $row['user_id'];

			if (!$row['notify'] || (isset($options['ignore_users'][$row['user_id']]) && in_array($row['method'], $options['ignore_users'][$row['user_id']])))
			{
				continue;
			}

			if (!isset($rowset[$row['user_id']]))
			{
				$rowset[$row['user_id']] = array();
			}

			$rowset[$row['user_id']][] = $row['method'];
		}

		$this->db->sql_freeresult($result);

		foreach ($user_ids as $user_id)
		{
			if (!in_array($user_id, $resulting_user_ids) && !isset($options['ignore_users'][$user_id]))
			{
				// No rows at all for this user, default to ''
				$rowset[$user_id] = array('');
			}
		}

		return $rowset;
	}

	/**
	* Mark this item read/unread helper
	*
	* @param bool $unread Unread (True/False) (Default: False)
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string
	*/
	protected function mark($unread = true, $return = false)
	{
		$this->notification_read = (bool) !$unread;

		$where = array(
			"item_type = '" . $this->db->sql_escape($this->item_type) . "'",
			'item_id = ' . (int) $this->item_id,
			'user_id = ' . (int) $this->user_id,
		);
		$where = implode(' AND ', $where);

		if ($return)
		{
			return $where;
		}

		$sql = 'UPDATE ' . $this->notifications_table . '
			SET notification_read = ' . (int) $this->notification_read . '
			WHERE ' . $where;
		$this->db->sql_query($sql);
	}
}
