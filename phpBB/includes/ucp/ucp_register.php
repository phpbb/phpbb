<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* ucp_register
* Board registration
*/
class ucp_register
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;
		global $request, $phpbb_container;

		//
		if ($config['require_activation'] == USER_ACTIVATION_DISABLE ||
			(in_array($config['require_activation'], array(USER_ACTIVATION_SELF, USER_ACTIVATION_ADMIN)) && !$config['email_enable']))
		{
			trigger_error('UCP_REGISTER_DISABLE');
		}

		$coppa			= $request->is_set('coppa') ? (int) $request->variable('coppa', false) : false;
		$agreed			= $request->variable('agreed', false);
		$submit			= $request->is_set_post('submit');
		$change_lang	= request_var('change_lang', '');
		$user_lang		= request_var('lang', $user->lang_name);

		if ($agreed)
		{
			add_form_key('ucp_register');
		}
		else
		{
			add_form_key('ucp_register_terms');
		}

		if ($change_lang || $user_lang != $config['default_lang'])
		{
			$use_lang = ($change_lang) ? basename($change_lang) : basename($user_lang);

			if (!validate_language_iso_name($use_lang))
			{
				if ($change_lang)
				{
					$submit = false;

					// Setting back agreed to let the user view the agreement in his/her language
					$agreed = false;
				}

				$user_lang = $use_lang;
			}
			else
			{
				$change_lang = '';
				$user_lang = $user->lang_name;
			}
		}

		$cp = $phpbb_container->get('profilefields.manager');

		$error = $cp_data = $cp_error = array();
		$s_hidden_fields = array();

		// Handle login_link data added to $_hidden_fields
		$login_link_data = $this->get_login_link_data_array();

		if (!empty($login_link_data))
		{
			// Confirm that we have all necessary data
			$provider_collection = $phpbb_container->get('auth.provider_collection');
			$auth_provider = $provider_collection->get_provider($request->variable('auth_provider', ''));

			$result = $auth_provider->login_link_has_necessary_data($login_link_data);
			if ($result !== null)
			{
				$error[] = $user->lang[$result];
			}

			$s_hidden_fields = array_merge($s_hidden_fields, $this->get_login_link_data_for_hidden_fields($login_link_data));
		}

		if (!$agreed || ($coppa === false && $config['coppa_enable']) || ($coppa && !$config['coppa_enable']))
		{
			$add_coppa = ($coppa !== false) ? '&amp;coppa=' . $coppa : '';

			$s_hidden_fields = array_merge($s_hidden_fields, array(
				'change_lang'	=> '',
			));

			// If we change the language, we want to pass on some more possible parameter.
			if ($change_lang)
			{
				// We do not include the password
				$s_hidden_fields = array_merge($s_hidden_fields, array(
					'username'			=> utf8_normalize_nfc(request_var('username', '', true)),
					'email'				=> strtolower(request_var('email', '')),
					'lang'				=> $user->lang_name,
					'tz'				=> request_var('tz', $config['board_timezone']),
				));

			}

			// Checking amount of available languages
			$sql = 'SELECT lang_id
				FROM ' . LANG_TABLE;
			$result = $db->sql_query($sql);

			$lang_row = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$lang_row[] = $row;
			}
			$db->sql_freeresult($result);

			if ($coppa === false && $config['coppa_enable'])
			{
				$now = getdate();
				$coppa_birthday = $user->create_datetime()
					->setDate($now['year'] - 13, $now['mon'], $now['mday'] - 1)
					->setTime(0, 0, 0)
					->format($user->lang['DATE_FORMAT'], true);
				unset($now);

				$template->assign_vars(array(
					'S_LANG_OPTIONS'	=> (sizeof($lang_row) > 1) ? language_select($user_lang) : '',
					'L_COPPA_NO'		=> sprintf($user->lang['UCP_COPPA_BEFORE'], $coppa_birthday),
					'L_COPPA_YES'		=> sprintf($user->lang['UCP_COPPA_ON_AFTER'], $coppa_birthday),

					'U_COPPA_NO'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register&amp;coppa=0'),
					'U_COPPA_YES'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register&amp;coppa=1'),

					'S_SHOW_COPPA'		=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'),

					'COOKIE_NAME'		=> $config['cookie_name'],
					'COOKIE_PATH'		=> $config['cookie_path'],
				));
			}
			else
			{
				$template->assign_vars(array(
					'S_LANG_OPTIONS'	=> (sizeof($lang_row) > 1) ? language_select($user_lang) : '',
					'L_TERMS_OF_USE'	=> sprintf($user->lang['TERMS_OF_USE_CONTENT'], $config['sitename'], generate_board_url()),

					'S_SHOW_COPPA'		=> false,
					'S_REGISTRATION'	=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register' . $add_coppa),

					'COOKIE_NAME'		=> $config['cookie_name'],
					'COOKIE_PATH'		=> $config['cookie_path'],
					)
				);
			}
			unset($lang_row);

			$this->tpl_name = 'ucp_agreement';
			return;
		}

		// The CAPTCHA kicks in here. We can't help that the information gets lost on language change.
		if ($config['enable_confirm'])
		{
			$captcha = $phpbb_container->get('captcha.factory')->get_instance($config['captcha_plugin']);
			$captcha->init(CONFIRM_REG);
		}

		$timezone = $config['board_timezone'];

		$data = array(
			'username'			=> utf8_normalize_nfc(request_var('username', '', true)),
			'new_password'		=> $request->variable('new_password', '', true),
			'password_confirm'	=> $request->variable('password_confirm', '', true),
			'email'				=> strtolower(request_var('email', '')),
			'lang'				=> basename(request_var('lang', $user->lang_name)),
			'tz'				=> request_var('tz', $timezone),
		);

		// Check and initialize some variables if needed
		if ($submit)
		{
			$error = validate_data($data, array(
				'username'			=> array(
					array('string', false, $config['min_name_chars'], $config['max_name_chars']),
					array('username', '')),
				'new_password'		=> array(
					array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
					array('password')),
				'password_confirm'	=> array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
				'email'				=> array(
					array('string', false, 6, 60),
					array('user_email')),
				'tz'				=> array('timezone'),
				'lang'				=> array('language_iso_name'),
			));

			if (!check_form_key('ucp_register'))
			{
				$error[] = $user->lang['FORM_INVALID'];
			}

			// Replace "error" strings with their real, localised form
			$error = array_map(array($user, 'lang'), $error);

			if ($config['enable_confirm'])
			{
				$vc_response = $captcha->validate($data);
				if ($vc_response !== false)
				{
					$error[] = $vc_response;
				}

				if ($config['max_reg_attempts'] && $captcha->get_attempt_count() > $config['max_reg_attempts'])
				{
					$error[] = $user->lang['TOO_MANY_REGISTERS'];
				}
			}

			// DNSBL check
			if ($config['check_dnsbl'])
			{
				if (($dnsbl = $user->check_dnsbl('register')) !== false)
				{
					$error[] = sprintf($user->lang['IP_BLACKLISTED'], $user->ip, $dnsbl[1]);
				}
			}

			// validate custom profile fields
			$cp->submit_cp_field('register', $user->get_iso_lang_id(), $cp_data, $error);

			if (!sizeof($error))
			{
				if ($data['new_password'] != $data['password_confirm'])
				{
					$error[] = $user->lang['NEW_PASSWORD_ERROR'];
				}
			}

			if (!sizeof($error))
			{
				$server_url = generate_board_url();

				// Which group by default?
				$group_name = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';

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
					$user_actkey = gen_rand_string(mt_rand(6, 10));
					$user_type = USER_INACTIVE;
					$user_inactive_reason = INACTIVE_REGISTER;
					$user_inactive_time = time();
				}
				else
				{
					$user_type = USER_NORMAL;
					$user_actkey = '';
					$user_inactive_reason = 0;
					$user_inactive_time = 0;
				}

				// Instantiate passwords manager
				$passwords_manager = $phpbb_container->get('passwords.manager');

				$user_row = array(
					'username'				=> $data['username'],
					'user_password'			=> $passwords_manager->hash($data['new_password']),
					'user_email'			=> $data['email'],
					'group_id'				=> (int) $group_id,
					'user_timezone'			=> $data['tz'],
					'user_lang'				=> $data['lang'],
					'user_type'				=> $user_type,
					'user_actkey'			=> $user_actkey,
					'user_ip'				=> $user->ip,
					'user_regdate'			=> time(),
					'user_inactive_reason'	=> $user_inactive_reason,
					'user_inactive_time'	=> $user_inactive_time,
				);

				if ($config['new_member_post_limit'])
				{
					$user_row['user_new'] = 1;
				}

				// Register user...
				$user_id = user_add($user_row, $cp_data);

				// This should not happen, because the required variables are listed above...
				if ($user_id === false)
				{
					trigger_error('NO_USER', E_USER_ERROR);
				}

				// Okay, captcha, your job is done.
				if ($config['enable_confirm'] && isset($captcha))
				{
					$captcha->reset();
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

					$messenger->template($email_template, $data['lang']);

					$messenger->to($data['email'], $data['username']);

					$messenger->anti_abuse_headers($config, $user);

					$messenger->assign_vars(array(
						'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
						'USERNAME'		=> htmlspecialchars_decode($data['username']),
						'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
						'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
					);

					if ($coppa)
					{
						$messenger->assign_vars(array(
							'FAX_INFO'		=> $config['coppa_fax'],
							'MAIL_INFO'		=> $config['coppa_mail'],
							'EMAIL_ADDRESS'	=> $data['email'])
						);
					}

					$messenger->send(NOTIFY_EMAIL);
				}

				if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
				{
					$phpbb_notifications = $phpbb_container->get('notification_manager');
					$phpbb_notifications->add_notifications('notification.type.admin_activate_user', array(
						'user_id'		=> $user_id,
						'user_actkey'	=> $user_row['user_actkey'],
						'user_regdate'	=> $user_row['user_regdate'],
					));
				}

				// Perform account linking if necessary
				if (!empty($login_link_data))
				{
					$login_link_data['user_id'] = $user_id;

					$result = $auth_provider->link_account($login_link_data);

					if ($result)
					{
						$message = $message . '<br /><br />' . $user->lang[$result];
					}
				}

				$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
				trigger_error($message);
			}
		}

		$s_hidden_fields = array_merge($s_hidden_fields, array(
			'agreed'		=> 'true',
			'change_lang'	=> 0,
		));

		if ($config['coppa_enable'])
		{
			$s_hidden_fields['coppa'] = $coppa;
		}

		if ($config['enable_confirm'])
		{
			$s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields());
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);
		$confirm_image = '';

		// Visual Confirmation - Show images
		if ($config['enable_confirm'])
		{
			$template->assign_vars(array(
				'CAPTCHA_TEMPLATE'		=> $captcha->get_template(),
			));
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

		$timezone_selects = phpbb_timezone_select($template, $user, $data['tz'], true);
		$template->assign_vars(array(
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'USERNAME'			=> $data['username'],
			'PASSWORD'			=> $data['new_password'],
			'PASSWORD_CONFIRM'	=> $data['password_confirm'],
			'EMAIL'				=> $data['email'],

			'L_REG_COND'				=> $l_reg_cond,
			'L_USERNAME_EXPLAIN'		=> $user->lang($config['allow_name_chars'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_name_chars']), $user->lang('CHARACTERS', (int) $config['max_name_chars'])),
			'L_PASSWORD_EXPLAIN'		=> $user->lang($config['pass_complex'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_pass_chars']), $user->lang('CHARACTERS', (int) $config['max_pass_chars'])),

			'S_LANG_OPTIONS'	=> language_select($data['lang']),
			'S_TZ_PRESELECT'	=> !$submit,
			'S_CONFIRM_REFRESH'	=> ($config['enable_confirm'] && $config['confirm_refresh']) ? true : false,
			'S_REGISTRATION'	=> true,
			'S_COPPA'			=> $coppa,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'),

			'COOKIE_NAME'		=> $config['cookie_name'],
			'COOKIE_PATH'		=> $config['cookie_path'],
		));

		//
		$user->profile_fields = array();

		// Generate profile fields -> Template Block Variable profile_fields
		$cp->generate_profile_fields('register', $user->get_iso_lang_id());

		//
		$this->tpl_name = 'ucp_register';
		$this->page_title = 'UCP_REGISTRATION';
	}

	/**
	* Creates the login_link data array
	*
	* @return	array	Returns an array of all POST paramaters whose names
	*					begin with 'login_link_'
	*/
	protected function get_login_link_data_array()
	{
		global $request;

		$var_names = $request->variable_names(\phpbb\request\request_interface::POST);
		$login_link_data = array();
		$string_start_length = strlen('login_link_');

		foreach ($var_names as $var_name)
		{
			if (strpos($var_name, 'login_link_') === 0)
			{
				$key_name = substr($var_name, $string_start_length);
				$login_link_data[$key_name] = $request->variable($var_name, '', false, \phpbb\request\request_interface::POST);
			}
		}

		return $login_link_data;
	}

	/**
	* Prepends they key names of an associative array with 'login_link_' for
	* inclusion on the page as hidden fields.
	*
	* @param	array	$data	The array to be modified
	* @return	array	The modified array
	*/
	protected function get_login_link_data_for_hidden_fields($data)
	{
		$new_data = array();

		foreach ($data as $key => $value)
		{
			$new_data['login_link_' . $key] = $value;
		}

		return $new_data;
	}
}
