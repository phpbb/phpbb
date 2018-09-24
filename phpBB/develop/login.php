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

$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
define('PHP_EXT', $phpEx);

define('PHPBB_PAGE_ERRORS', $phpbb_root_path . 'errors.' . $phpEx);
define('PHPBB_PAGE_FORUM', $phpbb_root_path . 'viewforum.' . $phpEx);
define('PHPBB_PAGE_LOGIN', $phpbb_root_path . 'login.' . $phpEx);
define('PHPBB_PAGE_PROFILE', $phpbb_root_path . 'profile.' . $phpEx);
define('LOGIN_REDIRECT_PAGE', $phpbb_root_path . 'index.' . $phpEx);

include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/auth_db_phpbb2.' . $phpEx);
$config['enable_social_connect'] = false;
//
// Start session management
//
$userdata = $user->session_pagestart($user_ip, PAGE_SEARCH);
$user->set_lang($user->lang, $user->help, 'common');
$lang = &$user->lang;
//$user->_init_userprefs($user->data);
init_userprefs($user->data);
//
// End session management
//

$redirect = $l_explain = $l_success = ''; 
$admin = false;
$s_display = false;

// If a bot gets redirected here is almost due to an error or a wrong page management... let's output an Error 404 code
if (!empty($user->data['is_bot']))
{
	redirect(append_sid(PHPBB_PAGE_ERRORS . '?code=404', true));
}

// session id check
$sid = request_var('sid', '');

$redirect = request_var('redirect', '', true);
$redirect_url = (!empty($redirect) ? urldecode(str_replace(array('&amp;', '?', PHP_EXT . '&'), array('&', '&', PHP_EXT . '?'), $redirect)) : LOGIN_REDIRECT_PAGE);

if (strstr($redirect_url, "\n") || strstr($redirect_url, "\r") || strstr($redirect_url, ';url'))
{
	message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
}

$available_networks = array();
if ($config['enable_social_connect'])
{
	include_once(PHPBB_ROOT_PATH . 'includes/class_social_connect.' . PHP_EXT);
	$available_networks = SocialConnect::get_available_networks();

	$login_admin = request_get_var('admin', 0);

	$social_network = request_var('social_network', '');
	$social_network_link = request_var('social_network_link', '');
	// Logging in via social network
	if (!empty($social_network) && !empty($available_networks[$social_network]))
	{
		$social_network = $available_networks[$social_network];
		$user_data = $social_network->do_login($return_url);

		if ($user_data !== null && $user_data['user_id'] > 0)
		{
			$admin = ($login_admin == 1 && $user_data['user_level'] == ADMIN) ? 1 : 0;
			$user->session_create($user_data['user_id'], $admin, 1, 1);

			$redirect_url = empty($redirect_url) ? LOGIN_REDIRECT_PAGE : $redirect_url;
			$redirect_url .= ((strpos($redirect_url, '?') === false) ? '?' : '&') . 'sid=' . $user->session_id;
			redirect(append_sid($redirect_url, true));
		}
		else
		{
			$social_network_name = $social_network->get_name();
			$social_network_name_clean = $social_network->get_name_clean();

			// Display login or register!
			$template->assign_block_vars('social_connect_button', array(
				'L_SOCIAL_CONNECT' => sprintf($lang['SOCIAL_CONNECT_LOGIN'], $social_network_name),
				'U_SOCIAL_CONNECT' => append_sid(PHPBB_PAGE_LOGIN . '?social_network=' . $social_network_name_clean),
				'IMG_SOCIAL_CONNECT' => '<img src="' . PHPBB_ROOT_PATH . 'images/social_connect/' . $social_network_name_clean . '_button_connect.png" alt="" title="" />'
				)
			);

			// Here we should display two options: new registration or link
			$url_login = append_sid(PHPBB_PAGE_LOGIN . '?social_network_link=' . $social_network_name_clean);
			$url_register = append_sid(PHPBB_PAGE_PROFILE . '?mode=register&amp;social_network=' . $social_network_name_clean);

			$message = sprintf($lang['SOCIAL_CONNECT_LINK_ACCOUNT_MSG'], $social_network_name, $social_network_name, '<a href="' . append_sid($url_login) . '">', '</a>', '<a href="' . append_sid($url_register) . '">', '</a>');
			message_die(GENERAL_MESSAGE, $message);
		}
	}
	// Linking a social network account with a board account
	elseif (!empty($social_network_link) && !empty($available_networks[$social_network_link]) && !isset($_POST['login']) && !isset($_GET['login']))
	{
		$social_network = $available_networks[$social_network_link];
		$user_data_social = $social_network->get_user_data();

		$template->assign_vars(array(
			'SOCIAL_CONNECT_LINK' => true,
			'U_PROFILE_PHOTO' => $user_data_social['u_profile_photo'],
			'USER_REAL_NAME' => $user_data_social['user_real_name'],
			'U_PROFILE_LINK' => $user_data_social['u_profile_link'],
			'SOCIAL_NETWORK_NAME' => $social_network->get_name(),
			'U_SOCIAL_NETWORK_ICON' => PHPBB_ROOT_PATH . 'images/social_connect/' . $social_network->get_name_clean() . '_icon.png',

			'S_LOGIN_ACTION' => append_sid(PHPBB_ROOT_PATH . PHPBB_PAGE_LOGIN . '?social_network_link=' . $social_network_link . '&redirect=' . urlencode($redirect_url) . '&admin=' . $login_admin))
		);
	}
	else
	{
		$template->assign_var('SOCIAL_CONNECT', true);
		foreach ($available_networks as $social_network)
		{
			$template->assign_block_vars('social_connect_button', array(
				'L_SOCIAL_CONNECT' => sprintf($lang['SOCIAL_CONNECT_LOGIN'], $social_network->get_name()),
				'U_SOCIAL_CONNECT' => append_sid(PHPBB_PAGE_LOGIN . '?social_network=' . $social_network->get_name_clean() . '&amp;redirect=' . urlencode($redirect_url) . '&amp;admin=' . $login_admin),
				'IMG_SOCIAL_CONNECT' => '<img src="' . PHPBB_ROOT_PATH . 'images/social_connect/' . $social_network->get_name_clean() . '_button_connect.png" alt="" title="" />'
				)
			);
		}
	}
}

// session id check
$sid = request_var('sid', '');

$redirect = request_var('redirect', '', true);
$redirect_url = (!empty($redirect) ? urldecode(str_replace(array('&amp;', '?', PHP_EXT . '&'), array('&', '&', PHP_EXT . '?'), $redirect)) : LOGIN_REDIRECT_PAGE);

if (strstr($redirect_url, "\n") || strstr($redirect_url, "\r") || strstr($redirect_url, ';url'))
{
	message_die(GENERAL_ERROR, 'Tried to redirect to potentially insecure url.');
}

if(isset($_POST['login']) || isset($_GET['login']) || isset($_POST['logout']) || isset($_GET['logout']))
{
	if((isset($_POST['login']) || isset($_GET['login'])) && (!$user->data['session_logged_in'] || isset($_POST['admin'])))
	{
		$username = isset($_POST['username']) ? phpbb_clean_username($_POST['username']) : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '';

		$login_result = login_db($username, $password, false, true);

		if ($login_result['status'] === LOGIN_ERROR_ATTEMPTS)
		{
			message_die(GENERAL_MESSAGE, sprintf($lang['LOGIN_ATTEMPTS_EXCEEDED'], $config['max_login_attempts'], $config['login_reset_time']));
		}

		if ($login_result['status'] === LOGIN_SUCCESS)
		{
			// Is user linking a social network account?
			if ($config['enable_social_connect'])
			{
				$available_networks = SocialConnect::get_available_networks();

				$social_network_link = request_var('social_network_link', '');
				if (!empty($social_network_link) && !empty($available_networks[$social_network_link]))
				{
					$social_network = $available_networks[$social_network_link];
					$field_name = "user_" . $social_network->get_name_clean() . "_id";
					$user_data_social = $social_network->get_user_data();

					$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $field_name . " = '" . $user_data_social[$field_name] . "' WHERE user_id = " . $login_result['user_row']['user_id'];
					$db->sql_query($sql);
				}
			}

			if(($login_result['user_row']['user_level'] != ADMIN) && !empty($config['board_disable']))
			{
				redirect(append_sid(PHPBB_PAGE_FORUM, true));
			}
			else
			{
				// CrackerTracker v5.x
				if ($config['ctracker_login_history'] == 1)
				{
					$ctracker_config->update_login_history($login_result['user_row']['user_id']);
				}

				if ($config['ctracker_login_ip_check'] == 1)
				{
					$ctracker_config->set_user_ip($login_result['user_row']['user_id']);
				}
				// CrackerTracker v5.x

				$set_admin = (isset($_POST['admin'])) ? 1 : 0;
				$persist_login = (isset($_POST['autologin'])) ? 1 : 0;
				$viewonline = (($_POST['online_status'] == 'hidden') ? 0 : 1);

				if (isset($_POST['online_status']) && (($_POST['online_status'] == 'hidden') || ($_POST['online_status'] == 'visible')))
				{
					$sql = 'UPDATE ' . USERS_TABLE . ' SET user_allow_viewonline = ' . $viewonline . ' WHERE user_id = ' . $login_result['user_row']['user_id'];
					$db->sql_return_on_error(true);
					$db->sql_query($sql);
					$db->sql_return_on_error(false);
				}

				$user->session_create($login_result['user_row']['user_id'], $set_admin, $persist_login, $viewonline);

				if(!empty($user->session_id))
				{
					$redirect_url = empty($redirect_url) ? LOGIN_REDIRECT_PAGE : $redirect_url;
					redirect(append_sid($redirect_url, true));
				}
				else
				{
					message_die(CRITICAL_ERROR, "Couldn't start session: login", "", __LINE__, __FILE__);
				}
			}
		}
		else
		{
			if (($login_result['status'] === LOGIN_ERROR_USERNAME) || ($login_result['status'] === LOGIN_ERROR_PASSWORD) || ($login_result['status'] === LOGIN_ERROR_ACTIVE))
			{
				if ($login_result['error_msg'] === 'LOGIN_ERROR_PASSWORD')
				{
					// CrackerTracker v5.x
					if (!class_exists('log_manager'))
					{
						include(PHPBB_ROOT_PATH . 'includes/ctracker/classes/class_log_manager.' . PHP_EXT);
					}
					$logfile = new log_manager();
					$logfile->prepare_log($login_result['user_row']['username']);
					$logfile->write_general_logfile($config['ctracker_logsize_logins'], 4);
					unset($logfile);
					// CrackerTracker v5.x
				}
				$error_message = ($login_result['error_msg'] === 'NO_PASSWORD_SUPPLIED') ? $lang[$login_result['error_msg']] : sprintf($lang[$login_result['error_msg']], '<a href="' . append_sid(CMS_PAGE_CONTACT_US) . '">', '</a>');
				message_die(GENERAL_MESSAGE, $error_message);
			}

			meta_refresh(3, (PHPBB_PAGE_LOGIN . '?redirect=' . htmlspecialchars($redirect_url)));

			$message = $lang['Error_login'] . '<br /><br />' . sprintf($lang['Click_return_login'], '<a href="' . PHPBB_PAGE_LOGIN . '?redirect=' . htmlspecialchars($redirect_url) . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . append_sid(PHPBB_PAGE_FORUM) . '">', '</a>');

			message_die(GENERAL_MESSAGE, $message);
		}
	}
	elseif (is_request('logout') && $userdata['session_logged_in'] )
	{
		// session id check
		if ($sid == '' || $sid != $userdata['session_id'])
		{
			message_die(GENERAL_ERROR, 'Invalid_session');
			//trigger_error('INVALID_SESSION');			
		}

		if ( $userdata['session_logged_in'] )
		{
			$user->session_end($userdata['session_id'], $userdata['user_id']);
			//$user->session_kill();			
		}

		if (!is_empty_request('redirect'))
		{
			$fromurl = ( !empty($HTTP_REFERER) ) ? str_replace('&amp;', '&', htmlspecialchars($HTTP_REFERER)) : "index.$phpEx";
			$redirect_url = !is_empty_post('redirect') ? str_replace('&amp;', '&', request_post_var('redirect', "index.$phpEx", false)) : $fromurl;
			redirect(append_sid($redirect_url, false, false, $session_id));
		}
		else
		{
			redirect(append_sid("index.$phpEx", false));
		}
	}
	else
	{
		$redirect_url = !is_empty_post('redirect') ? str_replace('&amp;', '&', request_post_var('redirect', "index.$phpEx", false)) : "index.$phpEx";
		//$redirect_url = empty($redirect_url) ? LOGIN_REDIRECT_PAGE : $redirect_url;
		redirect(append_sid($redirect_url, true));
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