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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

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
		//
		if (phpbb::$config['require_activation'] == USER_ACTIVATION_DISABLE)
		{
			trigger_error('UCP_REGISTER_DISABLE');
		}

		include(PHPBB_ROOT_PATH . 'includes/functions_profile_fields.' . PHP_EXT);

		$coppa			= phpbb_request::is_set('coppa') ? ((request_var('coppa', false)) ? 1 : 0) : false;
		$agreed			= phpbb_request::variable('agreed', false, false, phpbb_request::POST) ? 1 : 0;
		$submit			= phpbb_request::is_set_post('submit');
		$change_lang	= request_var('change_lang', '');
		$user_lang		= request_var('lang', phpbb::$user->lang_name);

		if ($agreed)
		{
			add_form_key('ucp_register');
		}
		else
		{
			add_form_key('ucp_register_terms');
		}

		if (phpbb::$config['enable_confirm'])
		{
			include(PHPBB_ROOT_PATH . 'includes/captcha/captcha_factory.' . PHP_EXT);
			$captcha = phpbb_captcha_factory::get_instance(phpbb::$config['captcha_plugin']);
			$captcha->init(CONFIRM_REG);
		}

		if ($change_lang || $user_lang != phpbb::$config['default_lang'])
		{
			$use_lang = ($change_lang) ? basename($change_lang) : basename($user_lang);

			if (file_exists(phpbb::$user->lang_path . $use_lang . '/'))
			{
				if ($change_lang)
				{
					$submit = false;

					// Setting back agreed to let the user view the agreement in his/her language
					$agreed = (phpbb_request::is_set_post('change_lang')) ? 0 : $agreed;
				}

				phpbb::$user->lang_name = $lang = $use_lang;
				phpbb::$user->lang = array();
				phpbb::$user->add_lang(array('common', 'ucp'));
			}
			else
			{
				$change_lang = '';
				$user_lang = phpbb::$user->lang_name;
			}
		}

		$cp = new custom_profile();

		$error = $cp_data = $cp_error = array();


		if (!$agreed || ($coppa === false && phpbb::$config['coppa_enable']) || ($coppa && !phpbb::$config['coppa_enable']))
		{
			$add_lang = ($change_lang) ? '&amp;change_lang=' . urlencode($change_lang) : '';
			$add_coppa = ($coppa !== false) ? '&amp;coppa=' . $coppa : '';

			$s_hidden_fields = array();

			// If we change the language, we want to pass on some more possible parameter.
			if ($change_lang)
			{
				// We do not include the password
				$s_hidden_fields = array_merge($s_hidden_fields, array(
					'username'			=> utf8_normalize_nfc(request_var('username', '', true)),
					'email'				=> strtolower(request_var('email', '')),
					'email_confirm'		=> strtolower(request_var('email_confirm', '')),
					'lang'				=> phpbb::$user->lang_name,
					'tz'				=> request_var('tz', (float) phpbb::$config['board_timezone']),
				));

				if (phpbb::$config['enable_confirm'])
				{
					$s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields());
				}
			}

			if ($coppa === false && phpbb::$config['coppa_enable'])
			{
				$now = getdate();
				$coppa_birthday = phpbb::$user->format_date(mktime($now['hours'] + phpbb::$user->data['user_dst'], $now['minutes'], $now['seconds'], $now['mon'], $now['mday'] - 1, $now['year'] - 13), phpbb::$user->lang['DATE_FORMAT']);
				unset($now);

				phpbb::$template->assign_vars(array(
					'L_COPPA_NO'		=> sprintf(phpbb::$user->lang['UCP_COPPA_BEFORE'], $coppa_birthday),
					'L_COPPA_YES'		=> sprintf(phpbb::$user->lang['UCP_COPPA_ON_AFTER'], $coppa_birthday),

					'U_COPPA_NO'		=> append_sid('ucp', 'mode=register&amp;coppa=0' . $add_lang),
					'U_COPPA_YES'		=> append_sid('ucp', 'mode=register&amp;coppa=1' . $add_lang),

					'S_SHOW_COPPA'		=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_UCP_ACTION'		=> append_sid('ucp', 'mode=register' . $add_lang),
				));
			}
			else
			{
				phpbb::$template->assign_vars(array(
					'L_TERMS_OF_USE'	=> sprintf(phpbb::$user->lang['TERMS_OF_USE_CONTENT'], phpbb::$config['sitename'], generate_board_url()),

					'S_SHOW_COPPA'		=> false,
					'S_REGISTRATION'	=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_UCP_ACTION'		=> append_sid('ucp', 'mode=register' . $add_lang . $add_coppa),
				));
			}

			$this->tpl_name = 'ucp_agreement';
			return;
		}


		// Try to manually determine the timezone and adjust the dst if the server date/time complies with the default setting +/- 1
		$timezone = date('Z') / 3600;
		$is_dst = date('I');

		if (phpbb::$config['board_timezone'] == $timezone || phpbb::$config['board_timezone'] == ($timezone - 1))
		{
			$timezone = ($is_dst) ? $timezone - 1 : $timezone;

			if (!isset(phpbb::$user->lang['tz_zones'][(string) $timezone]))
			{
				$timezone = phpbb::$config['board_timezone'];
			}
		}
		else
		{
			$is_dst = phpbb::$config['board_dst'];
			$timezone = phpbb::$config['board_timezone'];
		}

		$data = array(
			'username'			=> utf8_normalize_nfc(request_var('username', '', true)),
			'new_password'		=> request_var('new_password', '', true),
			'password_confirm'	=> request_var('password_confirm', '', true),
			'email'				=> strtolower(request_var('email', '')),
			'email_confirm'		=> strtolower(request_var('email_confirm', '')),
			'lang'				=> basename(request_var('lang', phpbb::$user->lang_name)),
			'tz'				=> request_var('tz', (float) $timezone),
		);

		// Check and initialize some variables if needed
		if ($submit)
		{
			$error = validate_data($data, array(
				'username'			=> array(
					array('string', false, phpbb::$config['min_name_chars'], phpbb::$config['max_name_chars']),
					array('username', '')),
				'new_password'		=> array(
					array('string', false, phpbb::$config['min_pass_chars'], phpbb::$config['max_pass_chars']),
					array('password')),
				'password_confirm'	=> array('string', false, phpbb::$config['min_pass_chars'], phpbb::$config['max_pass_chars']),
				'email'				=> array(
					array('string', false, 6, 60),
					array('email')),
				'email_confirm'		=> array('string', false, 6, 60),
				'tz'				=> array('num', false, -14, 14),
				'lang'				=> array('match', false, '#^[a-z_\-]{2,}$#i'),
			));
			if (!check_form_key('ucp_register'))
			{
				$error[] = phpbb::$user->lang['FORM_INVALID'];
			}
			// Replace "error" strings with their real, localised form
			$error = preg_replace('#^([A-Z_]+)$#e', "phpbb::\$user->lang('\\1')", $error);

			if (phpbb::$config['enable_confirm'])
			{
				$vc_response = $captcha->validate();
				if ($vc_response)
				{
					$error[] = $vc_response;
				}
				else
				{
					$captcha->reset();
				}
				if (phpbb::$config['max_reg_attempts'] && $captcha->get_attempt_count() > phpbb::$config['max_reg_attempts'])
				{
					$error[] = phpbb::$user->lang['TOO_MANY_REGISTERS'];
				}
			}
			// DNSBL check
			if (phpbb::$config['check_dnsbl'])
			{
				if (($dnsbl = phpbb::$user->check_dnsbl('register')) !== false)
				{
					$error[] = sprintf(phpbb::$user->lang['IP_BLACKLISTED'], phpbb::$user->ip, $dnsbl[1]);
				}
			}

			// validate custom profile fields
			$cp->submit_cp_field('register', phpbb::$user->get_iso_lang_id(), $cp_data, $error);

			if (!sizeof($error))
			{
				if ($data['new_password'] != $data['password_confirm'])
				{
					$error[] = phpbb::$user->lang['NEW_PASSWORD_ERROR'];
				}

				if ($data['email'] != $data['email_confirm'])
				{
					$error[] = phpbb::$user->lang['NEW_EMAIL_ERROR'];
				}
			}

			if (!sizeof($error))
			{
				$server_url = generate_board_url();

				// Which group by default?
				$group_name = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';

				$sql = 'SELECT group_id
					FROM ' . GROUPS_TABLE . "
					WHERE group_name = '" . phpbb::$db->sql_escape($group_name) . "'
						AND group_type = " . GROUP_SPECIAL;
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error('NO_GROUP');
				}

				$group_id = $row['group_id'];

				if (($coppa ||
					phpbb::$config['require_activation'] == USER_ACTIVATION_SELF ||
					phpbb::$config['require_activation'] == USER_ACTIVATION_ADMIN) && phpbb::$config['email_enable'])
				{
					$user_actkey = gen_rand_string(10);
					$key_len = 54 - (strlen($server_url));
					$key_len = ($key_len < 6) ? 6 : $key_len;
					$user_actkey = substr($user_actkey, 0, $key_len);

					$user_type = phpbb::USER_INACTIVE;
					$user_inactive_reason = INACTIVE_REGISTER;
					$user_inactive_time = time();
				}
				else
				{
					$user_type = phpbb::USER_NORMAL;
					$user_actkey = '';
					$user_inactive_reason = 0;
					$user_inactive_time = 0;
				}

				$user_row = array(
					'username'				=> $data['username'],
					'user_password'			=> phpbb_hash($data['new_password']),
					'user_email'			=> $data['email'],
					'group_id'				=> (int) $group_id,
					'user_timezone'			=> (float) $data['tz'],
					'user_dst'				=> $is_dst,
					'user_lang'				=> $data['lang'],
					'user_type'				=> $user_type,
					'user_actkey'			=> $user_actkey,
					'user_ip'				=> phpbb::$user->ip,
					'user_regdate'			=> time(),
					'user_inactive_reason'	=> $user_inactive_reason,
					'user_inactive_time'	=> $user_inactive_time,
				);

				// Register user...
				$user_id = user_add($user_row, $cp_data);

				// This should not happen, because the required variables are listed above...
				if ($user_id === false)
				{
					trigger_error('NO_USER', E_USER_ERROR);
				}

				if ($coppa && phpbb::$config['email_enable'])
				{
					$message = phpbb::$user->lang['ACCOUNT_COPPA'];
					$email_template = 'coppa_welcome_inactive';
				}
				else if (phpbb::$config['require_activation'] == USER_ACTIVATION_SELF && phpbb::$config['email_enable'])
				{
					$message = phpbb::$user->lang['ACCOUNT_INACTIVE'];
					$email_template = 'user_welcome_inactive';
				}
				else if (phpbb::$config['require_activation'] == USER_ACTIVATION_ADMIN && phpbb::$config['email_enable'])
				{
					$message = phpbb::$user->lang['ACCOUNT_INACTIVE_ADMIN'];
					$email_template = 'admin_welcome_inactive';
				}
				else
				{
					$message = phpbb::$user->lang['ACCOUNT_ADDED'];
					$email_template = 'user_welcome';
				}

				if (phpbb::$config['email_enable'])
				{
					include_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.' . PHP_EXT);

					$messenger = new messenger(false);

					$messenger->template($email_template, $data['lang']);

					$messenger->to($data['email'], $data['username']);

					$messenger->headers('X-AntiAbuse: Board servername - ' . phpbb::$config['server_name']);
					$messenger->headers('X-AntiAbuse: User_id - ' . phpbb::$user->data['user_id']);
					$messenger->headers('X-AntiAbuse: Username - ' . phpbb::$user->data['username']);
					$messenger->headers('X-AntiAbuse: User IP - ' . phpbb::$user->ip);

					$messenger->assign_vars(array(
						'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf(phpbb::$user->lang['WELCOME_SUBJECT'], phpbb::$config['sitename'])),
						'USERNAME'		=> htmlspecialchars_decode($data['username']),
						'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
						'U_ACTIVATE'	=> "$server_url/ucp." . PHP_EXT . "?mode=activate&u=$user_id&k=$user_actkey")
					);

					if ($coppa)
					{
						$messenger->assign_vars(array(
							'FAX_INFO'		=> phpbb::$config['coppa_fax'],
							'MAIL_INFO'		=> phpbb::$config['coppa_mail'],
							'EMAIL_ADDRESS'	=> $data['email'],
						));
					}

					$messenger->send(NOTIFY_EMAIL);

					if (phpbb::$config['require_activation'] == USER_ACTIVATION_ADMIN)
					{
						// Grab an array of user_id's with a_user permissions ... these users can activate a user
						$admin_ary = phpbb::$acl->acl_get_list(false, 'a_user', false);
						$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

						// Also include founders
						$where_sql = ' WHERE user_type = ' . phpbb::USER_FOUNDER;

						if (sizeof($admin_ary))
						{
							$where_sql .= ' OR ' . phpbb::$db->sql_in_set('user_id', $admin_ary);
						}

						$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
							FROM ' . USERS_TABLE . ' ' .
							$where_sql;
						$result = phpbb::$db->sql_query($sql);

						while ($row = phpbb::$db->sql_fetchrow($result))
						{
							$messenger->template('admin_activate', $row['user_lang']);
							$messenger->to($row['user_email'], $row['username']);
							$messenger->im($row['user_jabber'], $row['username']);

							$messenger->assign_vars(array(
								'USERNAME'			=> htmlspecialchars_decode($data['username']),
								'U_USER_DETAILS'	=> "$server_url/memberlist." . PHP_EXT . "?mode=viewprofile&u=$user_id",
								'U_ACTIVATE'		=> "$server_url/ucp." . PHP_EXT . "?mode=activate&u=$user_id&k=$user_actkey")
							);

							$messenger->send($row['user_notify_type']);
						}
						phpbb::$db->sql_freeresult($result);
					}
				}

				$message = $message . '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_INDEX'], '<a href="' . append_sid('index') . '">', '</a>');
				trigger_error($message);
			}
		}

		$s_hidden_fields = array(
			'agreed'		=> 'true',
			'change_lang'	=> 0,
		);

		if (phpbb::$config['coppa_enable'])
		{
			$s_hidden_fields['coppa'] = $coppa;
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$confirm_image = '';

		// Visual Confirmation - Show images

		if (phpbb::$config['enable_confirm'])
		{
			if ($change_lang)
			{
				$str = '&amp;change_lang=' . $change_lang;
			}
			else
			{
				$str = '';
			}

			phpbb::$template->assign_vars(array(
				'L_CONFIRM_EXPLAIN'		=> sprintf(phpbb::$user->lang['CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlspecialchars(phpbb::$config['board_contact']) . '">', '</a>'),
				'S_CAPTCHA'				=> $captcha->get_template(),
			));
		}

		//
		$l_reg_cond = '';
		switch (phpbb::$config['require_activation'])
		{
			case USER_ACTIVATION_SELF:
				$l_reg_cond = phpbb::$user->lang['UCP_EMAIL_ACTIVATE'];
			break;

			case USER_ACTIVATION_ADMIN:
				$l_reg_cond = phpbb::$user->lang['UCP_ADMIN_ACTIVATE'];
			break;
		}

		phpbb::$template->assign_vars(array(
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'USERNAME'			=> $data['username'],
			'PASSWORD'			=> $data['new_password'],
			'PASSWORD_CONFIRM'	=> $data['password_confirm'],
			'EMAIL'				=> $data['email'],
			'EMAIL_CONFIRM'		=> $data['email_confirm'],

			'L_REG_COND'				=> $l_reg_cond,
			'L_USERNAME_EXPLAIN'		=> sprintf(phpbb::$user->lang[phpbb::$config['allow_name_chars'] . '_EXPLAIN'], phpbb::$config['min_name_chars'], phpbb::$config['max_name_chars']),
			'L_PASSWORD_EXPLAIN'		=> sprintf(phpbb::$user->lang[phpbb::$config['pass_complex'] . '_EXPLAIN'], phpbb::$config['min_pass_chars'], phpbb::$config['max_pass_chars']),

			'S_LANG_OPTIONS'	=> language_select($data['lang']),
			'S_TZ_OPTIONS'		=> tz_select($data['tz']),
			'S_CONFIRM_REFRESH'	=> (phpbb::$config['enable_confirm'] && phpbb::$config['confirm_refresh']) ? true : false,
			'S_COPPA'			=> $coppa,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> append_sid('ucp', 'mode=register'),
		));

		//
		phpbb::$user->profile_fields = array();

		// Generate profile fields -> Template Block Variable profile_fields
		$cp->generate_profile_fields('register', phpbb::$user->get_iso_lang_id());

		//
		$this->tpl_name = 'ucp_register';
		$this->page_title = 'UCP_REGISTRATION';
	}
}

?>