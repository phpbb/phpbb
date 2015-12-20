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

namespace phpbb\notification\method;

/**
* Base notifications method class
*/
abstract class base implements \phpbb\notification\method\method_interface
{
	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/**
	* Queue of messages to be sent
	*
	* @var array
	*/
	protected $queue = array();

	/**
	* Set notification manager (required)
	*
	* @param \phpbb\notification\manager $notification_manager
	*/
	public function set_notification_manager(\phpbb\notification\manager $notification_manager)
	{
		$this->notification_manager = $notification_manager;
	}

	/**
	* Is the method enable by default?
	*
	* @return bool
	*/
	public function is_enabled_by_default()
	{
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_notified_users($notification_type_id, array $options)
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function load_notifications(array $options = array())
	{
		return array(
			'notifications'		=> array(),
			'unread_count'		=> 0,
			'total_count'		=> 0,
		);
	}

	/**
	* Add a notification to the queue
	*
	* @param \phpbb\notification\type\type_interface $notification
	*/
	public function add_to_queue(\phpbb\notification\type\type_interface $notification)
	{
		$this->queue[] = $notification;
	}

	/**
	* {@inheritdoc}
	*/
	public function update_notification($notification, array $data, array $options)
	{
	}

	/**
	* {@inheritdoc
	*/
	public function mark_notifications($notification_type_id, $item_id, $user_id, $time = false, $mark_read = true)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications_by_parent($notification_type_id, $item_parent_id, $user_id, $time = false, $mark_read = true)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function mark_notifications_by_id($notification_id, $time = false, $mark_read = true)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function delete_notifications($notification_type_id, $item_id, $parent_id = false, $user_id = false)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function prune_notifications($timestamp, $only_read = true)
	{
	}

	/**
	* {@inheritdoc}
	*/
	public function purge_notifications($notification_type_id)
	{
	}

	/**
	* Empty the queue
	*/
	protected function empty_queue()
	{
		$this->queue = array();
	}
}
