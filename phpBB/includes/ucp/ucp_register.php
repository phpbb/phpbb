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
* ucp_register
* Board registration
*/
class ucp_register
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		//
		if ($config['require_activation'] == USER_ACTIVATION_DISABLE)
		{
			trigger_error($user->lang['UCP_REGISTER_DISABLE']);
		}

		include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);

		// Do not alter this first one to use request_var!
		$confirm_id = request_var('confirm_id', '');
		$coppa		= (isset($_REQUEST['coppa'])) ? ((!empty($_REQUEST['coppa'])) ? 1 : 0) : false;
		$agreed		= (!empty($_POST['agreed'])) ? 1 : 0;
		$submit		= (isset($_POST['submit'])) ? true : false;
		$change_lang = request_var('change_lang', '');

		if ($change_lang)
		{
			$submit = false;
			$lang = $change_lang;
			$user->lang_name = $lang = $change_lang;
			$user->lang_path = $phpbb_root_path . 'language/' . $lang . '/';
			$user->lang = array();
			$user->add_lang(array('common', 'ucp'));
		}

		$cp = new custom_profile();

		$error = $data = $cp_data = $cp_error = array();

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

					'U_COPPA_NO'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=register&amp;coppa=0",
					'U_COPPA_YES'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=register&amp;coppa=1",

					'S_SHOW_COPPA'		=> true,
					'S_REGISTER_ACTION'	=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=register")
				);
			}
			else
			{
				$template->assign_vars(array(
					'L_TERMS_OF_USE'	=> $user->lang['TERMS_OF_USE_CONTENT'],

					'S_SHOW_COPPA'		=> false,
					'S_REGISTER_ACTION'	=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=register")
				);
			}

			$this->tpl_name = 'ucp_agreement';
			return;
		}

		$var_ary = array(
			'username'			=> (string) '',
			'password_confirm'	=> (string) '',
			'new_password'		=> (string) '',
			'cur_password'		=> (string) '',
			'email'				=> (string) '',
			'email_confirm'		=> (string) '',
			'confirm_code'		=> (string) '',
			'lang'				=> (string) $config['default_lang'],
			'tz'				=> (float) $config['board_timezone'],
		);

		// If we change the language inline, we do not want to display errors, but pre-fill already filled out values
		if ($change_lang)
		{
			foreach ($var_ary as $var => $default)
			{
				$$var = request_var($var, $default);
			}
		}

		// Check and initialize some variables if needed
		if ($submit)
		{
			foreach ($var_ary as $var => $default)
			{
				$data[$var] = request_var($var, $default);
			}

			$var_ary = array(
				'username'			=> array(
					array('string', false, $config['min_name_chars'], $config['max_name_chars']),
					array('username')),
				'password_confirm'	=> array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
				'new_password'		=> array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
				'email'				=> array(
					array('string', false, 6, 60),
					array('email')),
				'email_confirm'		=> array('string', false, 6, 60),
				'confirm_code'		=> array('string', !$config['enable_confirm'], 6, 6),
				'tz'				=> array('num', false, -13, 13),
				'lang'				=> array('match', false, '#^[a-z_]{2,}$#i'),
			);

			$error = validate_data($data, $var_ary);
			extract($data);
			unset($data);

			// Replace "error" strings with their real, localised form
			$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);

			// validate custom profile fields
			$cp->submit_cp_field('register', $user->get_iso_lang_id(), $cp_data, $error);

			// Visual Confirmation handling
			$wrong_confirm = false;
			if ($config['enable_confirm'])
			{
				if (!$confirm_id)
				{
					$error[] = $user->lang['CONFIRM_CODE_WRONG'];
					$wrong_confirm = true;
				}
				else
				{
					$sql = 'SELECT code
						FROM ' . CONFIRM_TABLE . "
						WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
							AND session_id = '" . $db->sql_escape($user->session_id) . "'";
					$result = $db->sql_query($sql);

					if ($row = $db->sql_fetchrow($result))
					{
						if ($row['code'] != $confirm_code)
						{
							$error[] = $user->lang['CONFIRM_CODE_WRONG'];
							$wrong_confirm = true;
						}
						else
						{
							$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
								WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
									AND session_id = '" . $db->sql_escape($user->session_id) . "'";
							$db->sql_query($sql);
						}
					}
					else
					{
						$error[] = $user->lang['CONFIRM_CODE_WRONG'];
						$wrong_confirm = true;
					}
					$db->sql_freeresult($result);
				}
			}

			if (!sizeof($error))
			{
				if ($new_password != $password_confirm)
				{
					$error[] = 'NEW_PASSWORD_ERROR';
				}

				if ($email != $email_confirm)
				{
					$error[] = 'NEW_EMAIL_ERROR';
				}
			}
			
			if (!sizeof($error))
			{
				$server_url = generate_board_url();

				// Which group by default?
				$group_reg = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';
				$group_inactive = ($coppa) ? 'INACTIVE_COPPA' : 'INACTIVE';
				$group_name = ($config['require_activation'] == USER_ACTIVATION_NONE) ? $group_reg : $group_inactive;

				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = '$group_name'
						AND group_type = " . GROUP_SPECIAL;
				$result = $db->sql_query($sql);

				if (!($row = $db->sql_fetchrow($result)))
				{
					trigger_error($user->lang['NO_GROUP']);
				}
				$db->sql_freeresult($result);

				$group_id = $row['group_id'];

				if (($coppa ||
					$config['require_activation'] == USER_ACTIVATION_SELF ||
					$config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable'])
				{
					$user_actkey = gen_rand_string(10);
					$key_len = 54 - (strlen($server_url));
					$key_len = ($key_len > 6) ? $key_len : 6;
					$user_actkey = substr($user_actkey, 0, $key_len);
					$user_type = USER_INACTIVE;
				}
				else
				{
					$user_type = USER_NORMAL;
					$user_actkey = '';
				}

				// Begin transaction ... should this screw up we can rollback
				$db->sql_transaction();

				$sql_ary = array(
					'username'			=> $username,
					'user_password'		=> md5($new_password),
					'user_email'		=> $email,
					'user_email_hash'	=> (int) crc32(strtolower($email)) . strlen($email),
					'group_id'			=> (int) $group_id,
					'user_timezone'		=> (float) $tz,
					'user_lang'			=> $lang,
					'user_allow_pm'		=> 1,
					'user_type'			=> $user_type,
					'user_actkey'		=> $user_actkey,
					'user_ip'			=> $user->ip,
					'user_regdate'		=> time(),
				);

				$sql = 'INSERT INTO ' . USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);

				$user_id = $db->sql_nextid();

				// Insert Custom Profile Fields
				if (sizeof($cp_data))
				{
					$cp_data['user_id'] = (int) $user_id;
					$sql = 'INSERT INTO ' . PROFILE_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $cp->build_insert_sql_array($cp_data));
					$db->sql_query($sql);
				}

				// Place into appropriate group, either REGISTERED(_COPPA) or INACTIVE(_COPPA) depending on config
				$sql = 'INSERT INTO ' . USER_GROUP_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'user_id'		=> (int) $user_id,
					'group_id'		=> (int) $group_id,
					'user_pending'	=> 0)
				);
				$db->sql_query($sql);

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
					include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

					$messenger = new messenger(false);

					$messenger->template($email_template, $lang);

					$messenger->replyto($config['board_contact']);
					$messenger->to($email, $username);

					$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
					$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
					$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
					$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

					$messenger->assign_vars(array(
						'SITENAME'		=> $config['sitename'],
						'WELCOME_MSG'	=> sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename']),
						'USERNAME'		=> $username,
						'PASSWORD'		=> $password_confirm,
						'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

						'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
					);

					if ($coppa)
					{
						$messenger->assign_vars(array(
							'FAX_INFO'		=> $config['coppa_fax'],
							'MAIL_INFO'		=> $config['coppa_mail'],
							'EMAIL_ADDRESS' => $email,
							'SITENAME'		=> $config['sitename'])
						);
					}

					$messenger->send(NOTIFY_EMAIL);

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
								'USERNAME'		=> $username,
								'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

								'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
							);

							$messenger->send($row['user_notify_type']);
						}
						$db->sql_freeresult($result);
					}
				}

				if ($user_type == USER_NORMAL || !$config['email_enable'])
				{
					set_config('newest_user_id', $user_id);
					set_config('newest_username', $username);
					set_config('num_users', $config['num_users'] + 1, true);
				}
				unset($data);

				$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'],  "<a href=\"index.$phpEx$SID\">", '</a>');
				trigger_error($message);
			}
		}

		$s_hidden_fields = build_hidden_fields(array(
			'agreed'	=> 'true', 
			'coppa'		=> $coppa,
			'change_lang'	=> 0)
		);

		$confirm_image = '';
		// Visual Confirmation - Show images
		if ($config['enable_confirm'])
		{
			if (!$change_lang)
			{
				$sql = 'SELECT session_id
					FROM ' . SESSIONS_TABLE;
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					$sql_in = array();
					do
					{
						$sql_in[] = "'" . $db->sql_escape($row['session_id']) . "'";
					}
					while ($row = $db->sql_fetchrow($result));

					$sql = 'DELETE FROM ' .  CONFIRM_TABLE . '
						WHERE session_id NOT IN (' . implode(', ', $sql_in) . ')';
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT COUNT(session_id) AS attempts
					FROM ' . CONFIRM_TABLE . "
					WHERE session_id = '" . $db->sql_escape($user->session_id) . "'";
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					if ($config['max_reg_attempts'] && $row['attempts'] >= $config['max_reg_attempts'])
					{
						trigger_error($user->lang['TOO_MANY_REGISTERS']);
					}
				}
				$db->sql_freeresult($result);
		
				$code = gen_rand_string(6);
				$confirm_id = md5(uniqid($user->ip));

				$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'confirm_id'	=> (string) $confirm_id,
					'session_id'	=> (string) $user->session_id,
					'code'			=> (string) $code)
				);
				$db->sql_query($sql);
			}

			$confirm_image = (@extension_loaded('zlib')) ? "<img src=\"ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id\" alt=\"\" title=\"\" />" : "<img src=\"ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=1\" alt=\"\" title=\"\" /><img src=\"ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=2\" alt=\"\" title=\"\" /><img src=\"ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=3\" alt=\"\" title=\"\" /><img src=\"ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=4\" alt=\"\" title=\"\" /><img src=\"ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=5\" alt=\"\" title=\"\" /><img src=\"ucp.$phpEx$SID&amp;mode=confirm&amp;id=$confirm_id&amp;c=6\" alt=\"\" title=\"\" />";
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

		$lang = (isset($lang)) ? $lang : $config['default_lang'];
		$tz = (isset($tz)) ? $tz : $config['board_timezone'];

		//
		$template->assign_vars(array(
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'USERNAME'			=> (isset($username)) ? $username : '',
			'PASSWORD'			=> (isset($password)) ? $password : '',
			'PASSWORD_CONFIRM'	=> (isset($password_confirm)) ? $password_confirm : '',
			'EMAIL'				=> (isset($email)) ? $email : '',
			'EMAIL_CONFIRM'		=> (isset($email_confirm)) ? $email_confirm : '',
			'CONFIRM_IMG'		=> $confirm_image,

			'L_CONFIRM_EXPLAIN'		=> sprintf($user->lang['CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlentities($config['board_contact']) . '">', '</a>'),
			'L_ITEMS_REQUIRED'		=> $l_reg_cond,
			'L_USERNAME_EXPLAIN'	=> sprintf($user->lang[$user_char_ary[str_replace('\\\\', '\\', $config['allow_name_chars'])] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']),
			'L_NEW_PASSWORD_EXPLAIN'=> sprintf($user->lang['NEW_PASSWORD_EXPLAIN'], $config['min_pass_chars'], $config['max_pass_chars']),

			'S_LANG_OPTIONS'	=> language_select($lang),
			'S_TZ_OPTIONS'		=> tz_select($tz),
			'S_CONFIRM_CODE'	=> ($config['enable_confirm']) ? true : false,
			'S_COPPA'			=> $coppa,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=register")
		);

		//
		$user->profile_fields = array();

		// Generate profile fields -> Template Block Variable profile_fields
		$cp->generate_profile_fields('register', $user->get_iso_lang_id());

		//
		$this->tpl_name = 'ucp_register';
	}
}

?>