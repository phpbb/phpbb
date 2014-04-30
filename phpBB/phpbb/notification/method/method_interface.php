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
* Base notifications method interface
*/
interface method_interface
{
	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type();

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available();

	/**
	* Add a notification to the queue
	*
	* @param \phpbb\notification\type\type_interface $notification
	*/
	public function add_to_queue(\phpbb\notification\type\type_interface $notification);

	/**
	* Parse the queue and notify the users
	*/
	public function notify();

	/**
	* Is the method enable by default?
	*
	* @return bool
	*/
	public function is_enabled_by_default();

	/**
	* Load the user's notifications
	*
	* @param array $options Optional options to control what notifications are loaded
	*				notification_id		Notification id to load (or array of notification ids)
	*				user_id				User id to load notifications for (Default: $user->data['user_id'])
	*				order_by			Order by (Default: notification_time)
	*				order_dir			Order direction (Default: DESC)
	* 				limit				Number of notifications to load (Default: 5)
	* 				start				Notifications offset (Default: 0)
	* 				all_unread			Load all unread notifications? If set to true, count_unread is set to true (Default: false)
	* 				count_unread		Count all unread notifications? (Default: false)
	* 				count_total			Count all notifications? (Default: false)
	* @return array Array of information based on the request with keys:
	*	'notifications'		array of notification type objects
	*	'unread_count'		number of unread notifications the user has if count_unread is true in the options
	*	'total_count'		number of notifications the user has if count_total is true in the options
	*/
	public function load_notifications(array $options = array());
}
