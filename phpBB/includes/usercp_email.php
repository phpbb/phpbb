<?php
/***************************************************************************
 *                             usercp_email.php 
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

// Is send through board enabled? No, return to index
if (!$board_config['board_email_form'])
{
	redirect(append_sid("index.$phpEx", true));
}

if ( !empty($HTTP_GET_VARS[POST_USERS_URL]) || !empty($HTTP_POST_VARS[POST_USERS_URL]) )
{
	$user_id = ( !empty($HTTP_GET_VARS[POST_USERS_URL]) ) ? intval($HTTP_GET_VARS[POST_USERS_URL]) : intval($HTTP_POST_VARS[POST_USERS_URL]);
}
else
{
	message_die(GENERAL_MESSAGE, $lang['No_user_specified']);
}

if ( !$userdata['session_logged_in'] )
{
	redirect(append_sid("login.$phpEx?redirect=profile.$phpEx&mode=email&" . POST_USERS_URL . "=$user_id", true));
}

$sql = "SELECT username, user_email, user_viewemail, user_lang  
	FROM " . USERS_TABLE . " 
	WHERE user_id = $user_id";
if ( $result = $db->sql_query($sql) )
{
	$row = $db->sql_fetchrow($result);

	$username = $row['username'];
	$user_email = $row['user_email']; 
	$user_lang = $row['user_lang'];

	if ( $row['user_viewemail'] || $userdata['user_level'] == ADMIN )
	{
		if ( time() - $userdata['user_emailtime'] < $board_config['flood_interval'] )
		{
			message_die(GENERAL_MESSAGE, $lang['Flood_email_limit']);
		}

		if ( isset($HTTP_POST_VARS['submit']) )
		{
			$error = FALSE;

			if ( !empty($HTTP_POST_VARS['subject']) )
			{
				$subject = trim(stripslashes($HTTP_POST_VARS['subject']));
			}
			else
			{
				$error = TRUE;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . '<br />' . $lang['Empty_subject_email'] : $lang['Empty_subject_email'];
			}

			if ( !empty($HTTP_POST_VARS['message']) )
			{
				$message = trim(stripslashes($HTTP_POST_VARS['message']));
			}
			else
			{
				$error = TRUE;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . '<br />' . $lang['Empty_message_email'] : $lang['Empty_message_email'];
			}

			if ( !$error )
			{
				$sql = "UPDATE " . USERS_TABLE . " 
					SET user_emailtime = " . time() . " 
					WHERE user_id = " . $userdata['user_id'];
				if ( $result = $db->sql_query($sql) )
				{
					include($phpbb_root_path . 'includes/emailer.'.$phpEx);
					$emailer = new emailer($board_config['smtp_delivery']);

					$emailer->from($userdata['user_email']);
					$emailer->replyto($userdata['user_email']);

					$email_headers = 'X-AntiAbuse: Board servername - ' . $server_name . "\n";
					$email_headers .= 'X-AntiAbuse: User_id - ' . $userdata['user_id'] . "\n";
					$email_headers .= 'X-AntiAbuse: Username - ' . $userdata['username'] . "\n";
					$email_headers .= 'X-AntiAbuse: User IP - ' . decode_ip($user_ip) . "\n";

					$emailer->use_template('profile_send_email', $user_lang);
					$emailer->email_address($user_email);
					$emailer->set_subject($subject);
					$emailer->extra_headers($email_headers);

					$emailer->assign_vars(array(
						'SITENAME' => $board_config['sitename'], 
						'BOARD_EMAIL' => $board_config['board_email'], 
						'FROM_USERNAME' => $userdata['username'], 
						'TO_USERNAME' => $username, 
						'MESSAGE' => $message)
					);
					$emailer->send();
					$emailer->reset();

					if ( !empty($HTTP_POST_VARS['cc_email']) )
					{
						$emailer->from($userdata['user_email']);
						$emailer->replyto($userdata['user_email']);
						$emailer->use_template('profile_send_email');
						$emailer->email_address($userdata['user_email']);
						$emailer->set_subject($subject);

						$emailer->assign_vars(array(
							'SITENAME' => $board_config['sitename'], 
							'BOARD_EMAIL' => $board_config['board_email'], 
							'FROM_USERNAME' => $userdata['username'], 
							'TO_USERNAME' => $username, 
							'MESSAGE' => $message)
						);
						$emailer->send();
						$emailer->reset();
					}

					$template->assign_vars(array(
						'META' => '<meta http-equiv="refresh" content="5;url=' . append_sid("index.$phpEx") . '">')
					);

					$message = $lang['Email_sent'] . '<br /><br />' . sprintf($lang['Click_return_index'],  '<a href="' . append_sid("index.$phpEx") . '">', '</a>');

					message_die(GENERAL_MESSAGE, $message);
				}
				else
				{
					message_die(GENERAL_ERROR, 'Could not update last email time', '', __LINE__, __FILE__, $sql);
				}
			}
		}

		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			'body' => 'profile_send_email.tpl')
		);
		make_jumpbox('viewforum.'.$phpEx);

		if ( $error )
		{
			$template->set_filenames(array(
				'reg_header' => 'error_body.tpl')
			);
			$template->assign_vars(array(
				'ERROR_MESSAGE' => $error_msg)
			);
			$template->assign_var_from_handle('ERROR_BOX', 'reg_header');
		}

		$template->assign_vars(array(
			'USERNAME' => $username,

			'S_HIDDEN_FIELDS' => '', 
			'S_POST_ACTION' => append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL . "=$user_id"), 

			'L_SEND_EMAIL_MSG' => $lang['Send_email_msg'], 
			'L_RECIPIENT' => $lang['Recipient'], 
			'L_SUBJECT' => $lang['Subject'],
			'L_MESSAGE_BODY' => $lang['Message_body'], 
			'L_MESSAGE_BODY_DESC' => $lang['Email_message_desc'], 
			'L_EMPTY_SUBJECT_EMAIL' => $lang['Empty_subject_email'],
			'L_EMPTY_MESSAGE_EMAIL' => $lang['Empty_message_email'],
			'L_OPTIONS' => $lang['Options'],
			'L_CC_EMAIL' => $lang['CC_email'], 
			'L_SPELLCHECK' => $lang['Spellcheck'],
			'L_SEND_EMAIL' => $lang['Send_email'])
		);

		$template->pparse('body');

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['User_prevent_email']);
	}
}
else
{
	message_die(GENERAL_MESSAGE, $lang['User_not_exist']);
}

?>