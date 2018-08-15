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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* ucp_username
* Sending username reminders
*/
class ucp_username
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $phpbb_root_path, $phpEx, $request;
		global $db, $user, $template, $phpbb_container, $phpbb_dispatcher;

		$email		= strtolower($request->variable('email', ''));
		$submit		= (isset($_POST['submit'])) ? true : false;

		add_form_key('ucp_username');

		if ($submit)
		{
			if (!check_form_key('ucp_username'))
			{
				trigger_error('FORM_INVALID');
			}

			$server_url = generate_board_url();

			include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

			$sql_array = array(
				'SELECT'	=> 'username, user_email, user_jabber, user_notify_type, user_lang',
				'FROM'		=> array(USERS_TABLE => 'u'),
				'WHERE'		=> "user_email_hash = '" . $db->sql_escape(phpbb_email_hash($email)) . "'"
			);

			$sql = $db->sql_build_query('SELECT', $sql_array);
			$result = $db->sql_query($sql);
			$count = 0;
			while ($user_row = $db->sql_fetchrow($result))
			{
				$count++;
				$messenger = new messenger(false);
				$messenger->template('user_remind_username', $user_row['user_lang']);
				$messenger->set_addresses($user_row);
				$messenger->anti_abuse_headers($config, $user);
				$messenger->assign_vars(array(
					'USERNAME'	=> htmlspecialchars_decode($user_row['username']),
					'U_LOGIN'	=> "$server_url/ucp.$phpEx?mode=login",
					'U_FORGOT_PASS'	=> "$server_url/ucp.$phpEx?mode=sendpassword")
				);
				$messenger->send($user_row['user_notify_type']);
			}
			$db->sql_freeresult($result);

			if ($count == 0)
			{
				trigger_error('NO_EMAIL_USER');
			}

			meta_refresh(3, append_sid("{$phpbb_root_path}index.$phpEx"));

			$message = $user->lang(['USERNAME_REMINDER_SENT'], $count) . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars(array(
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid($phpbb_root_path . 'ucp.' . $phpEx, 'mode=remind_username'))
		);

		$this->tpl_name = 'ucp_username';
		$this->page_title = 'UCP_USERNAME';
	}
}
