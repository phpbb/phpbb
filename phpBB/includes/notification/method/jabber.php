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
* Jabber notification method class
* This class handles sending Jabber messages for notifications
*
* @package notifications
*/
class phpbb_notification_method_jabber extends phpbb_notification_method_messenger_base
{
	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'jabber';
	}

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available()
	{
		return ($this->global_available() && $this->user->data['user_jabber']);
	}

	/**
	* Is this method available at all?
	* This is checked before notifications are sent
	*/
	public function global_available()
	{
		return ($this->config['jab_enable'] && @extension_loaded('xml'));
	}

	public function notify()
	{
		if (!$this->global_available())
		{
			return;
		}

		return $this->notify_using_messenger(NOTIFY_IM, 'short/');
	}
}
