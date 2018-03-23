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

namespace phpbb\notification\type;

/**
* Base notifications interface
*/
interface type_interface
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type();

	/**
	* Set initial data from the database
	*
	* @param array $data Row directly from the database
	*/
	public function set_initial_data($data);

	/**
	* Get the id of the item
	*
	* @param array $type_data The type specific data
	*/
	static public function get_item_id($type_data);

	/**
	* Get the id of the parent
	*
	* @param array $type_data The type specific data
	*/
	static public function get_item_parent_id($type_data);

	/**
	* Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	*
	* @return bool True/False whether or not this is available to the user
	*/
	public function is_available();

	/**
	* Find the users who want to receive notifications
	*
	* @param array $type_data The type specific data
	* @param array $options Options for finding users for notification
	* 		ignore_users => array of users and user types that should not receive notifications from this type because they've already been notified
	* 						e.g.: array(2 => array(''), 3 => array('', 'email'), ...)
	*
	* @return array
	*/
	public function find_users_for_notification($type_data, $options);

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query();

	/**
	* Get the special items to load
	*
	* @return array Data will be combined sent to load_special() so you can run a single query and get data required for this notification type
	*/
	public function get_load_special();

	/**
	* Load the special items
	*
	* @param array $data Data from get_load_special()
	* @param array $notifications Array of notifications (key is notification_id, value is the notification objects)
	*/
	public function load_special($data, $notifications);

	/**
	* Get the CSS style class of the notification
	*
	* @return string
	*/
	public function get_style_class();

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title();

	/**
	* Get the HTML formatted reference of the notification
	*
	* @return string
	*/
	public function get_reference();

	/**
	* Get the forum of the notification reference
	*
	* @return string
	*/
	public function get_forum();

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url();

	/**
	* Get the url to redirect after the item has been marked as read
	*
	* @return string URL
	*/
	public function get_redirect_url();

	/**
	* URL to unsubscribe to this notification
	*
	* @param string|bool $method Method name to unsubscribe from (email|jabber|etc), False to unsubscribe from all notifications for this item
	*/
	public function get_unsubscribe_url($method);

	/**
	* Get the user's avatar (the user who caused the notification typically)
	*
	* @return string
	*/
	public function get_avatar();

	/**
	* Prepare to output the notification to the template
	*/
	public function prepare_for_display();

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template();

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables();

	/**
	* Pre create insert array function
	* This allows you to perform certain actions, like run a query
	* and load data, before create_insert_array() is run. The data
	* returned from this function will be sent to create_insert_array().
	*
	* @param array $type_data The type specific data
	* @param array $notify_users Notify users list
	* 		Formated from find_users_for_notification()
	* @return array Whatever you want to send to create_insert_array().
	*/
	public function pre_create_insert_array($type_data, $notify_users);

	/**
	* Function for preparing the data for insertion in an SQL query
	*
	* @param array $type_data The type specific data
	* @param array $pre_create_data Data from pre_create_insert_array()
	*/
	public function create_insert_array($type_data, $pre_create_data);

	/**
	* Function for getting the data for insertion in an SQL query
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function get_insert_array();

	/**
	* Function for preparing the data for update in an SQL query
	* (The service handles insertion)
	*
	* @param array $type_data Data unique to this notification type
	*
	* @return array Array of data ready to be updated in the database
	*/
	public function create_update_array($type_data);

	/**
	* Mark this item read
	*
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string
	*/
	public function mark_read($return = false);

	/**
	* Mark this item unread
	*
	* @param bool $return True to return a string containing the SQL code to update this item, False to execute it (Default: False)
	* @return string
	*/
	public function mark_unread($return = false);
}
