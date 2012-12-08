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
* Notifications service class
* @package notifications
*/
class phpbb_mock_notification_manager
{
	public function load_notifications()
	{
		return array(
			'notifications'		=> array(),
			'unread_count'		=> 0,
		);
	}

	public function mark_notifications_read()
	{
	}

	public function mark_notifications_read_by_parent()
	{
	}

	public function mark_notifications_read_by_id()
	{
	}


	public function add_notifications()
	{
		return array();
	}

	public function add_notifications_for_users()
	{
	}

	public function update_notifications()
	{
	}

	public function delete_notifications()
	{
	}

	public function get_subscription_types()
	{
		return array();
	}

	public function get_subscription_methods()
	{
		return array();
	}


	public function get_global_subscriptions()
	{
		return array();
	}

	public function add_subscription()
	{
	}

	public function delete_subscription()
	{
	}

	public function load_users()
	{
	}

	public function get_user()
	{
		return null;
	}
}
