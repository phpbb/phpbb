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
* Jabber notification method class
* This class handles sending Jabber messages for notifications
*/

class jabber extends \phpbb\notification\method\messenger_base
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
		return !(
			empty($this->config['jab_enable']) ||
			empty($this->config['jab_host']) ||
			empty($this->config['jab_username']) ||
			empty($this->config['jab_password']) ||
			!@extension_loaded('xml')
		);
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
