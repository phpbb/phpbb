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
class phpbb_notification_manager_helper extends phpbb_notification_manager
{
	public function set_var($name, $value)
	{
		$this->$name = $value;
	}

	// Extra dependencies for get_*_class functions
	protected $auth = null;
	protected $config = null;
	public function setDependencies($auth, $config)
	{
		$this->auth = $auth;
		$this->config = $config;
	}

	/**
	* Helper to get the notifications item type class and set it up
	*/
	public function get_item_type_class($item_type, $data = array())
	{
		$item_type = 'phpbb_notification_type_' . $item_type;

		$item = new $item_type($this->user_loader, $this->db, $this->cache->get_driver(), $this->user, $this->auth, $this->config, $this->phpbb_root_path, $this->php_ext, $this->notification_types_table, $this->notifications_table, $this->user_notifications_table);

		$item->set_notification_manager($this);

		$item->set_initial_data($data);

		return $item;
	}

	/**
	* Helper to get the notifications method class and set it up
	*/
	public function get_method_class($method_name)
	{
		$method_name = 'phpbb_notification_method_' . $method_name;

		$method = new $method_name($this->user_loader, $this->db, $this->cache->get_driver(), $this->user, $this->auth, $this->config, $this->phpbb_root_path, $this->php_ext, $this->notification_types_table, $this->notifications_table, $this->user_notifications_table);

		$method->set_notification_manager($this);

		return $method;
	}
}
