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
* ucp_register
* Board registration
* @package ucp
*/
class ucp_register
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;

		//
		if ($config['require_activation'] == USER_ACTIVATION_DISABLE)
		{
			trigger_error('UCP_REGISTER_DISABLE');
		}

		include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);

		$confirm_id		= request_var('confirm_id', '');
		$coppa			= (isset($_REQUEST['coppa'])) ? ((!empty($_REQUEST['coppa'])) ? 1 : 0) : false;
		$agreed			= (!empty($_POST['agreed'])) ? 1 : 0;
		$submit			= (isset($_POST['submit'])) ? true : false;
		$change_lang	= request_var('change_lang', '');

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

					'U_COPPA_NO'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register&amp;coppa=0'),
					'U_COPPA_YES'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register&amp;coppa=1'),

					'S_SHOW_COPPA'		=> true,
					'S_REGISTER_ACTION'	=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'))
				);
			}
			else
			{
				$template->assign_vars(array(
					'L_TERMS_OF_USE'	=> sprintf($user->lang['TERMS_OF_USE_CONTENT'], $config['sitename'], generate_board_url()),

					'S_SHOW_COPPA'		=> false,
					'S_REGISTRATION'	=> true,
					'S_REGISTER_ACTION'	=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'))
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
				$$var = request_var($var, $default, true);
			}
		}

		// Check and initialize some variables if needed
		if ($submit)
		{
			foreach ($var_ary as $var => $default)
			{
				$data[$var] = request_var($var, $default, true);
			}

			$var_ary = array(
				'username'			=> array(
					array('string', false, $config['min_name_chars'], $config['max_name_chars']),
					array('username')),
				'new_password'		=> array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
				'password_confirm'	=> array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
				'email'				=> array(
					array('string', false, 6, 60),
					array('email')),
				'email_confirm'		=> array('string', false, 6, 60),
				'confirm_code'		=> array('string', !$config['enable_confirm'], 5, 8),
				'tz'				=> array('num', false, -14, 14),
				'lang'				=> array('match', false, '#^[a-z_\-]{2,}$#i'),
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
							AND session_id = '" . $db->sql_escape($user->session_id) . "'
							AND confirm_type = " . CONFIRM_REG;
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if ($row)
					{
						if (strcasecmp($row['code'], $confirm_code) === 0)
						{
							$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
								WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
									AND session_id = '" . $db->sql_escape($user->session_id) . "'
									AND confirm_type = " . CONFIRM_REG;
							$db->sql_query($sql);
						}
						else
						{
							$error[] = $user->lang['CONFIRM_CODE_WRONG'];
							$wrong_confirm = true;
						}
					}
					else
					{
						$error[] = $user->lang['CONFIRM_CODE_WRONG'];
						$wrong_confirm = true;
					}
				}
			}

			if (!sizeof($error))
			{
				if ($new_password != $password_confirm)
				{
					$error[] = $user->lang['NEW_PASSWORD_ERROR'];
				}

				if ($email != $email_confirm)
				{
					$error[] = $user->lang['NEW_EMAIL_ERROR'];
				}
			}

			if (!sizeof($error))
			{
				$server_url = generate_board_url();

				// Which group by default?
				$group_reg = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';
				$group_inactive = ($coppa) ? 'INACTIVE_COPPA' : 'INACTIVE';
				$group_name = ($config['require_activation'] == USER_ACTIVATION_NONE || !$config['email_enable']) ? $group_reg : $group_inactive;

				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = '" . $db->sql_escape($group_name) . "'
						AND group_type = " . GROUP_SPECIAL;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error('NO_GROUP');
				}

				$group_id = $row['group_id'];

				if (($coppa ||
					$config['require_activation'] == USER_ACTIVATION_SELF ||
					$config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable'])
				{
					$user_actkey = gen_rand_string(10);
					$key_len = 54 - (strlen($server_url));
					$key_len = ($key_len < 6) ? 6 : $key_len;
					$user_actkey = substr($user_actkey, 0, $key_len);
					$user_type = USER_INACTIVE;
				}
				else
				{
					$user_type = USER_NORMAL;
					$user_actkey = '';
				}

				$user_row = array(
					'username'		=> $username,
					'user_password'	=> md5($new_password),
					'user_email'	=> $email,
					'group_id'		=> (int) $group_id,
					'user_timezone'	=> (float) $tz,
					'user_lang'		=> $lang,
					'user_type'		=> $user_type,
					'user_actkey'	=> $user_actkey,
					'user_ip'		=> $user->ip,
					'user_regdate'	=> time(),
				);

				// Register user...
				$user_id = user_add($user_row, $cp_data);

				// This should not happen, because the required variables are listed above...
				if ($user_id === false)
				{
					trigger_error($user->lang['NO_USER'], E_USER_ERROR);
				}

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
					include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

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
						'USERNAME'		=> html_entity_decode($username),
						'PASSWORD'		=> html_entity_decode($password_confirm),
						'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

						'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
					);

					if ($coppa)
					{
						$messenger->assign_vars(array(
							'FAX_INFO'		=> $config['coppa_fax'],
							'MAIL_INFO'		=> $config['coppa_mail'],
							'EMAIL_ADDRESS'	=> $email,
							'SITENAME'		=> $config['sitename'])
						);
					}

					$messenger->send(NOTIFY_EMAIL);

					if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
					{
						// Grab an array of user_id's with a_user permissions ... these users can activate a user
						$admin_ary = $auth->acl_get_list(false, 'a_user', false);

						$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
							FROM ' . USERS_TABLE . '
							WHERE ' . $db->sql_in_set('user_id', $admin_ary[0]['a_user']);
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$messenger->template('admin_activate', $row['user_lang']);
							$messenger->replyto($config['board_contact']);
							$messenger->to($row['user_email'], $row['username']);
							$messenger->im($row['user_jabber'], $row['username']);

							$messenger->assign_vars(array(
								'USERNAME'		=> html_entity_decode($username),
								'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

								'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
							);

							$messenger->send($row['user_notify_type']);
						}
						$db->sql_freeresult($result);
					}
				}
				unset($data);

				$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'],  '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
				trigger_error($message);
			}
		}

		$s_hidden_fields = build_hidden_fields(array(
			'agreed'		=> 'true', 
			'coppa'			=> $coppa,
			'change_lang'	=> 0)
		);

		$confirm_image = '';

		// Visual Confirmation - Show images
		if ($config['enable_confirm'])
		{
			$str = '';
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
						$sql_in[] = (string) $row['session_id'];
					}
					while ($row = $db->sql_fetchrow($result));

					$sql = 'DELETE FROM ' .  CONFIRM_TABLE . '
						WHERE ' . $db->sql_in_set('session_id', $sql_in, true) . '
							AND confirm_type = ' . CONFIRM_REG;
					$db->sql_query($sql);
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT COUNT(session_id) AS attempts
					FROM ' . CONFIRM_TABLE . "
					WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
						AND confirm_type = " . CONFIRM_REG;
				$result = $db->sql_query($sql);
				$attempts = (int) $db->sql_fetchfield('attempts');
				$db->sql_freeresult($result);

				if ($config['max_reg_attempts'] && $attempts > $config['max_reg_attempts'])
				{
					trigger_error($user->lang['TOO_MANY_REGISTERS']);
				}

				$code = gen_rand_string(mt_rand(5, 8));
				$confirm_id = md5(unique_id($user->ip));

				$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'confirm_id'	=> (string) $confirm_id,
					'session_id'	=> (string) $user->session_id,
					'confirm_type'	=> (int) CONFIRM_REG,
					'code'			=> (string) $code)
				);
				$db->sql_query($sql);
			}
			else
			{
				$str .= '&amp;change_lang=' . $change_lang;
			}

			$confirm_image = '<img src="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=confirm&amp;id=' . $confirm_id . '&amp;type=' . CONFIRM_REG . $str) . '" alt="" title="" />';
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
			'PASSWORD'			=> (isset($new_password)) ? $new_password : '',
			'PASSWORD_CONFIRM'	=> (isset($password_confirm)) ? $password_confirm : '',
			'EMAIL'				=> (isset($email)) ? $email : '',
			'EMAIL_CONFIRM'		=> (isset($email_confirm)) ? $email_confirm : '',
			'CONFIRM_IMG'		=> $confirm_image,

			'L_CONFIRM_EXPLAIN'			=> sprintf($user->lang['CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlentities($config['board_contact']) . '">', '</a>'),
			'L_REG_COND'				=> $l_reg_cond,
			'L_USERNAME_EXPLAIN'		=> sprintf($user->lang[$user_char_ary[str_replace('\\\\', '\\', $config['allow_name_chars'])] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']),
			'L_NEW_PASSWORD_EXPLAIN'	=> sprintf($user->lang['NEW_PASSWORD_EXPLAIN'], $config['min_pass_chars'], $config['max_pass_chars']),

			'S_LANG_OPTIONS'	=> language_select($lang),
			'S_TZ_OPTIONS'		=> tz_select($tz),
			'S_CONFIRM_CODE'	=> ($config['enable_confirm']) ? true : false,
			'S_COPPA'			=> $coppa,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'))
		);

		//
		$user->profile_fields = array();

		// Generate profile fields -> Template Block Variable profile_fields
		$cp->generate_profile_fields('register', $user->get_iso_lang_id());

		//
		$this->tpl_name = 'ucp_register';
		$this->page_title = 'UCP_REGISTRATION';
	}
}

?>