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
* Email notification method class
* This class handles sending emails for notifications
*
* @package notifications
*/
class phpbb_notification_method_email extends phpbb_notification_method_messenger_base
{
	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'email';
	}

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available()
	{
		return $this->config['email_enable'] && $this->user->data['user_email'];
	}

	/**
	* Parse the queue and notify the users
	*/
	public function notify()
	{
		return $this->notify_using_messenger(NOTIFY_EMAIL);
	}
}
