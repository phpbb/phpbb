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
	* Indentification data
	* item_type
	* item_id
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
	* Output the notification to the template
	*
	* @param array $options Array of options
	* 				template_block		Template block name to output to (Default: notifications)
	*/
	public function display($options = array())
	{
		$template = $this->phpbb_container->get('template');
		$user = $this->phpbb_container->get('user');

		// Merge default options
		$options = array_merge(array(
			'template_block'		=> 'notifications',
		), $options);

		$template->assign_block_vars($options['template_block'], array(
			'TITLE'		=> $this->get_formatted_title(),
			'URL'		=> $this->get_url(),
			'TIME'		=> $user->format_date($this->time),

			'UNREAD'	=> $this->unread,
		));
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
		$data = array_merge(array(
			'item_type'		=> $this->get_item_type(),
			'time'			=> time(),
			'unread'		=> true,

			'data'			=> array(),
		), $this->data);

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
	* Find the users who want to receive notifications (helper)
	*
	* @param ContainerBuilder $phpbb_container
	* @param array $item_id The item_id to search for
	*
	* @return array
	*/
	protected static function _find_users_for_notification(ContainerBuilder $phpbb_container, $item_id)
	{
		$db = $phpbb_container->get('dbal.conn');

		$rowset = array();

		$sql = 'SELECT * FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . static::get_item_type() . "'
				AND item_id = " . (int) $item_id;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
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
	* Get the formatted title of this notification (fall-back)
	*
	* @return string
	*/
	public function get_formatted_title()
	{
		return $this->get_title();
	}

	/**
	* URL to unsubscribe to this notification
	*
	* @param string|bool $method Method name to unsubscribe from (email|jabber|etc), False to unsubscribe from all notifications for this item
	*/
	public function get_unsubscribe_url($method = false)
	{
		return false;
	}
}
