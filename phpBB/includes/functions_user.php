<?php
/***************************************************************************
 *                           functions_user.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

// Does supplementary validation of optional profile fields. This
// expects common stuff like trim() and strip_tags() to have already
// been run. Params are passed by-ref, so we can set them to the empty
// string if they fail.
function validate_optional_fields(&$icq, &$aim, &$msnm, &$yim, &$website, &$location, &$occupation, &$interests, &$sig)
{
	$check_var_length = array('aim', 'msnm', 'yim', 'location', 'occupation', 'interests', 'sig');

	for($i = 0; $i < count($check_var_length); $i++)
	{
		if (strlen($$check_var_length[$i]) < 2)
		{
			$$check_var_length[$i] = '';
		}
	}

	// ICQ number has to be only numbers.
	if (!preg_match('/^[0-9]+$/', $icq))
	{
		$icq = '';
	}

	// website has to start with http://, followed by something with length at least 3 that
	// contains at least one dot.
	if ($website != '')
	{
		if (!preg_match('#^http[s]?:\/\/#i', $website))
		{
			$website = 'http://' . $website;
		}

		if (!preg_match('#^http[s]?\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $website))
		{
			$website = '';
		}
	}

	return;
}

// Handles manipulation of user data. Primary used in registration
// and user profile manipulation
class userdata extends user
{
	var $error = array();
	
	function add_user($coppa)
	{
		global $config, $db, $user;

		$userdata = $this->prepare_data(true);
		
		if (!sizeof($this->error))
		{
			if ($coppa && ($config['require_activation'] == USER_ACTIVATION_SELF || 
				$config['require_activation'] == USER_ACTIVATION_ADMIN))
			{
				$user_actkey = ucp::gen_rand_string(10);
				$key_len = 54 - (strlen($server_url));
				$key_len = ($key_len > 6) ? $key_len : 6;
	
				$user_actkey = substr($user_actkey, 0, $key_len);
				$user_active = 0;
	
				if ($user->data['user_id'] != ANONYMOUS)
				{
					$user->destroy();
				}
			}
			else
			{
				$user_active = 1;
				$user_actkey = '';
			}
	
			// Begin transaction ... should this screw up we can rollback
			$db->sql_transaction();
	
			$sql_ary = array(
				'user_ip'		=> $user->ip, 
				'user_regdate'	=> time(),
				'username'		=> $userdata['username'], 
				'user_password' => $userdata['password'],
				'user_email'	=> $userdata['email'],
				'user_allow_pm'	=> 1,
				'user_timezone' => (float) $userdata['timezone'],
				'user_lang'		=> $userdata['language'],
				'user_active'	=> $user_active,
				'user_actkey'	=> $user_actkey
			);
	
			$sql = 'INSERT INTO ' . USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
			
			$user_id = $db->sql_nextid();
	
			// Place into appropriate group, either REGISTERED or INACTIVE depending on config
			$group_name = ($config['require_activation'] == USER_ACTIVATION_NONE) ? 'REGISTERED' : 'INACTIVE';
			$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending) 
				SELECT $user_id, group_id, 0 
					FROM " . GROUPS_TABLE . " 
					WHERE group_name = '$group_name' 
						AND group_type = " . GROUP_SPECIAL;
			$result = $db->sql_query($sql);
	
			$db->sql_transaction('commit');
		
			if ($coppa)
			{
				$message = $user->lang['ACCOUNT_COPPA'];
				$email_template = 'coppa_welcome_inactive';
			}
			else if ($config['require_activation'] == USER_ACTIVATION_SELF)
			{
				$message = $user->lang['ACCOUNT_INACTIVE'];
				$email_template = 'user_welcome_inactive';
			}
			else if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				$message = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
				$email_template = 'admin_welcome_inactive';
			}
			else
			{
				$message = $user->lang['ACCOUNT_ADDED'];
				$email_template = 'user_welcome';
			}
	
	/*
			include($phpbb_root_path . 'includes/emailer.'.$phpEx);
			$emailer = new emailer($config['smtp_delivery']);
	
			// Should we just define this within the email class?
			$email_headers = "From: " . $config['board_email'] . "\nReturn-Path: " . $config['board_email'] . "\r\n";
	
			$emailer->use_template($email_template, $user->data['user_lang']);
			$emailer->email_address($email);
			$emailer->set_subject();//sprintf($user->lang['Welcome_subject'], $config['sitename'])
			$emailer->extra_headers($email_headers);
	
			if ($coppa)
			{
				$emailer->assign_vars(array(
					'SITENAME' => $config['sitename'],
					'WELCOME_MSG' => sprintf($user->lang['Welcome_subject'], $config['sitename']),
					'USERNAME' => $username,
					'PASSWORD' => $password_confirm,
					'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),
	
					'U_ACTIVATE' => $server_url . '?mode=activate&act_key=' . $user_actkey,
					'FAX_INFO' => $config['coppa_fax'],
					'MAIL_INFO' => $config['coppa_mail'],
					'EMAIL_ADDRESS' => $email,
					'SITENAME' => $config['sitename']));
			}
			else
			{
				$emailer->assign_vars(array(
					'SITENAME' => $config['sitename'],
					'WELCOME_MSG' => sprintf($user->lang['Welcome_subject'], $config['sitename']),
					'USERNAME' => $username,
					'PASSWORD' => $password_confirm,
					'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),
					'U_ACTIVATE' => $server_url . '?mode=activate&act_key=' . $user_actkey)
				);
			}
	
			$emailer->send();
			$emailer->reset();
	
			if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				$emailer->use_template('admin_activate', stripslashes($user_lang));
				$emailer->email_address($config['board_email']);
				$emailer->set_subject(); //$user->lang['New_account_subject']
				$emailer->extra_headers($email_headers);
	
				$emailer->assign_vars(array(
					'USERNAME' => $username,
					'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),
	
					'U_ACTIVATE' => $server_url . '?mode=activate&act_key=' . $user_actkey)
				);
				$emailer->send();
				$emailer->reset();
			}
	*/
			
			if ($config['require_activation'] == USER_ACTIVATION_NONE)
			{
				set_config('newest_user_id', $userdata['user_id']);
				set_config('newest_username', $userdata['username']);
				set_config('num_users', $config['num_users'] + 1, TRUE);
			}
			unset($userdata);
				
			return $message;
		}

		return false;
	}
	
	function prepare_data($registration = FALSE)
	{
		global $db, $user, $config;
	
		$userdata = &$_POST;

		$strip_var_list = array('username' => 'username', 'email' => 'email'); 
	
		foreach ($strip_var_list as $var => $param)
		{
			if (!empty($userdata[$param]))
			{
				$userdata[$var] = trim(strip_tags($userdata[$param]));
			}
		}
				
		$trim_var_list = array('password_current' => 'cur_password', 'password' => 'new_password', 'password_confirm' => 'password_confirm');
	
		foreach ($trim_var_list as $var => $param)
		{
			if (!empty($userdata[$param]))
			{
				$userdata[$var] = trim($userdata[$param]);
			}
		}
	
		$userdata['username'] = str_replace('&nbsp;', '', $userdata['username']);
		$userdata['email'] = htmlspecialchars($userdata['email']);
	
		// Run some validation on the optional fields. These are pass-by-ref, so they'll be changed to
		// empty strings if they fail.
		//validate_optional_fields($icq, $aim, $msn, $yim, $website, $location, $occupation, $interests, $signature);
	
		$userdata['viewemail'] = (isset($userdata['viewemail'])) ? (($userdata['viewemail']) ? TRUE : 0) : 0;
		$userdata['hideonline'] = (isset($userdata['hideonline'])) ? (($userdata['hideonline']) ? 0 : TRUE) : TRUE;
		$userdata['notifyreply'] = (isset($userdata['notifyreply'])) ? (($userdata['notifyreply']) ? TRUE : 0) : 0;
		$userdata['notifypm'] = (isset($userdata['notifypm'])) ? (($userdata['notifypm']) ? TRUE : 0) : TRUE;
		$userdata['popup_pm'] = (isset($userdata['popup_pm'])) ? (($userdata['popup_pm']) ? TRUE : 0) : TRUE;
	
		$userdata['attachsig'] = (isset($userdata['attachsig'])) ? (($userdata['attachsig']) ? TRUE : 0) : $config['allow_sig'];
	
		$userdata['allowhtml'] = (isset($userdata['allowhtml'])) ? (($userdata['allowhtml']) ? TRUE : 0) : $config['allow_html'];
		$userdata['allowbbcode'] = (isset($userdata['allowbbcode'])) ? (($userdata['allowbbcode']) ? TRUE : 0) : $config['allow_bbcode'];
		$userdata['allowsmilies'] = (isset($userdata['allowsmilies'])) ? (($userdata['allowsmilies']) ? TRUE : 0) : $config['allow_smilies'];
	
		$userdata['style'] = (isset($userdata['style'])) ? intval($userdata['style']) : $config['default_style'];
	
		if (!empty($userdata['language']))
		{
			if (preg_match('#^[a-z_]+$#i', $userdata['language']))
			{
				$userdata['language'] = $userdata['language'];
			}
			else
			{
				$this->error[] = $user->lang['Fields_empty'];
			}
		}
		else
		{
			$userdata['language'] = $config['default_lang'];
		}
	
		$userdata['timezone'] = (isset($userdata['timezone'])) ? doubleval($userdata['timezone']) : $config['board_timezone'];
		$userdata['dateformat'] = (!empty($userdata['dateformat'])) ? trim($userdata['dateformat']) : $config['default_dateformat'];
	
		if (empty($userdata['username']) || empty($userdata['password']) || empty($userdata['password_confirm']) || empty($userdata['email']))
		{
			$this->error[] = $user->lang['Fields_empty'];
		}
	
		if (!empty($userdata['password']) && !empty($userdata['password_confirm']))
		{
			if ($userdata['password'] != $userdata['password_confirm'])
			{
				$this->error[] = $user->lang['Password_mismatch'];
			}
			else if (strlen($userdata['password']) > 32)
			{
				$this->error[] = $user->lang['Password_long'];
			}
			else
			{
				if (!sizeof($this->error))
				{
					$userdata['password'] = md5($userdata['password']);
					$passwd_sql = "user_password = '$password', ";
				}
			}
		}
		else if ((empty($userdata['password']) && !empty($userdata['password_confirm'])) || (!empty($userdata['password']) && empty($userdata['password_confirm'])))
		{
			$this->error[] = $user->lang['Password_mismatch'];
		}
		else
		{
			$userdata['password'] = $user->data['user_password'];
		}
	
		// Do a ban check on this email address
		if ($userdata['email'] != $user->data['user_email'] || $registration)
		{
			if (($result = $this->validate_email($userdata['email'])) != false)
			{
				$userdata['email'] = $user->data['user_email'];

				$this->error[] = $result;
			}
		}
	
		if (empty($userdata['username']))
		{
			$this->error[] = $user->lang['Username_disallowed'];
		}
		else
		{
			if (($result = $this->validate_username($userdata['username'])) != false)
			{
				$this->error[] = $result;
			}
		}
	
		// Visual Confirmation handling
		if ($config['enable_confirm'] && $registration)
		{
			if (empty($userdata['confirm_id']))
			{
				$this->error[] = $user->lang['CONFIRM_CODE_WRONG'];
			}
			else
			{
				$sql = "SELECT code 
					FROM " . CONFIRM_TABLE . " 
					WHERE confirm_id = '" . $userdata['confirm_id'] . "' 
						AND session_id = '" . $user->data['session_id'] . "'";
				$result = $db->sql_query($sql);
	
				if ($row = $db->sql_fetchrow($result))
				{
					if ($row['code'] != $userdata['confirm_code'])
					{			
						$this->error[] = $user->lang['CONFIRM_CODE_WRONG'];
					}
				}
				else
				{		
					$this->error[] = $user->lang['CONFIRM_CODE_WRONG'];
				}
				$db->sql_freeresult($result);
	
				$sql = "DELETE FROM " . CONFIRM_TABLE . " 
					WHERE confirm_id = '" . $userdata['confirm_id'] . "' 
						AND session_id = '" . $user->data['session_id'] . "'";
				$db->sql_query($sql);
			}
		}

		return $userdata;
	}
	
	function modify_userdata($userdata)
	{
		
		
	}
	
	// Check to see if the username has been taken, or if it is disallowed.
	// Also checks if it includes the " character, which we don't allow in usernames.
	// Used for registering, changing names, and posting anonymously with a username
	function validate_username($username)
	{
		global $db, $user;
	
		// Clean up username ... convert any entities into normal
		// text, remove excess spaces, then escape it
		$username = strtr(trim($username), array_flip(get_html_translation_table(HTML_ENTITIES)));
		$username = preg_replace('#\s{2,}#', '', $username);

		if (strlen(htmlspecialchars($username)) > 60)
		{
			return 'USERNAME_LONG';
		}

		// Don't allow " in username.
		if (strstr($username, '"'))
		{
			return 'USERNAME_INVALID';
		}
	
		$username = $db->sql_escape($username);

		$sql = "SELECT username
			FROM " . USERS_TABLE . "
			WHERE LOWER(username) = '" . strtolower($username) . "'";
		$result = $db->sql_query($sql);
	
		if (($row = $db->sql_fetchrow($result)) && $row['username'] != $user->data['username'])
		{
			return 'USERNAME_TAKEN';
		}
		$db->sql_freeresult($result);
	
		$sql = "SELECT group_name
			FROM " . GROUPS_TABLE . "
			WHERE LOWER(group_name) = '" . strtolower($username) . "'";
		$result = $db->sql_query($sql);
	
		if ($row = $db->sql_fetchrow($result))
		{
			return 'USERNAME_TAKEN';
		}
		$db->sql_freeresult($result);

		$sql = "SELECT disallow_username
			FROM " . DISALLOW_TABLE;
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			if (preg_match('#(' . str_replace('\*', '.*?', preg_quote($row['disallow_username'], '#')) . ')#i', $username))
			{
				return 'USERNAME_DISALLOWED';
			}
		}
		$db->sql_freeresult($result);

		$sql = "SELECT word
			FROM  " . WORDS_TABLE;
		$result = $db->sql_query($sql);
	
		while ($row = $db->sql_fetchrow($result))
		{
			if (preg_match('#(' . str_replace('\*', '.*?', preg_quote($row['word'], '#')) . ')#i', $username))
			{
				return 'USERNAME_DISALLOWED';
			}
		}
		$db->sql_freeresult($result);

		return false;
	}
	
	// Check to see if email address is banned or already present in the DB
	function validate_email($email)
	{
		global $db, $user;
	
		if ($email != '')
		{
			if (preg_match('#^[a-z0-9\.\-_\+]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$#is', $email))
			{
				$sql = "SELECT ban_email
					FROM " . BANLIST_TABLE;
				$result = $db->sql_query($sql);
	
				while ($row = $db->sql_fetchrow($result))
				{
					if (preg_match('#^' . str_replace('*', '.*?', $row['ban_email']) . '$#is', $email))
					{
						return 'EMAIL_BANNED';
					}
				}
				$db->sql_freeresult($result);
	
				$sql = "SELECT user_email
					FROM " . USERS_TABLE . "
					WHERE user_email = '" . $db->sql_escape($email) . "'";
				$result = $db->sql_query($sql);
	
				if ($row = $db->sql_fetchrow($result))
				{
					return 'EMAIL_TAKEN';
				}
				$db->sql_freeresult($result);
	
				return false;
			}
		}
	
		return 'EMAIL_INVALID';
	}
}

?>