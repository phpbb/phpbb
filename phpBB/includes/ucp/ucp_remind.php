<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp_remind.php
// STARTED   : Mon May 19, 2003
// COPYRIGHT : © 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

class ucp_remind extends module 
{
	function ucp_remind($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$submit = (isset($_POST['submit'])) ? true : false;

		if ($submit)
		{
			$username	= request_var('username', '');
			$email		= request_var('email', '');

			$sql = 'SELECT user_id, username, user_email, user_jabber, user_notify_type, user_type, user_lang
				FROM ' . USERS_TABLE . "
				WHERE user_email = '" . $db->sql_escape($email) . "'
					AND username = '" . $db->sql_escape($username) . "'";
			if (!($result = $db->sql_query($sql)))
			{
				trigger_error($user->lang['NO_USER']);
			}

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error($lang['NO_EMAIL']);
			}
			$db->sql_freeresult($result);

			if ($row['user_type'] == USER_INACTIVE)
			{
				trigger_error($lang['ACCOUNT_INACTIVE']);
			}

			$server_url = generate_board_url();
			$username = $row['username'];
			$user_id = $row['user_id'];

			$key_len = 54 - strlen($server_url);
			$key_len = ($str_len > 6) ? $key_len : 6;
			$user_actkey = substr(gen_rand_string(10), 0, $key_len);
			$user_password = gen_rand_string(8);

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_newpasswd = '" . $db->sql_escape(md5($user_password)) . "', user_actkey = '" . $db->sql_escape($user_actkey) . "'
				WHERE user_id = " . $row['user_id'];
			$db->sql_query($sql);

			include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

			$messenger = new messenger();

			$messenger->template('user_activate_passwd', $row['user_lang']);
			$messenger->subject($subject);

			$messenger->replyto($user->data['user_email']);
			$messenger->to($row['user_email'], $row['username']);
			$messenger->im($row['user_jabber'], $row['username']);

			$messenger->assign_vars(array(
				'SITENAME'	=> $config['sitename'],
				'USERNAME'	=> $username,
				'PASSWORD'	=> $user_password,
				'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

				'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
			);

			$messenger->send($row['user_notify_type']);
			$messenger->queue->save();

			meta_refresh(3, "index.$phpEx$SID");

			$message = $user->lang['PASSWORD_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'],  '<a href="' . "index.$phpEx$SID" . '">', '</a>');
			trigger_error($message);
		}
		else
		{
			$username = $email = '';
		}

		$template->assign_vars(array(
			'USERNAME'	=> $username,
			'EMAIL'		=> $email)
		);

		$this->display($user->lang['UCP_REMIND'], 'ucp_remind.html');
	}
}

?>