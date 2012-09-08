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
class phpbb_notifications_method_email extends phpbb_notifications_method_base
{
	public static function is_available()
	{
		// Email is always available
		return true;
	}

	public function notify($notification)
	{
		// email the user
	}

	public function run_queue()
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

		$sql = 'SELECT * FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

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
			$notification->users($users);

			$user = $notification->get_user($notification->user_id);

			$messenger->template('privmsg_notify', $user['user_lang']);

			$messenger->to($user['user_email'], $user['username']);

			$messenger->assign_vars(array(
				'SUBJECT'		=> htmlspecialchars_decode($notification->get_title()),
				'AUTHOR_NAME'	=> '',
				'USERNAME'		=> htmlspecialchars_decode($user['username']),

				'U_INBOX'			=> $board_url . "/ucp.{$this->php_ext}?i=pm&folder=inbox",
				'U_VIEW_MESSAGE'	=> $board_url . "/ucp.{$this->php_ext}?i=pm&mode=view&p={$notification->item_id}",
			));

			$messenger->send('email');
		}

		// Save the queue in the messenger class (has to be called or these emails could be lost?)
		$messenger->save_queue();

		// We're done, empty the queue
		$this->empty_queue();
	}
}
