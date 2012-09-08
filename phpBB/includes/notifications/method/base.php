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
abstract class phpbb_notifications_method_base implements phpbb_notifications_method_interface
{
	protected $phpbb_container;
	protected $db;
	protected $user;

	/**
	* Queue of messages to be sent
	*
	* @var array
	*/
	protected $queue = array();

	public function __construct(ContainerBuilder $phpbb_container, $data = array())
	{
		// phpBB Container
		$this->phpbb_container = $phpbb_container;

		// Some common things we're going to use
		$this->db = $phpbb_container->get('dbal.conn');
		$this->user = $phpbb_container->get('user');
	}

	/**
	* Add a notification to the queue
	*
	* @param phpbb_notifications_type_interface $notification
	*/
	public function add_to_queue(phpbb_notifications_type_interface $notification)
	{
		$this->queue[] = $notification;
	}

	/**
	* Basic run queue function.
	* Child methods should override this function if there are more efficient methods to mass-notification
	*/
	public function run_queue()
	{
		foreach ($this->queue as $notification)
		{
			$this->notify($notification);
		}

		// Empty queue
		$this->queue = array();
	}
}
