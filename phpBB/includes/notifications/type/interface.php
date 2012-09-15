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
interface phpbb_notifications_type_interface
{
	public static function get_item_type();

	public static function get_item_id($type_data);

	public static function find_users_for_notification(ContainerBuilder $phpbb_container, $type_data);

	public function get_title();

	public function get_email_template_variables();

	public function get_url();

	public function get_unsubscribe_url($method);

	public function mark_read($return);

	public function mark_unread($return);

	public function create_insert_array($type_data);

	public function get_load_special();

	public static function load_special(ContainerBuilder $phpbb_container, $data, $notifications);
}
