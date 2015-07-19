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
* Email notification method class
* This class handles sending emails for notifications
*/

class email extends \phpbb\notification\method\messenger_base
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Notification Method email Constructor
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
		return 'notification.method.email';
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
