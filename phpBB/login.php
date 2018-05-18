<?php
/***************************************************************************
 *                                login.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: login.php,v 1.1 2010/10/10 15:01:18 orynider Exp $
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
 ***************************************************************************/

//
// Allow people to reach login page if
// board is shut down
//
define("IN_LOGIN", true);

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

//
// Set page ID for session management
//
$userdata = session_pagestart($user_ip, PAGE_LOGIN);
init_userprefs($userdata);
//
// End session management
//

$redirect = $l_explain = $l_success = ''; 
$admin = false;
$s_display = false;

// session id check
if (!empty($_POST['sid']) || !empty($_GET['sid']))
{
	$sid = (!empty($_POST['sid'])) ? $_POST['sid'] : $_GET['sid'];
}
else
{
	$sid = '';
}

if( isset($_POST['login']) || isset($_GET['login']) || isset($_POST['logout']) || isset($_GET['logout']) )
{
	if( ( isset($_POST['login']) || isset($_GET['login']) ) && (!$userdata['session_logged_in'] || isset($_POST['admin'])) )
	{
		$username = isset($_POST['username']) ? phpbb_clean_username($_POST['username']) : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';

		$sql = "SELECT user_id, username, user_password, user_active, user_level, user_login_tries, user_last_login_try
			FROM " . USERS_TABLE . "
			WHERE username = '" . str_replace("\\'", "''", $username) . "'";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Error in obtaining userdata', '', __LINE__, __FILE__, $sql);
		}

		if( $row = $db->sql_fetchrow($result) )
		{
			if( $row['user_level'] != ADMIN && $board_config['board_disable'] )
			{
				redirect(append_sid("index.$phpEx", true));
			}
			else
			{
				// If the last login is more than x minutes ago, then reset the login tries/time
				if ($row['user_last_login_try'] && $board_config['login_reset_time'] && $row['user_last_login_try'] < (time() - ($board_config['login_reset_time'] * 60)))
				{
					$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_login_tries = 0, user_last_login_try = 0 WHERE user_id = ' . $row['user_id']);
					$row['user_last_login_try'] = $row['user_login_tries'] = 0;
				}
				
				// Check to see if user is allowed to login again... if his tries are exceeded
				if ($row['user_last_login_try'] && $board_config['login_reset_time'] && $board_config['max_login_attempts'] && 
					$row['user_last_login_try'] >= (time() - ($board_config['login_reset_time'] * 60)) && $row['user_login_tries'] >= $board_config['max_login_attempts'] && $userdata['user_level'] != ADMIN)
				{
					message_die(GENERAL_MESSAGE, sprintf($lang['Login_attempts_exceeded'], $board_config['max_login_attempts'], $board_config['login_reset_time']));
				}

				if( md5($password) == $row['user_password'] && $row['user_active'] )
				{
					$autologin = ( isset($_POST['autologin']) ) ? TRUE : 0;

					$admin = (isset($_POST['admin'])) ? 1 : 0;
					$session_id = session_begin($row['user_id'], $user_ip, PAGE_INDEX, FALSE, $autologin, $admin);

					// Reset login tries
					$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_login_tries = 0, user_last_login_try = 0 WHERE user_id = ' . $row['user_id']);

					if( $session_id )
					{
						$url = ( !empty($_POST['redirect']) ) ? str_replace('&amp;', '&', htmlspecialchars($_POST['redirect'])) : "index.$phpEx";
						redirect(append_sid($url, true));
					}
					else
					{
						message_die(CRITICAL_ERROR, "Couldn't start session : login", "", __LINE__, __FILE__);
					}
				}
				// Only store a failed login attempt for an active user - inactive users can't login even with a correct password
				elseif ($row['user_active'])
				{
					// Save login tries and last login
					if ($row['user_id'] != ANONYMOUS)
					{
						$sql = 'UPDATE ' . USERS_TABLE . '
							SET user_login_tries = user_login_tries + 1, user_last_login_try = ' . time() . '
							WHERE user_id = ' . $row['user_id'];
						$db->sql_query($sql);
					}
				}

				$redirect = ( !empty($_POST['redirect']) ) ? str_replace('&amp;', '&', htmlspecialchars($_POST['redirect'])) : '';
				$redirect = str_replace('?', '&', $redirect);

				if (strstr(urldecode($redirect), "\n") || strstr(urldecode($redirect), "\r") || strstr(urldecode($redirect), ';url'))
				{
					message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
				}

				$template->assign_vars(array(
					'META' => "<meta http-equiv=\"refresh\" content=\"3;url=login.$phpEx?redirect=$redirect\">")
				);

				$message = $lang['Error_login'] . '<br /><br />' . sprintf($lang['Click_return_login'], "<a href=\"login.$phpEx?redirect=$redirect\">", '</a>') . '<br /><br />' .  sprintf($lang['Click_return_index'], '<a href="' . append_sid("index.$phpEx") . '">', '</a>');

				message_die(GENERAL_MESSAGE, $message);
			}
		}
		else
		{
			$redirect = ( !empty($_POST['redirect']) ) ? str_replace('&amp;', '&', htmlspecialchars($_POST['redirect'])) : "";
			$redirect = str_replace("?", "&", $redirect);

			if (strstr(urldecode($redirect), "\n") || strstr(urldecode($redirect), "\r") || strstr(urldecode($redirect), ';url'))
			{
				message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
			}

			$template->assign_vars(array(
				'META' => "<meta http-equiv=\"refresh\" content=\"3;url=login.$phpEx?redirect=$redirect\">")
			);

			$message = $lang['Error_login'] . '<br /><br />' . sprintf($lang['Click_return_login'], "<a href=\"login.$phpEx?redirect=$redirect\">", '</a>') . '<br /><br />' .  sprintf($lang['Click_return_index'], '<a href="' . append_sid("index.$phpEx") . '">', '</a>');

			message_die(GENERAL_MESSAGE, $message);
		}
	}
	else if( ( isset($_GET['logout']) || isset($_POST['logout']) ) && $userdata['session_logged_in'] )
	{
		// session id check
		if ($sid == '' || $sid != $userdata['session_id'])
		{
			message_die(GENERAL_ERROR, 'Invalid_session');
		}

		if( $userdata['session_logged_in'] )
		{
			session_end($userdata['session_id'], $userdata['user_id']);
		}

		if (!empty($_POST['redirect']) || !empty($_GET['redirect']))
		{
			$url = (!empty($_POST['redirect'])) ? htmlspecialchars($_POST['redirect']) : htmlspecialchars($_GET['redirect']);
			$url = str_replace('&amp;', '&', $url);
			redirect(append_sid($url, true));
		}
		else
		{
			redirect(append_sid("index.$phpEx", true));
		}
	}
	else
	{
		$url = ( !empty($_POST['redirect']) ) ? str_replace('&amp;', '&', htmlspecialchars($_POST['redirect'])) : "index.$phpEx";
		redirect(append_sid($url, true));
	}
}
else
{
	//
	// Do a full login page dohickey if
	// user not already logged in
	//
	if( !$userdata['session_logged_in'] || (isset($_GET['admin']) && $userdata['session_logged_in'] && $userdata['user_level'] == ADMIN))
	{
		$page_title = $lang['Login'];
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			'body' => 'login_body.tpl')
		);

		$forward_page = '';

		if( isset($_POST['redirect']) || isset($_GET['redirect']) )
		{
			$forward_to = $_SERVER['QUERY_STRING'];

			if( preg_match("/^redirect=([a-z0-9\.#\/\?&=\+\-_]+)/si", $forward_to, $forward_matches) )
			{
				$forward_to = ( !empty($forward_matches[3]) ) ? $forward_matches[3] : $forward_matches[1];
				$forward_match = explode('&', $forward_to);

				if(count($forward_match) > 1)
				{
					for($i = 1; $i < count($forward_match); $i++)
					{
						if(!preg_match('#sid=#', $forward_match[$i]))
						{
							if( $forward_page != '' )
							{
								$forward_page .= '&';
							}
							$forward_page .= $forward_match[$i];
						}
					}
					$forward_page = $forward_match[0] . '?' . $forward_page;
				}
				else
				{
					$forward_page = $forward_match[0];
				}
			}
		}

		$username = ( $userdata['user_id'] != ANONYMOUS ) ? $userdata['username'] : '';
		
		// Assign credential for username/password pair
		$credential = ($admin) ? md5(unique_id()) : false;
		
		$auth_provider_data = '';
		
		if ($redirect)
		{
			$s_hidden_fields['redirect'] = $redirect;
		}

		if ($admin)
		{
			$s_hidden_fields['credential'] = $credential;
		}		

		$s_hidden_fields = '<input type="hidden" name="redirect" value="' . $forward_page . '" />';
		$s_hidden_fields .= (isset($_GET['admin'])) ? '<input type="hidden" name="admin" value="1" />' : '';
		
		$s_hidden_fields = array('sid' => $user->session_id, $s_hidden_fields);		
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);		
		
		make_jumpbox('viewforum.'.$phpEx);				
		$template->assign_vars(array(
			'LOGIN_ERROR'		=> '',
			'LOGIN_EXPLAIN'		=> $l_explain,
			
			'USERNAME'				=> ($admin) ? $username : '',

			'USERNAME_CREDENTIAL'	=> 'username',
			'PASSWORD_CREDENTIAL'	=> ($admin) ? 'password_' . $credential : 'password',
			
			'PROVIDER_TEMPLATE_FILE' => false,
			
			'U_SEND_PASSWORD' 		=> append_sid("{$phpbb_root_path}profile.$phpEx?mode=sendpassword"),
			'U_RESEND_ACTIVATION'	=> ($board_config['require_activation'] == USER_ACTIVATION_SELF) ? append_sid("{$phpbb_root_path}profile.$phpEx?mode=resend_act") : '',
			'U_TERMS_USE'			=> append_sid("{$phpbb_root_path}profile.$phpEx?mode=terms"),
			'U_PRIVACY'				=> append_sid("{$phpbb_root_path}profile.$phpEx?mode=privacy"),

			'S_DISPLAY_FULL_LOGIN'	=> ($s_display) ? true : false,
			'S_HIDDEN_FIELDS' 		=> $s_hidden_fields,
			'S_CONFIRM_CODE'		=> false,
			'S_SIMPLE_MESSAGE'		=> '',
			'S_ADMIN_AUTH'			=> $admin,
						
			'CAPTCHA_TEMPLATE'			=> '',			
		));

		$template->pparse('body');

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else
	{
		redirect(append_sid("index.$phpEx", true));
	}

}

?>