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
* Base notifications interface
* @package notifications
*/
interface phpbb_notification_type_interface
{
	public static function get_item_type();

	public static function get_item_id($type_data);

	public function is_available();

	public function find_users_for_notification($type_data, $options);

	public function get_title();

	public function get_email_template_variables();

	public function get_url();

	public function get_unsubscribe_url($method);

	public function mark_read($return);

	public function mark_unread($return);

	public function pre_create_insert_array($type_data, $notify_users);

	public function create_insert_array($type_data, $pre_create_data);

	public function users_to_query();

	public function get_load_special();

	public function load_special($data, $notifications);
}
