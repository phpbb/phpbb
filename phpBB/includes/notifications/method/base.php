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
* Base notifications method class
* @package notifications
*/
abstract class phpbb_notification_method_base implements phpbb_notification_method_interface
{
	protected $phpbb_container;
	protected $service;
	protected $db;
	protected $user;
	protected $phpbb_root_path;
	protected $php_ext;

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

	public function __construct(ContainerBuilder $phpbb_container)
	{
		// phpBB Container
		$this->phpbb_container = $phpbb_container;

		// Service
		$this->service = $phpbb_container->get('notifications');

		// Some common things we're going to use
		$this->db = $phpbb_container->get('dbal.conn');
		$this->user = $phpbb_container->get('user');

		$this->phpbb_root_path = $phpbb_container->getParameter('core.root_path');
		$this->php_ext = $phpbb_container->getParameter('core.php_ext');
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
