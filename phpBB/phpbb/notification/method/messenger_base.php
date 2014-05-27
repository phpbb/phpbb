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
		$board_url = generate_board_url();

		// Time to go through the queue and send emails
		foreach ($this->queue as $notification)
		{
			if ($notification->get_email_template() === false)
			{
				continue;
			}

			$user = $this->user_loader->get_user($notification->user_id);

			if ($user['user_type'] == USER_IGNORE || in_array($notification->user_id, $banned_users))
			{
				continue;
			}

			$messenger->template($template_dir_prefix . $notification->get_email_template(), $user['user_lang']);

			$messenger->set_addresses($user);

			$messenger->assign_vars(array_merge(array(
				'USERNAME'						=> $user['username'],

				'U_NOTIFICATION_SETTINGS'		=> generate_board_url() . '/ucp.' . $this->php_ext . '?i=ucp_notifications',
			), $notification->get_email_template_variables()));

			$messenger->send($notify_method);
		}

		// Save the queue in the messenger class (has to be called or these emails could be lost?)
		$messenger->save_queue();

		// We're done, empty the queue
		$this->empty_queue();
	}
}
