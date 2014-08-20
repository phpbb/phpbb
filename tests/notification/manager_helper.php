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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Notifications service class
*/
class phpbb_notification_manager_helper extends \phpbb\notification\manager
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
		$item_parts = explode('.', $item_type);
		$item_type = 'phpbb\notification\type\\' . array_pop($item_parts);

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
		$method_name = 'phpbb\notification\method\\' . $method_name;

		$method = new $method_name($this->user_loader, $this->db, $this->cache->get_driver(), $this->user, $this->auth, $this->config, $this->phpbb_root_path, $this->php_ext, $this->notification_types_table, $this->notifications_table, $this->user_notifications_table);

		$method->set_notification_manager($this);

		return $method;
	}
}
