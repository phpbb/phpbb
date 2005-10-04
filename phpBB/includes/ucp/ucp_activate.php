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
* @package ucp
* ucp_activate
* User activation
*/
class ucp_activate
{
	function main($id, $mode)
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

		$update_password = ($row['user_newpasswd']) ? true : false;

		if ($update_password)
		{
			$sql_ary = array(
				'user_type'			=> USER_NORMAL,
				'user_actkey'		=> '',
				'user_password'		=> $row['user_newpasswd'],
				'user_newpasswd'	=> ''
			);
		
			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE user_id = ' . $row['user_id'];
			$result = $db->sql_query($sql);
		}

		// TODO: check for group membership after password update... active_flip there too
		if (!$update_password)
		{
			// Now we need to demote the user from the inactive group and add him to the registered group

			include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			user_active_flip($row['user_id'], $row['user_type'], '', $row['username']);
		}

		if ($config['require_activation'] == USER_ACTIVATION_ADMIN && !$update_password)
		{
			include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

			$messenger = new messenger();

			$messenger->template('admin_welcome_activated', $row['user_lang']);

			$messenger->replyto($config['board_contact']);
			$messenger->to($row['user_email'], $row['username']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

			$messenger->assign_vars(array(
				'SITENAME'	=> $config['sitename'],
				'USERNAME'	=> $row['username'],

				'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']))
			);

			$messenger->send($row['user_notify_type']);
			$messenger->save_queue();

			$message = 'ACCOUNT_ACTIVE_ADMIN';
		}
		else
		{
			$message = (!$update_password) ? 'ACCOUNT_ACTIVE' : 'PASSWORD_ACTIVATED';
		}

		if (!$update_password)
		{
			set_config('newest_user_id', $row['user_id']);
			set_config('newest_username', $row['username']);
			set_config('num_users', $config['num_users'] + 1, TRUE);
		}

		meta_refresh(3, "{$phpbb_root_path}index.$phpEx$SID");
		trigger_error($user->lang[$message]);
	}
}

?>