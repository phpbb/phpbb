<?php
/***************************************************************************
 *                             ucp_register.php
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

class ucp_register extends ucp
{
	function main($id)
	{
		global $censors, $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		//
		if ($config['require_activation'] == USER_ACTIVATION_DISABLE)
		{
			trigger_error($user->lang['UCP_REGISTER_DISABLE']);
		}

		$coppa = (isset($_REQUEST['coppa'])) ? ((!empty($_REQUEST['coppa'])) ? 1 : 0) : false;
		$agreed = (!empty($_POST['agreed'])) ? 1 : 0;

		//
		if (!$agreed)
		{
			if ($coppa === false && $config['coppa_enable'])
			{
				$now = getdate();
				$coppa_birthday = $user->format_date(mktime($now['hours'] + $user->data['user_dst'], $now['minutes'], $now['seconds'], $now['mon'], $now['mday'] - 1, $now['year'] - 13), $user->lang['DATE_FORMAT']); 
				unset($now);

				$template->assign_vars(array(
					'L_COPPA_NO'		=> sprintf($user->lang['UCP_COPPA_BEFORE'], $coppa_birthday),
					'L_COPPA_YES'		=> sprintf($user->lang['UCP_COPPA_ON_AFTER'], $coppa_birthday),

					'U_COPPA_NO'		=> "ucp.$phpEx$SID&amp;mode=register&amp;coppa=0", 
					'U_COPPA_YES'		=> "ucp.$phpEx$SID&amp;mode=register&amp;coppa=1", 

					'S_SHOW_COPPA'		=> true, 
					'S_REGISTER_ACTION'	=> "ucp.$phpEx$SID&amp;mode=register")
				);
			}
			else
			{
				$template->assign_vars(array(
					'L_AGREEMENT'		=> $user->lang['UCP_AGREEMENT'], 

					'S_SHOW_COPPA'		=> false, 
					'S_REGISTER_ACTION'	=> "ucp.$phpEx$SID&amp;mode=register")
				);
			}

			$this->display($user->lang['REGISTER'], 'ucp_agreement.html');
		}

		// Check and initialize some variables if needed
		$error = $data = array();
		if (isset($_POST['submit']))
		{
			$normalise = array(
				'string' => array(
					'username'			=> $config['min_name_chars'] . ',' . $config['max_name_chars'],
					'password_confirm'	=> $config['min_pass_chars'] . ',' . $config['max_pass_chars'], 
					'new_password'		=> $config['min_pass_chars'] . ',' . $config['max_pass_chars'],
					'lang'				=> '1,50', 
					'confirm_code'		=> '6,6', 
					'email'				=> '7,60', 
					'email_confirm'		=> '7,60',
				),
				'float'	=> array('tz')
			);
			$data = $this->normalise_data($_POST, $normalise);

			$validate = array(
				'reqd'		=> array('username', 'email', 'email_confirm', 'new_password', 'password_confirm', 'lang', 'confirm_code', 'tz'), 
				'compare'	=> array(
					'password_confirm'	=> $data['new_password'], 
					'email_confirm'		=> $data['email'], 
				), 
				'match'		=> array(
					'username'	=> '#^' . preg_replace('#/{1}#', '\\', $config['allow_name_chars']) . '$#iu', 
				), 
				'function'	=> array(
					'username'	=> 'validate_username', 
					'email'		=> 'validate_email', 
				), 
			);
			$this->validate_data($data, $validate);

			// Visual Confirmation handling
			if ($config['enable_confirm'])
			{
				if (empty($_POST['confirm_id']))
				{
					$this->error[] = $user->lang['CONFIRM_CODE_WRONG'];
				}
				else
				{
					$sql = 'SELECT code 
						FROM ' . CONFIRM_TABLE . " 
						WHERE confirm_id = '" . $_POST['confirm_id'] . "' 
							AND session_id = '" . $user->data['session_id'] . "'";
					$result = $db->sql_query($sql);
		
					if ($row = $db->sql_fetchrow($result))
					{
						if ($row['code'] != $data['confirm_code'])
						{			
							$this->error[] = $user->lang['CONFIRM_CODE_WRONG'];
						}
						else
						{
							$sql = 'DELETE FROM ' . CONFIRM_TABLE . " 
								WHERE confirm_id = '" . $_POST['confirm_id'] . "' 
									AND session_id = '" . $user->data['session_id'] . "'";
							$db->sql_query($sql);
						}
					}
					else
					{		
						$this->error[] = $user->lang['CONFIRM_CODE_WRONG'];
					}
					$db->sql_freeresult($result);
				}
			}

			if (!sizeof($this->error))
			{
				$server_url = generate_board_url();

				if (($coppa || 
					$config['require_activation'] == USER_ACTIVATION_SELF || 
					$config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable'])
				{
					$user_actkey = gen_rand_string(10);
					$key_len = 54 - (strlen($server_url));
					$key_len = ($key_len > 6) ? $key_len : 6;
					$user_actkey = substr($user_actkey, 0, $key_len);
					$user_active = 0;
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
					'username'		=> $data['username'], 
					'user_password' => md5($data['new_password']),
					'user_email'	=> $data['email'],
					'user_allow_pm'	=> 1,
					'user_timezone' => (float) $data['tz'],
					'user_lang'		=> $data['lang'],
					'user_active'	=> $user_active,
					'user_actkey'	=> $user_actkey
				);

				$sql = 'INSERT INTO ' . USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
				
				$user_id = $db->sql_nextid();
		
				// Place into appropriate group, either REGISTERED(_COPPA) or INACTIVE(_COPPA) depending on config
				$group_reg = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';
				$group_inactive = ($coppa) ? 'INACTIVE_COPPA' : 'INACTIVE';
				$group_name = ($config['require_activation'] == USER_ACTIVATION_NONE) ? $group_reg : $group_inactive;
				$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending) 
					SELECT $user_id, group_id, 0 
						FROM " . GROUPS_TABLE . " 
						WHERE group_name = '$group_name' 
							AND group_type = " . GROUP_SPECIAL;
				$result = $db->sql_query($sql);

				$db->sql_transaction('commit');

				if ($coppa && $config['email_enable'])
				{
					$message = $user->lang['ACCOUNT_COPPA'];
					$email_template = 'coppa_welcome_inactive';
				}
				else if ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable'])
				{
					$message = $user->lang['ACCOUNT_INACTIVE'];
					$email_template = 'user_welcome_inactive';
				}
				else if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $config['email_enable'])
				{
					$message = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
					$email_template = 'admin_welcome_inactive';
				}
				else
				{
					$message = $user->lang['ACCOUNT_ADDED'];
					$email_template = 'user_welcome';
				}

				if ($config['email_enable'])
				{
					include($phpbb_root_path . 'includes/emailer.'.$phpEx);
					$emailer = new emailer();
				
					$emailer->template($email_template, $user->data['user_lang']);
					$emailer->replyto($config['board_contact']);
					$emailer->to($data['user_email'], $data['username']);

					$emailer->assign_vars(array(
						'SITENAME'		=> $config['sitename'],
						'WELCOME_MSG'	=> sprintf($user->lang['Welcome_subject'], $config['sitename']),
						'USERNAME'		=> $username,
						'PASSWORD'		=> $password_confirm,
						'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

						'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&k=$user_actkey")
					);

					if ($coppa)
					{
						$emailer->assign_vars(array(
							'FAX_INFO' => $config['coppa_fax'],
							'MAIL_INFO' => $config['coppa_mail'],
							'EMAIL_ADDRESS' => $email,
							'SITENAME' => $config['sitename'])
						);
					}

					$emailer->send();
					$emailer->reset();

					if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
					{
						$emailer->use_template('admin_activate', $config['default_lang']);
						$emailer->replyto($config['board_contact']);
						$emailer->to($config['board_contact']);

						$emailer->assign_vars(array(
							'USERNAME' => $username,
							'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),
			
							'U_ACTIVATE' => generate_board_url() . "/ucp.$phpEx?mode=activate&k=$user_actkey")
						);

						$emailer->send();
						$emailer->reset();
					}
				}

				if ($config['require_activation'] == USER_ACTIVATION_NONE || !$config['email_enable'])
				{
					set_config('newest_user_id', $user_id);
					set_config('newest_username', $data['username']);
					set_config('num_users', $config['num_users'] + 1, TRUE);
				}
				unset($data);

				$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'],  "<a href=\"index.$phpEx$SID\">", '</a>');
				trigger_error($message);
			}
		}

		// If an error occured we need to stripslashes on returned data
		$username		= (isset($_POST['username'])) ? stripslashes(htmlspecialchars($_POST['username'])) : '';
		$password		= (isset($_POST['new_password'])) ? stripslashes(htmlspecialchars($_POST['new_password'])) : '';
		$password_confirm = (isset($_POST['password_confirm'])) ? stripslashes(htmlspecialchars($_POST['password_confirm'])) : '';
		$email			= (isset($_POST['email'])) ? stripslashes(htmlspecialchars($_POST['email'])) : '';
		$email_confirm	= (isset($_POST['email_confirm'])) ? stripslashes(htmlspecialchars($_POST['email_confirm'])) : '';
		$lang			= (isset($_POST['lang'])) ? htmlspecialchars($_POST['lang']) : '';
		$tz				= (isset($_POST['tz'])) ? intval($_POST['tz']) : $config['board_timezone'];

		$s_hidden_fields = '<input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';

		// Visual Confirmation - Show images
		$confirm_image = '';
		if (!empty($config['enable_confirm']))
		{
			$sql = "SELECT session_id 
				FROM " . SESSIONS_TABLE; 
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$confirm_sql = '';
				do
				{
					$confirm_sql .= (($confirm_sql != '') ? ', ' : '') . "'" . $row['session_id'] . "'";
				}
				while ($row = $db->sql_fetchrow($result));
			
				$sql = 'DELETE FROM ' .  CONFIRM_TABLE . " 
					WHERE session_id NOT IN ($confirm_sql)";
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT COUNT(session_id) AS attempts 
				FROM ' . CONFIRM_TABLE . " 
				WHERE session_id = '$user->session_id'";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				if ($row['attempts'] > 3)
				{
//					trigger_error($user->lang['TOO_MANY_REGISTERS']);
				}
			}
			$db->sql_freeresult($result);

			$code = gen_rand_string(6);
			$confirm_id = md5(uniqid($user_ip));

			$sql = 'INSERT INTO ' . CONFIRM_TABLE . " (confirm_id, session_id, code) 
				VALUES ('$confirm_id', '$user->session_id', '$code')";
			$db->sql_query($sql);
			
			$confirm_image = (@extension_loaded('zlib')) ? "<img src=\"ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id\" alt=\"\" title=\"\" />" : '<img src="ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=1" alt="" title="" /><img src="ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=2" alt="" title="" /><img src="ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=3" alt="" title="" /><img src="ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=4" alt="" title="" /><img src="ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=5" alt="" title="" /><img src="ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=6" alt="" title="" />';
			$s_hidden_fields .= '<input type="hidden" name="confirm_id" value="' . $confirm_id . '" />';
		}

		// 
		$l_reg_cond = '';
		switch ($config['require_activation'])
		{
			case USER_ACTIVATION_SELF:
				$l_reg_cond = $user->lang['UCP_EMAIL_ACTIVATE'];
				break;

			case USER_ACTIVATION_ADMIN:
				$l_reg_cond = $user->lang['UCP_ADMIN_ACTIVATE'];
				break;
		}

		$user_char_ary = array('.*' => 'USERNAME_CHARS_ANY', '[\w]+' => 'USERNAME_ALPHA_ONLY', '[\w_\+\. \-\[\]]+' => 'USERNAME_ALPHA_SPACERS');

		//
		$template->assign_vars(array(
			'USERNAME'			=> $username,
			'PASSWORD'			=> $password,
			'PASSWORD_CONFIRM'	=> $password_confirm,
			'EMAIL'				=> $email,
			'EMAIL_CONFIRM'		=> $email_confirm,
			'CONFIRM_IMG'		=> $confirm_image, 
			'ERROR'				=> (sizeof($this->error)) ? implode('<br />', $this->error) : '', 

			'L_CONFIRM_EXPLAIN'		=> sprintf($user->lang['CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlentities($config['board_contact']) . '">', '</a>'), 
			'L_ITEMS_REQUIRED'		=> $l_reg_cond, 
			'L_USERNAME_EXPLAIN'	=> sprintf($user->lang[$user_char_ary[str_replace('\\\\', '\\', $config['allow_name_chars'])] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']), 
			'L_NEW_PASSWORD_EXPLAIN'=> sprintf($user->lang['NEW_PASSWORD_EXPLAIN'], $config['min_pass_chars'], $config['max_pass_chars']), 

			'S_LANG_OPTIONS'	=> language_select($lang), 
			'S_TZ_OPTIONS'		=> tz_select($tz),
			'S_CONFIRM_CODE'	=> ($config['enable_confirm']) ? true : false,
			'S_COPPA'			=> $coppa, 
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> "ucp.$phpEx$SID&amp;mode=register")
		);

		//
		$this->display($user->lang['REGISTER'], 'ucp_register.html');
	}
}

?>