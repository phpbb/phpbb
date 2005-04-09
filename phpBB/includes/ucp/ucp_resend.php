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
* ucp_resend
* Resending activation emails
*/
class ucp_resend extends module 
{
	function ucp_resend($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$submit = (isset($_POST['submit'])) ? true : false;

		if ($submit)
		{
			$username	= request_var('username', '');
			$email		= request_var('email', '');

			$sql = 'SELECT user_id, username, user_email, user_type, user_lang, user_actkey
				FROM ' . USERS_TABLE . "
				WHERE user_email = '" . $db->sql_escape($email) . "'
					AND username = '" . $db->sql_escape($username) . "'";
			$result = $db->sql_query($sql);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error('NO_EMAIL_USER');
			}
			$db->sql_freeresult($result);

			if (!$row['user_actkey'])
			{
				trigger_error('ACCOUNT_ALREADY_ACTIVATED');
			}

			$server_url = generate_board_url();
			$username = $row['username'];
			$user_id = $row['user_id'];

/*			if ($coppa)
			{
				$email_template = 'coppa_welcome_inactive';
			}*/
			if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				$email_template = 'admin_welcome_inactive';
			}
			else
			{
				$email_template = 'user_welcome_inactive';
			}

			include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

			$messenger = new messenger(false);

			if ($config['require_activation'] == USER_ACTIVATION_SELF || $coppa)
			{
				$messenger->template('user_resend_inactive', $row['user_lang']);

				$messenger->replyto($config['board_contact']);
				$messenger->to($row['user_email'], $row['username']);

				$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

				$messenger->assign_vars(array(
					'SITENAME'		=> $config['sitename'],
					'WELCOME_MSG'	=> sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename']),
					'USERNAME'		=> $row['username'],
					'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

					'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u={$row['user_id']}&k={$row['user_actkey']}")
				);

				if ($coppa)
				{
					$messenger->assign_vars(array(
						'FAX_INFO'		=> $config['coppa_fax'],
						'MAIL_INFO'		=> $config['coppa_mail'],
						'EMAIL_ADDRESS' => $row['user_email'],
						'SITENAME'		=> $config['sitename'])
					);
				}

				$messenger->send(NOTIFY_EMAIL);
			}

			if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				// Grab an array of user_id's with a_user permissions ... these users
				// can activate a user
				$admin_ary = $auth->acl_get_list(false, 'a_user', false);

				$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
					FROM ' . USERS_TABLE . '
					WHERE user_id IN (' . implode(', ', $admin_ary[0]['a_user']) .')';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$messenger->template('admin_activate', $row['user_lang']);
					$messenger->replyto($config['board_contact']);
					$messenger->to($row['user_email'], $row['username']);
					$messenger->im($row['user_jabber'], $row['username']);

					$messenger->assign_vars(array(
						'USERNAME'		=> $row['username'],
						'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

						'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u={$row['user_id']}&k={$row['user_actkey']}")
					);

					$messenger->send($row['user_notify_type']);
				}
				$db->sql_freeresult($result);
			}

			meta_refresh(3, "index.$phpEx$SID");

			$message = $user->lang['ACTIVATION_EMAIL_SENT'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'],  '<a href="' . "index.$phpEx$SID" . '">', '</a>');
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

		$this->display($user->lang['UCP_RESEND'], 'ucp_resend.html');
	}
}

?>
