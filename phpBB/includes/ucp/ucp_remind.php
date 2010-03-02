<?php
/** 
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* ucp_remind
* Sending password reminders
* @package ucp
*/
class ucp_remind
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $phpbb_root_path, $phpEx;
		global $db, $user, $auth, $template;

		$username	= request_var('username', '');
		$email		= request_var('email', '');
		$submit		= (isset($_POST['submit'])) ? true : false;

		if ($submit)
		{
			$sql = 'SELECT user_id, username, user_email, user_jabber, user_notify_type, user_type, user_lang
				FROM ' . USERS_TABLE . "
				WHERE user_email = '" . $db->sql_escape($email) . "'
					AND LOWER(username) = '" . $db->sql_escape(strtolower($username)) . "'";
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$user_row)
			{
				trigger_error('NO_EMAIL_USER');
			}

			if ($user_row['user_type'] == USER_INACTIVE)
			{
				trigger_error('ACCOUNT_NOT_ACTIVATED');
			}

			$server_url = generate_board_url();

			$key_len = 54 - strlen($server_url);
			$key_len = ($key_len < 6) ? 6 : $key_len;
			$user_actkey = substr(gen_rand_string(10), 0, $key_len);
			$user_password = gen_rand_string(8);

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_newpasswd = '" . $db->sql_escape(md5($user_password)) . "', user_actkey = '" . $db->sql_escape($user_actkey) . "'
				WHERE user_id = " . $user_row['user_id'];
			$db->sql_query($sql);

			include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

			$messenger = new messenger(false);

			$messenger->template('user_activate_passwd', $user_row['user_lang']);

			$messenger->replyto($user->data['user_email']);
			$messenger->to($user_row['user_email'], $user_row['username']);
			$messenger->im($user_row['user_jabber'], $user_row['username']);

			$messenger->assign_vars(array(
				'SITENAME'	=> $config['sitename'],
				'USERNAME'	=> html_entity_decode($user_row['username']),
				'PASSWORD'	=> html_entity_decode($user_password),
				'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

				'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u={$user_row['user_id']}&k=$user_actkey")
			);

			$messenger->send($user_row['user_notify_type']);

			meta_refresh(3, append_sid("{$phpbb_root_path}index.$phpEx"));

			$message = $user->lang['PASSWORD_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'],  '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
			trigger_error($message);
		}

		$template->assign_vars(array(
			'USERNAME'			=> $username,
			'EMAIL'				=> $email,
			'S_PROFILE_ACTION'	=> append_sid($phpbb_root_path . 'ucp.' . $phpEx, 'mode=sendpassword'))
		);

		$this->tpl_name = 'ucp_remind';
		$this->page_title = 'UCP_REMIND';
	}
}

?>