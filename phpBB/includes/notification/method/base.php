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
* Base notifications method class
* @package notifications
*/
abstract class phpbb_notification_method_base implements phpbb_notification_method_interface
{
	protected $notification_manager, $db, $cache, $template, $extension_manager, $user, $auth, $config, $phpbb_root_path, $php_ext = null;

	/**
	* Desired notifications
	* unique by (type, type_id, user_id, method)
	* if multiple methods are desired, multiple rows will exist.
	*
	* method of "none" will over-ride any other options
	*
	* item_type
	* item_id
	* user_id
	* method
	* 	none (will never receive notifications)
	* 	standard (listed in notifications window
	* 	popup?
	* 	email
	* 	jabber
	*	sms?
	*/

	/**
	* Queue of messages to be sent
	*
	* @var array
	*/
	protected $queue = array();

	public function __construct(phpbb_notification_manager $notification_manager, dbal $db, phpbb_cache_driver_interface $cache, phpbb_template $template, phpbb_extension_manager $extension_manager, $user, phpbb_auth $auth, phpbb_config $config, $phpbb_root_path, $php_ext)
	{
		$this->notification_manager = $notification_manager;
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->extension_manager = $extension_manager;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Add a notification to the queue
	*
	* @param phpbb_notification_type_interface $notification
	*/
	public function add_to_queue(phpbb_notification_type_interface $notification)
	{
		$this->queue[] = $notification;
	}

	/**
	* Empty the queue
	*/
	protected function empty_queue()
	{
		$this->queue = array();
	}
}
