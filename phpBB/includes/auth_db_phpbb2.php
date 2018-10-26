<?php
/**
*
* @package login
* @version $Id: auth_db_phpbb3.php,v 1.9 2013/06/28 15:33:47 orynider Exp $
* @copyright (c) 2002-2008 MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
* @link http://mxpcms.sourceforge.net/
*
*/

/**
* Database auth plug-in for phpBB2 Backend
*
* Authentication plug-ins is largely down to Sergey Kanareykin, and Jon Olson.
*
* This is for authentication via the integrated user table
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

/**
* Login function
*/
function login_db(&$username, &$password, $user_id = false, $increase_attempts = true)
{
	global $db, $board_config, $user, $phpEx;
		
	// User data and Lang...	
	$userdata = $user->data;
	$lang = $user->lang;
	
	$redirect = request_var('redirect', '', true);
	$redirect_url = (!empty($redirect) ? urldecode(str_replace(array('&amp;', '?', PHP_EXT . '&'), array('&', '&', PHP_EXT . '?'), $redirect)) : LOGIN_REDIRECT_PAGE);
	
	$username = is_post('username') ? phpbb_clean_username(request_post_var('username', $username)) : $username;
	$password = request_post_var('password', $password, false);
		
	// do not allow empty password
	if (!$password)
	{
		return array(
			'status' => LOGIN_ERROR_PASSWORD,
			'error_msg' => 'NO_PASSWORD_SUPPLIED',
			'user_row' => array('user_id' => ANONYMOUS),
		);
	}

	if (!$username)
	{
		return array(
			'status' => LOGIN_ERROR_USERNAME,
			'error_msg' => 'LOGIN_ERROR_USERNAME',
			'user_row' => array('user_id' => ANONYMOUS),
		);
	}	
	
	if (is_request('login') && (!$userdata['session_logged_in'] || is_post('admin')) )
	{
		$sql = "SELECT user_id, username, user_password, user_active, user_level, user_login_tries, user_last_login_try
			FROM " . USERS_TABLE . "
			WHERE username = '" . str_replace("\\'", "''", $username) . "'";

		if ( !($result = $db->sql_query($sql) ) )
		{
			message_die(GENERAL_ERROR, 'Error in obtaining userdata', '', __LINE__, __FILE__, $sql);
		}
		
		$row = $db->sql_fetchrow($result);
		
		if (!$row)
		{
			return array(
				'status' => LOGIN_ERROR_USERNAME,
				'error_msg' => 'LOGIN_ERROR_USERNAME',
				'user_row' => array('user_id' => ANONYMOUS),
			);
		}
		
		// User inactive...
		if (empty($row['user_active']))
		{
			return array(
				'status' => LOGIN_ERROR_ACTIVE,
				'error_msg' => 'ACTIVE_ERROR',
				'user_row' => $row,
			);
		}		
		
		if($row)
		{
			if( $row['user_level'] != ADMIN && $board_config['board_disable'] )
			{
				redirect(append_sid("index.$phpEx", false));
			}
			else
			{							
				// Check to see if user is allowed to login again... if his tries are exceeded
				if ($row['user_last_login_try'] && $board_config['login_reset_time'] && $board_config['max_login_attempts'] &&
					$row['user_last_login_try'] >= (time() - ($board_config['login_reset_time'] * 60)) && $row['user_login_tries'] >= $board_config['max_login_attempts'] && $userdata['user_level'] != ADMIN)
				{
					return array(
						'status' => LOGIN_ERROR_ATTEMPTS,
						'error_msg' => 'LOGIN_ATTEMPTS_EXCEEDED',
						'user_row' => array('user_id' => ANONYMOUS),
					);										
					//message_die(GENERAL_MESSAGE, sprintf($lang['Login_attempts_exceeded'], $board_config['max_login_attempts'], $board_config['login_reset_time']));
				}
				
				// If there are too much login attempts, we need to check for a confirm image
				// Every auth module is able to define what to do by itself...
				if (!empty($board_config['max_login_attempts']) && ($row['user_login_attempts'] >= $board_config['max_login_attempts']))
				{
					/*
					// Visual Confirmation handling
					$captcha =& phpbb_captcha_factory::get_instance($board_config['captcha_plugin']);
					$captcha->init(CONFIRM_LOGIN);
					$vc_response = $captcha->validate();
					if ($vc_response)
					{
						return array(
							'status' => LOGIN_ERROR_ATTEMPTS,
							'error_msg' => 'LOGIN_ERROR_ATTEMPTS',
							'user_row' => $row,
						);
					}
					*/
					redirect(append_sid('login_captcha.' . PHP_EXT . '?uid=' . $row['user_id'], true));
				}				
				
				// If the last login is more than x minutes ago, then reset the login tries/time
				if ($row['user_last_login_try'] && $board_config['login_reset_time'] && $row['user_last_login_try'] < (time() - ($board_config['login_reset_time'] * 60)))
				{									
					$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_login_tries = 0, user_last_login_try = 0 WHERE user_id = ' . $row['user_id']);
					$row['user_last_login_try'] = $row['user_login_tries'] = 0;
				}				
				
				// If the password convert flag is set we need to convert it
				// print_r('error: '. md5($password) .'='. $row['user_password'].'?');				
				// We skip the password convert before we upgrade phpBB2
				
				// Check password ...				
				if ( md5($password) == $row['user_password'] && $row['user_active'] )
				{
					$autologin = is_post('autologin');

					$admin = is_post('admin');
					$session_id = $user->session_begin($row['user_id'], $user_ip, PAGE_INDEX, FALSE, $autologin, $admin);

					// Reset login tries
					$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_login_tries = 0, user_last_login_try = 0 WHERE user_id = ' . $row['user_id']);

					if ( $session_id )
					{
						$fromurl = ( !empty($HTTP_REFERER) ) ? str_replace('&amp;', '&', htmlspecialchars($HTTP_REFERER)) : "index.$phpEx";
						$redirect_url = !is_empty_post('redirect') ? str_replace('&amp;', '&', request_post_var('redirect', "index.$phpEx", false)) : $fromurl;
						redirect(append_sid($redirect_url, false, false, $session_id));
					}
					else
					{
						message_die(CRITICAL_ERROR, "Couldn't start session : login", "", __LINE__, __FILE__);
					}
				}
				// Only store a failed login attempt for an active user - inactive users can't login even with a correct password
				elseif ( $row['user_active'] )
				{
					// Save login tries and last login
					if ($row['user_id'] != ANONYMOUS)
					{
						$sql = 'UPDATE ' . USERS_TABLE . '
							SET user_login_tries = user_login_tries + 1, user_last_login_try = ' . time() . '
							WHERE user_id = ' . $row['user_id'];
						$db->sql_query($sql);
					}

					$redirect = !is_empty_post('redirect') ? str_replace('&amp;', '&', request_post_var('redirect', "index.$phpEx", false)) : '';
					$redirect = str_replace('?', '&', $redirect);

					if (strstr(urldecode($redirect), "\n") || strstr(urldecode($redirect), "\r"))
					{
						message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
					}
					global $template;
					$template->assign_vars(array(
						'META' => "<meta http-equiv=\"refresh\" content=\"3;url=login.$phpEx?redirect=$redirect\">")
					);

					$message = $lang['Error_login'] . '<br /><br />' . sprintf($lang['Click_return_login'], "<a href=\"login.$phpEx?redirect=$redirect\">", '</a>') . '<br /><br />' .  sprintf($lang['Click_return_index'], '<a href="' . append_sid("index.$phpEx") . '">', '</a>');
					message_die(GENERAL_MESSAGE, $message);
				}
			}
		}
		else
		{
			$redirect = !is_empty_post('redirect') ? str_replace('&amp;', '&', request_post_var('redirect', "index.$phpEx", false)) : "";
			$redirect = str_replace("?", "&", $redirect);

			if (strstr(urldecode($redirect), "\n") || strstr(urldecode($redirect), "\r"))
			{
				message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
			}
			global $template;
			$template->assign_vars(array(
				'META' => "<meta http-equiv=\"refresh\" content=\"3;url=login.$phpEx?redirect=$redirect\">")
			);

			$message = $lang['Error_login'] . '<br /><br />' . sprintf($lang['Click_return_login'], "<a href=\"login.$phpEx?redirect=$redirect\">", '</a>') . '<br /><br />' .  sprintf($lang['Click_return_index'], '<a href="' . append_sid("index.$phpEx") . '">', '</a>');
			message_die(GENERAL_MESSAGE, $message);
		}
	}
}

/**
* Reset login attempts
*/
function reset_login_attempts($user_id)
{
	global $db;

	$user_id = (int) $user_id;
	$sql = 'UPDATE ' . USERS_TABLE . ' SET user_login_attempts = 0, user_last_login_attempt = 0 WHERE user_id = ' . $user_id;
	$result = $db->sql_query($sql);

	return true;
}

/**
* Increase login attempts
*/
function increase_login_attempts($user_id)
{
	global $db;

	$user_id = (int) $user_id;
	$sql = 'UPDATE ' . USERS_TABLE . ' SET user_login_attempts = user_login_attempts + 1, user_last_login_attempt = \'' . time() . '\' WHERE user_id = ' . $user_id;
	$result = $db->sql_query($sql);

	return true;
}

?>