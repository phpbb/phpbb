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
	/** @var phpbb_notification_manager */
	protected $notification_manager = null;

	/** @var phpbb_user_loader */
	protected $user_loader = null;

	/** @var dbal */
	protected $db = null;

	/** @var phpbb_cache_service */
	protected $cache = null;

	/** @var phpbb_template */
	protected $template = null;

	/** @var phpbb_extension_manager */
	protected $extension_manager = null;

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

	/**
	* Queue of messages to be sent
	*
	* @var array
	*/
	protected $queue = array();

	public function __construct(phpbb_user_loader $user_loader, dbal $db, phpbb_cache_driver_interface $cache, $user, phpbb_auth $auth, phpbb_config $config, $phpbb_root_path, $php_ext)
	{
		$this->user_loader = $user_loader;
		$this->db = $db;
		$this->cache = $cache;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function set_notification_manager(phpbb_notification_manager $notification_manager)
	{
		$this->notification_manager = $notification_manager;
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
