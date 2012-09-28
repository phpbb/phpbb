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
* Base notifications class
* @package notifications
*/
abstract class phpbb_notifications_type_base implements phpbb_notifications_type_interface
{
	protected $phpbb_container;
	protected $service;
	protected $db;
	protected $phpbb_root_path;
	protected $php_ext;

	/**
	* Array of user data containing information needed to output the notifications to the template
	*
	* @var array
	*/
	protected $users = array();

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id' and 'lang')
	*/
	public static $notification_option = false;

	/**
	* Indentification data
	* item_type
	* item_id
	* item_parent_id // Parent item id (ex: for topic => forum_id, for post => topic_id, etc)
	* user_id
	* unread
	*
	* time
	* data (special serialized field that each notification type can use to store stuff)
	*
	* @var array $data Notification row from the database
	* 		This must be private, all interaction should use __get(), __set(), get_data(), set_data()
	*/
	private $data = array();

	public function __construct(ContainerBuilder $phpbb_container, $data = array())
	{
		// phpBB Container
		$this->phpbb_container = $phpbb_container;

		// Service
		$this->service = $phpbb_container->get('notifications');

		// Some common things we're going to use
		$this->db = $phpbb_container->get('dbal.conn');

		$this->phpbb_root_path = $phpbb_container->getParameter('core.root_path');
		$this->php_ext = $phpbb_container->getParameter('core.php_ext');

		// The row from the database (unless this is a new notification we're going to add)
		$this->data = $data;
		$this->data['data'] = (isset($this->data['data'])) ? unserialize($this->data['data']) : array();
	}

	public function __get($name)
	{
		return $this->data[$name];
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
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
		return (isset($this->data['data'][$name])) ? $this->data['data'][$name] : null;
	}

	/**
	* Set special data (only important for the classes that extend this)
	*
	* @param string $name Name of the variable to set
	* @param mixed $value Value to set to the variable
	*/
	protected function set_data($name, $value)
	{
		$this->data['data'][$name] = $value;
	}

	/**
	* Prepare to output the notification to the template
	*/
	public function prepare_for_display()
	{
		$user = $this->phpbb_container->get('user');

		return array(
			'AVATAR'			=> $this->get_avatar(),

			'FORMATTED_TITLE'	=> $this->get_title(),

			'URL'				=> $this->get_url(),
			'TIME'	   			=> $user->format_date($this->time),

			'UNREAD'			=> $this->unread,

			'U_MARK_READ'		=> append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, 'mark_notification[]=' . $this->notification_id),
		);
	}

	/**
	* Mark this item read
	*
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string
	*/
	public function mark_read($return = true)
	{
		return $this->mark(false, $return);
	}

	/**
	* Mark this item unread
	*
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string
	*/
	public function mark_unread($return = true)
	{
		return $this->mark(true, $return);
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $type_data Data unique to this notification type
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($type_data)
	{
		// Defaults
		$this->data = array_merge(array(
			'item_id'				=> static::get_item_id($type_data),
			'item_type'	   			=> $this->get_item_type(),
			'item_parent_id'		=> static::get_item_parent_id($type_data),

			'time'					=> time(),
			'unread'				=> true,

			'data'					=> array(),
		), $this->data);

		$data = $this->data;

		$data['data'] = serialize($data['data']);

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
			$data['notification_id'],
			$data['unread'],
			$data['user_id']
		);

		return $data;
	}

	/**
	* -------------- Fall back functions -------------------
	*/

	/**
	* URL to unsubscribe to this notification (fall-back)
	*
	* @param string|bool $method Method name to unsubscribe from (email|jabber|etc), False to unsubscribe from all notifications for this item
	*/
	public function get_unsubscribe_url($method = false)
	{
		return false;
	}

	/**
	* Get the user's avatar (fall-back)
	*/
	public function get_avatar()
	{
		return '';
	}

	/**
	* Get the special items to load (fall-back)
	*/
	public function get_load_special()
	{
		return array();
	}

	/**
	* Load the special items (fall-back)
	*/
	public static function load_special(ContainerBuilder $phpbb_container, $data, $notifications)
	{
		return;
	}

	/**
	* Is available (fall-back)
	*/
	public static function is_available(ContainerBuilder $phpbb_container)
	{
		return true;
	}

	/**
	* -------------- Helper functions -------------------
	*/

	/**
	* Find the users who want to receive notifications (helper)
	*
	* @param ContainerBuilder $phpbb_container
	* @param array $item_id The item_id to search for
	*
	* @return array
	*/
	protected static function _find_users_for_notification(ContainerBuilder $phpbb_container, $item_id, $options)
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		$db = $phpbb_container->get('dbal.conn');

		$rowset = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . static::get_item_type() . "'
				AND item_id = " . (int) $item_id;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (isset($options['ignore_users'][$row['user_id']]) && in_array($row['method'], $options['ignore_users'][$row['user_id']]))
			{
				continue;
			}

			if (!isset($rowset[$row['user_id']]))
			{
				$rowset[$row['user_id']] = array();
			}

			$rowset[$row['user_id']][] = $row['method'];
		}
		$db->sql_freeresult($result);

		return $rowset;
	}

	/**
	* Get avatar helper
	*
	* @param int $user_id
	* @return string
	*/
	protected function _get_avatar($user_id)
	{
		$user = $this->service->get_user($user_id);

		if (!function_exists('get_user_avatar'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}

		return get_user_avatar($user['user_avatar'], $user['user_avatar_type'], $user['user_avatar_width'], $user['user_avatar_height'], $user['username'], false, 'notifications-avatar');
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
		$where = array(
			'item_type = ' . $this->db->sql_escape($this->item_type),
			'item_id = ' . (int) $this->item_id,
			'user_id = ' . (int) $this->user_id,
		);
		$where = implode(' AND ' . $where);

		if ($return)
		{
			return $where;
		}

		$sql = 'UPDATE ' . NOTIFICATIONS_TABLE . '
			SET unread = ' . (bool) $unread . '
			WHERE ' . $where;
		$this->db->sql_query($sql);
	}
}
