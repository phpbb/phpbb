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

use phpbb\notification\type\type_interface;

/**
* Jabber notification method class
* This class handles sending Jabber messages for notifications
*/

class jabber extends \phpbb\notification\method\messenger_base
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Notification Method jabber Constructor
	 *
	 * @param \phpbb\user_loader $user_loader
	 * @param \phpbb\user $user
	 * @param \phpbb\config\config $config
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\user $user, \phpbb\config\config $config, $phpbb_root_path, $php_ext)
	{
		parent::__construct($user_loader, $phpbb_root_path, $php_ext);

		$this->user = $user;
		$this->config = $config;
	}

	/**
	* Get notification method name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.method.jabber';
	}

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*
	* @param type_interface $notification_type	An optional instance of a notification type. If provided, this
	*											method additionally checks if the type provides an email template.
	* @return bool
	*/
	public function is_available(type_interface $notification_type = null)
	{
		return parent::is_available($notification_type) && $this->global_available() && !empty($this->user->data['user_jabber']);
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

		$this->notify_using_messenger(NOTIFY_IM, 'short/');
	}
}
