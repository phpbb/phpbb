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
class phpbb_notification_method_email extends phpbb_notification_method_base
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
	* Notify method (since jabber gets sent through the same messenger, we let the jabber class inherit from this to reduce code duplication)
	*
	* @var mixed
	*/
	protected $notify_method = NOTIFY_EMAIL;

	/**
	* Base directory to prepend to the email template name
	*
	* @var string
	*/
	protected $email_template_base_dir = '';

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
		if (!sizeof($this->queue))
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
		$messenger = new messenger();
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

			$messenger->template($this->email_template_base_dir . $notification->get_email_template(), $user['user_lang']);

			$messenger->to($user['user_email'], $user['username']);

			$messenger->assign_vars(array_merge(array(
				'USERNAME'						=> $user['username'],

				'U_NOTIFICATION_SETTINGS'		=> generate_board_url() . '/ucp.' . $this->php_ext . '?i=ucp_notifications',
			), $notification->get_email_template_variables()));

			$messenger->send($this->notify_method);
		}

		// Save the queue in the messenger class (has to be called or these emails could be lost?)
		$messenger->save_queue();

		// We're done, empty the queue
		$this->empty_queue();
	}
}
