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
	* Is the method enable by default?
	*
	* @return bool
	*/
	public function is_enabled_by_default();

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available();

	/**
	* Return the list of the users already notified
	*
	* @param int $notification_type_id ID of the notification type
	* @param array $options
	* @return array User
	*/
	public function get_notified_users($notification_type_id, array $options);

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
	* Update a notification
	*
	* @param \phpbb\notification\type\type_interface $notification Notification to update
	* @param array $data Data specific for this type that will be updated
	* @param array $options
	*/
	public function update_notification($notification, array $data, array $options);

	/**
	* Mark notifications read or unread
	*
	* @param bool|string $notification_type_id Type identifier of item types. False to mark read for all item types
	* @param bool|int|array $item_id Item id or array of item ids. False to mark read for all item ids
	* @param bool|int|array $user_id User id or array of user ids. False to mark read for all user ids
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	* @param bool $mark_read Define if the notification as to be set to True or False. (Default: True)
	*/
	public function mark_notifications($notification_type_id, $item_id, $user_id, $time = false, $mark_read = true);

	/**
	* Mark notifications read or unread from a parent identifier
	*
	* @param string $notification_type_id Type identifier of item types
	* @param bool|int|array $item_parent_id Item parent id or array of item parent ids. False to mark read for all item parent ids
	* @param bool|int|array $user_id User id or array of user ids. False to mark read for all user ids
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	* @param bool $mark_read Define if the notification as to be set to True or False. (Default: True)
	*/
	public function mark_notifications_by_parent($notification_type_id, $item_parent_id, $user_id, $time = false, $mark_read = true);

	/**
	* Mark notifications read or unread
	*
	* @param int $notification_id Notification id of notification ids.
	* @param bool|int $time Time at which to mark all notifications prior to as read. False to mark all as read. (Default: False)
	* @param bool $mark_read Define if the notification as to be set to True or False. (Default: True)
	*/
	public function mark_notifications_by_id($notification_id, $time = false, $mark_read = true);

	/**
	* Delete a notification
	*
	* @param string $notification_type_id Type identifier of item types
	* @param int|array $item_id Identifier within the type (or array of ids)
	* @param mixed $parent_id Parent identifier within the type (or array of ids), used in combination with item_id if specified (Default: false; not checked)
	* @param mixed $user_id User id (Default: false; not checked)
	*/
	public function delete_notifications($notification_type_id, $item_id, $parent_id = false, $user_id = false);

	/**
	* Delete all notifications older than a certain time
	*
	* @param int $timestamp Unix timestamp to delete all notifications that were created before
	* @param bool $only_read True (default) to only prune read notifications
	*/
	public function prune_notifications($timestamp, $only_read = true);

	/**
	* Purge all notifications of a certain type
	*
	* This should be called when an extension which has notification types
	* is purged so that all those notifications are removed
	*
	* @param string $notification_type_id Type identifier of the subscription
	*/
	public function purge_notifications($notification_type_id);
}
