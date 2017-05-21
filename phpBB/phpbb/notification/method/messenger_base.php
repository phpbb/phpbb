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
* Abstract notification method handling email and jabber notifications
* using the phpBB messenger.
*/
abstract class messenger_base extends \phpbb\notification\method\base
{
	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Notification Method Board Constructor
	 *
	 * @param \phpbb\user_loader $user_loader
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\user_loader $user_loader, $phpbb_root_path, $php_ext)
	{
		$this->user_loader = $user_loader;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Notify using phpBB messenger
	*
	* @param int $notify_method				Notify method for messenger (e.g. NOTIFY_IM)
	* @param string $template_dir_prefix	Base directory to prepend to the email template name
	*
	* @return null
	*/
	protected function notify_using_messenger($notify_method, $template_dir_prefix = '')
	{
		if (empty($this->queue))
		{
			return;
		}

		// Load all users we want to notify (we need their email address)
		$user_ids = $users = array();
		foreach ($this->queue as $notification)
		{
			$user_ids[] = $notification->user_id;
		}

		// We do not send emails to banned users
		if (!function_exists('phpbb_get_banned_user_ids'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}
		$banned_users = phpbb_get_banned_user_ids($user_ids);

		// Load all the users we need
		$this->user_loader->load_users($user_ids);

		// Load the messenger
		if (!class_exists('messenger'))
		{
			include($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);
		}
		$messenger = new \messenger();

		// Time to go through the queue and send emails
		/** @var \phpbb\notification\type\type_interface $notification */
		foreach ($this->queue as $notification)
		{
			if ($notification->get_email_template() === false)
			{
				continue;
			}

			$user = $this->user_loader->get_user($notification->user_id);

			if ($user['user_type'] == USER_IGNORE || ($user['user_type'] == USER_INACTIVE && $user['user_inactive_reason'] == INACTIVE_MANUAL) || in_array($notification->user_id, $banned_users))
			{
				continue;
			}

			$messenger->template($notification->get_email_template(), $user['user_lang'], '', $template_dir_prefix);

			$messenger->set_addresses($user);

			$messenger->assign_vars(array_merge(array(
				'USERNAME'						=> $user['username'],

				'U_NOTIFICATION_SETTINGS'		=> generate_board_url() . '/ucp.' . $this->php_ext . '?i=ucp_notifications&mode=notification_options',
			), $notification->get_email_template_variables()));

			$messenger->send($notify_method);
		}

		// Save the queue in the messenger class (has to be called or these emails could be lost?)
		$messenger->save_queue();

		// We're done, empty the queue
		$this->empty_queue();
	}
}
