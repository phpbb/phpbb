<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp_activate.php
// STARTED   : Mon May 19, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------
 
class ucp_activate extends module 
{
	function ucp_activate($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$user_id = request_var('u', 0);
		$key = request_var('k', '');

		$sql = 'SELECT user_id, username, user_type, user_email, user_newpasswd, user_lang, user_notify_type, user_actkey
			FROM ' . USERS_TABLE . "
			WHERE user_id = $user_id";
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_USER']);
		}
		$db->sql_freeresult($result);

		if ($row['user_type'] <> USER_INACTIVE && !$row['user_newpasswd'])
		{
			meta_refresh(3, "index.$phpEx$SID");
			trigger_error($user->lang['ALREADY_ACTIVATED']);
		}
		
		if ($row['user_actkey'] != $key)
		{
			trigger_error($user->lang['WRONG_ACTIVATION']);
		}

		$sql_update_pass = ($row['user_newpasswd']) ? ", user_password = '" . $db->sql_escape($row['user_newpasswd']) . "', user_newpasswd = ''" : '';

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_type = ' . USER_NORMAL . ", user_actkey = ''$sql_update_pass 
			WHERE user_id = " . $row['user_id'];
		$result = $db->sql_query($sql);

		if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $sql_update_pass)
		{
			include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

			$messenger = new messenger();

			$messenger->template('admin_welcome_activated', $row['user_lang']);
			$messenger->subject($subject);

			$messenger->replyto($user->data['board_contact']);
			$messenger->to($row['user_email'], $row['username']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

			$messenger->assign_vars(array(
				'SITENAME'	=> $config['sitename'],
				'USERNAME'	=> $row['username'],
				'PASSWORD'	=> $password_confirm,
				'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']))
			);

			$messenger->send($row['user_notify_type']);
			$messenger->queue->save();

			$message = 'ACCOUNT_ACTIVE_ADMIN';

		}
		else
		{
			$message = (!$sql_update_pass) ? 'ACCOUNT_ACTIVE' : 'PASSWORD_ACTIVATED';
		}

		if (!$sql_update_pass)
		{
			set_config('newest_user_id', $row['user_id']);
			set_config('newest_username', $row['username']);
			set_config('num_users', $config['num_users'] + 1, TRUE);
		}

		meta_refresh(3, "index.$phpEx$SID");
		trigger_error($user->lang[$message]);
	}
}

?>