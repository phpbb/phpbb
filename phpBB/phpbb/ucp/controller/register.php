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

namespace phpbb\ucp\controller;

/**
 * Board registration
 */
class register
{
	var $u_action;

	public function main($id, $mode)
	{

		//
		if ($this->config['require_activation'] == USER_ACTIVATION_DISABLE ||
			(in_array($this->config['require_activation'], [USER_ACTIVATION_SELF, USER_ACTIVATION_ADMIN]) && !$this->config['email_enable']))
		{
			trigger_error('UCP_REGISTER_DISABLE');
		}

		$coppa			= $request->is_set('coppa_yes') ? 1 : ($request->is_set('coppa_no') ? 0 : false);
		$coppa			= $request->is_set('coppa') ? $request->variable('coppa', 0) : $coppa;
		$agreed			= $this->request->variable('agreed', false);
		$submit			= $this->request->is_set_post('submit');
		$change_lang	= $this->request->variable('change_lang', '');
		$user_lang		= $this->request->variable('lang', $this->language->lang_name);

		if ($agreed && !check_form_key('ucp_register'))
		{
			$agreed = false;
		}

		if ($coppa !== false && !check_form_key('ucp_register'))
		{
			$coppa = false;
		}

		/**
		 * Add UCP register data before they are assigned to the template or submitted
		 *
		 * To assign data to the template, use $this->template->assign_vars()
		 *
		 * @event core.ucp_register_requests_after
		 * @var bool	coppa		Is set coppa
		 * @var bool	agreed		Did user agree to coppa?
		 * @var bool	submit		Is set post submit?
		 * @var string	change_lang	Change language request
		 * @var string	user_lang	User language request
		 * @since 3.1.11-RC1
		 */
		$vars = [
			'coppa',
			'agreed',
			'submit',
			'change_lang',
			'user_lang',
		];
		extract($this->dispatcher->trigger_event('core.ucp_register_requests_after', compact($vars)));

		add_form_key('ucp_register');

		if ($change_lang || $user_lang != $this->config['default_lang'])
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
				$user_lang = $this->language->lang_name;
			}
		}

		/* @var $cp \phpbb\profilefields\manager */
		$cp = $phpbb_container->get('profilefields.manager');

		$error = $cp_data = $cp_error = [];
		$s_hidden_fields = [];

		// Handle login_link data added to $_hidden_fields
		$login_link_data = $this->get_login_link_data_array();

		if (!empty($login_link_data))
		{
			// Confirm that we have all necessary data
			/* @var $provider_collection \phpbb\auth\provider_collection */
			$provider_collection = $phpbb_container->get('auth.provider_collection');
			$auth_provider = $provider_collection->get_provider($this->request->variable('auth_provider', ''));

			$result = $auth_provider->login_link_has_necessary_data($login_link_data);
			if ($result !== null)
			{
				$error[] = $this->language->lang($result);
			}

			$s_hidden_fields = array_merge($s_hidden_fields, $this->get_login_link_data_for_hidden_fields($login_link_data));
		}

		if (!$agreed || ($coppa === false && $this->config['coppa_enable']) || ($coppa && !$this->config['coppa_enable']))
		{
			$add_coppa = ($coppa !== false) ? '&amp;coppa=' . $coppa : '';

			$s_hidden_fields = array_merge($s_hidden_fields, [
				'change_lang'	=> '',
			]);

			// If we change the language, we want to pass on some more possible parameter.
			if ($change_lang)
			{
				// We do not include the password
				$s_hidden_fields = array_merge($s_hidden_fields, [
					'username'			=> $this->request->variable('username', '', true),
					'email'				=> strtolower($this->request->variable('email', '')),
					'lang'				=> $this->language->lang_name,
					'tz'				=> $this->request->variable('tz', $this->config['board_timezone']),
				]);

			}

			// Checking amount of available languages
			$sql = 'SELECT lang_id
				FROM ' . $this->tables['lang'];
			$result = $this->db->sql_query($sql);

			$lang_row = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$lang_row[] = $row;
			}
			$this->db->sql_freeresult($result);

			if ($coppa === false && $this->config['coppa_enable'])
			{
				$now = getdate();
				$coppa_birthday = $this->user->create_datetime()
					->setDate($now['year'] - 13, $now['mon'], $now['mday'] - 1)
					->setTime(0, 0, 0)
					->format($this->language->lang('DATE_FORMAT'), true);
				unset($now);

				$template_vars = [
					'S_LANG_OPTIONS'	=> (count($lang_row) > 1) ? language_select($user_lang) : '',

					'L_COPPA_NO'		=> $user->lang('UCP_COPPA_BEFORE', $coppa_birthday),
					'L_COPPA_YES'		=> $user->lang('UCP_COPPA_ON_AFTER', $coppa_birthday),

					'S_SHOW_COPPA'		=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_UCP_ACTION'		=> append_sid("{$this->root_path}ucp.$this->php_ext", 'mode=register'),

					'COOKIE_NAME'		=> $this->config['cookie_name'],
					'COOKIE_PATH'		=> $this->config['cookie_path'],
				];
			}
			else
			{
				$template_vars = [
					'S_LANG_OPTIONS'	=> (count($lang_row) > 1) ? language_select($user_lang) : '',
					'L_TERMS_OF_USE'	=> sprintf($this->language->lang('TERMS_OF_USE_CONTENT'), $this->config['sitename'], generate_board_url()),

					'S_SHOW_COPPA'		=> false,
					'S_REGISTRATION'	=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_UCP_ACTION'		=> append_sid("{$this->root_path}ucp.$this->php_ext", 'mode=register' . $add_coppa),

					'COOKIE_NAME'		=> $this->config['cookie_name'],
					'COOKIE_PATH'		=> $this->config['cookie_path'],
				];
			}

			$tpl_name = 'ucp_agreement';

			/**
			 * Allows to modify the agreements.
			 *
			 * @event core.ucp_register_agreement_modify_template_data
			 * @var string	tpl_name			Template file
			 * @var array	template_vars		Array with data about to be assigned to the template
			 * @var array	s_hidden_fields		Array with hidden form elements
			 * @var array	lang_row			Array with available languages, read only
			 * @since 3.2.2-RC1
			 */
			$vars = ['tpl_name', 'template_vars', 's_hidden_fields', 'lang_row'];
			extract($this->dispatcher->trigger_event('core.ucp_register_agreement_modify_template_data', compact($vars)));

			unset($lang_row);

			$template_vars = array_merge($template_vars, [
				'S_HIDDEN_FIELDS' => build_hidden_fields($s_hidden_fields),
			]);

			$this->template->assign_vars($template_vars);

			/**
			 * Allows to modify the agreements.
			 *
			 * To assign data to the template, use $this->template->assign_vars()
			 *
			 * @event core.ucp_register_agreement
			 * @since 3.1.6-RC1
			 * @deprecated 3.2.2-RC1 Replaced by core.ucp_register_agreement_modify_template_data and to be removed in 3.3.0-RC1
			 */
			$this->dispatcher->dispatch('core.ucp_register_agreement');

			$this->tpl_name = $tpl_name;
			return;
		}

		// The CAPTCHA kicks in here. We can't help that the information gets lost on language change.
		if ($this->config['enable_confirm'])
		{
			$captcha = $phpbb_container->get('captcha.factory')->get_instance($this->config['captcha_plugin']);
			$captcha->init(CONFIRM_REG);
		}

		$timezone = $this->config['board_timezone'];

		$data = [
			'username'			=> $this->request->variable('username', '', true),
			'new_password'		=> $this->request->variable('new_password', '', true),
			'password_confirm'	=> $this->request->variable('password_confirm', '', true),
			'email'				=> strtolower($this->request->variable('email', '')),
			'lang'				=> basename($this->request->variable('lang', $this->language->lang_name)),
			'tz'				=> $this->request->variable('tz', $timezone),
		];
		/**
		 * Add UCP register data before they are assigned to the template or submitted
		 *
		 * To assign data to the template, use $this->template->assign_vars()
		 *
		 * @event core.ucp_register_data_before
		 * @var bool	submit		Do we display the form only
		 *							or did the user press submit
		 * @var array	data		Array with current ucp registration data
		 * @since 3.1.4-RC1
		 */
		$vars = ['submit', 'data'];
		extract($this->dispatcher->trigger_event('core.ucp_register_data_before', compact($vars)));

		// Check and initialize some variables if needed
		if ($submit)
		{
			$error = validate_data($data, [
				'username'			=> [
					['string', false, $this->config['min_name_chars'], $this->config['max_name_chars']],
					['username', '']],
				'new_password'		=> [
					['string', false, $this->config['min_pass_chars'], 0],
					['password']],
				'password_confirm'	=> ['string', false, $this->config['min_pass_chars'], 0],
				'email'				=> [
					['string', false, 6, 60],
					['user_email']],
				'tz'				=> ['timezone'],
				'lang'				=> ['language_iso_name'],
			]);

			if (!check_form_key('ucp_register'))
			{
				$error[] = $this->language->lang('FORM_INVALID');
			}

			// Replace "error" strings with their real, localised form
			$error = array_map([$user, 'lang'], $error);

			if ($this->config['enable_confirm'])
			{
				$vc_response = $captcha->validate($data);
				if ($vc_response !== false)
				{
					$error[] = $vc_response;
				}

				if ($this->config['max_reg_attempts'] && $captcha->get_attempt_count() > $this->config['max_reg_attempts'])
				{
					$error[] = $this->language->lang('TOO_MANY_REGISTERS');
				}
			}

			// DNSBL check
			if ($this->config['check_dnsbl'])
			{
				if (($dnsbl = $this->user->check_dnsbl('register')) !== false)
				{
					$error[] = sprintf($this->language->lang('IP_BLACKLISTED'), $this->user->ip, $dnsbl[1]);
				}
			}

			// validate custom profile fields
			$cp->submit_cp_field('register', $this->user->get_iso_lang_id(), $cp_data, $error);

			if (!count($error))
			{
				if ($data['new_password'] != $data['password_confirm'])
				{
					$error[] = $this->language->lang('NEW_PASSWORD_ERROR');
				}
			}
			/**
			 * Check UCP registration data after they are submitted
			 *
			 * @event core.ucp_register_data_after
			 * @var bool	submit		Do we display the form only
			 *							or did the user press submit
			 * @var array 	data		Array with current ucp registration data
			 * @var array	cp_data		Array with custom profile fields data
			 * @var array 	error		Array with list of errors
			 * @since 3.1.4-RC1
			 */
			$vars = ['submit', 'data', 'cp_data', 'error'];
			extract($this->dispatcher->trigger_event('core.ucp_register_data_after', compact($vars)));

			if (!count($error))
			{
				$server_url = generate_board_url();

				// Which group by default?
				$group_name = ($coppa) ? 'REGISTERED_COPPA' : 'REGISTERED';

				$sql = 'SELECT group_id
					FROM ' . $this->tables['groups'] . "
					WHERE group_name = '" . $this->db->sql_escape($group_name) . "'
						AND group_type = " . GROUP_SPECIAL;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error('NO_GROUP');
				}

				$group_id = $row['group_id'];

				if (($coppa ||
					$this->config['require_activation'] == USER_ACTIVATION_SELF ||
					$this->config['require_activation'] == USER_ACTIVATION_ADMIN) && $this->config['email_enable'])
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
				/* @var $passwords_manager \phpbb\passwords\manager */
				$passwords_manager = $phpbb_container->get('passwords.manager');

				$user_row = [
					'username'				=> $data['username'],
					'user_password'			=> $this->passwords_manager->hash($data['new_password']),
					'user_email'			=> $data['email'],
					'group_id'				=> (int) $group_id,
					'user_timezone'			=> $data['tz'],
					'user_lang'				=> $data['lang'],
					'user_type'				=> $user_type,
					'user_actkey'			=> $user_actkey,
					'user_ip'				=> $this->user->ip,
					'user_regdate'			=> time(),
					'user_inactive_reason'	=> $user_inactive_reason,
					'user_inactive_time'	=> $user_inactive_time,
				];

				if ($this->config['new_member_post_limit'])
				{
					$user_row['user_new'] = 1;
				}
				/**
				 * Add into $user_row before user_add
				 *
				 * user_add allows adding more data into the users table
				 *
				 * @event core.ucp_register_user_row_after
				 * @var bool	submit		Do we display the form only
				 *							or did the user press submit
				 * @var array	cp_data		Array with custom profile fields data
				 * @var array	user_row	Array with current ucp registration data
				 * @since 3.1.4-RC1
				 */
				$vars = ['submit', 'cp_data', 'user_row'];
				extract($this->dispatcher->trigger_event('core.ucp_register_user_row_after', compact($vars)));

				// Register user...
				$user_id = user_add($user_row, $cp_data);

				// This should not happen, because the required variables are listed above...
				if ($user_id === false)
				{
					trigger_error('NO_USER', E_USER_ERROR);
				}

				// Okay, captcha, your job is done.
				if ($this->config['enable_confirm'] && isset($captcha))
				{
					$captcha->reset();
				}

				if ($coppa && $this->config['email_enable'])
				{
					$message = $this->language->lang('ACCOUNT_COPPA');
					$email_template = 'coppa_welcome_inactive';
				}
				else if ($this->config['require_activation'] == USER_ACTIVATION_SELF && $this->config['email_enable'])
				{
					$message = $this->language->lang('ACCOUNT_INACTIVE');
					$email_template = 'user_welcome_inactive';
				}
				else if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN && $this->config['email_enable'])
				{
					$message = $this->language->lang('ACCOUNT_INACTIVE_ADMIN');
					$email_template = 'admin_welcome_inactive';
				}
				else
				{
					$message = $this->language->lang('ACCOUNT_ADDED');
					$email_template = 'user_welcome';

					// Autologin after registration
					$this->user->session_create($user_id, 0, false, 1);
				}

				if ($this->config['email_enable'])
				{
					include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

					$messenger = new messenger(false);

					$messenger->template($email_template, $data['lang']);

					$messenger->to($data['email'], $data['username']);

					$messenger->anti_abuse_headers($config, $user);

					$messenger->assign_vars([
						'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($this->language->lang('WELCOME_SUBJECT'), $this->config['sitename'])),
						'USERNAME'		=> htmlspecialchars_decode($data['username']),
						'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
						'U_ACTIVATE'	=> "$server_url/ucp.$this->php_ext?mode=activate&u=$user_id&k=$user_actkey"]
					);

					if ($coppa)
					{
						$messenger->assign_vars([
							'FAX_INFO'		=> $this->config['coppa_fax'],
							'MAIL_INFO'		=> $this->config['coppa_mail'],
							'EMAIL_ADDRESS'	=> $data['email']]
						);
					}

					/**
					 * Modify messenger data before welcome mail is sent
					 *
					 * @event core.ucp_register_welcome_email_before
					 * @var array		user_row	Array with user registration data
					 * @var array		cp_data		Array with custom profile fields data
					 * @var array		data		Array with current ucp registration data
					 * @var string		message		Message to be displayed to the user after registration
					 * @var string		server_url	Server URL
					 * @var int			user_id		New user ID
					 * @var string		user_actkey	User activation key
					 * @var messenger	messenger	phpBB Messenger
					 * @since 3.2.4-RC1
					 */
					$vars = [
						'user_row',
						'cp_data',
						'data',
						'message',
						'server_url',
						'user_id',
						'user_actkey',
						'messenger',
					];
					extract($this->dispatcher->trigger_event('core.ucp_register_welcome_email_before', compact($vars)));

					$messenger->send(NOTIFY_EMAIL);
				}

				if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN)
				{
					/* @var $phpbb_notifications \phpbb\notification\manager */
					$phpbb_notifications = $phpbb_container->get('notification_manager');
					$this->notification_manager->add_notifications('notification.type.admin_activate_user', [
						'user_id'		=> $user_id,
						'user_actkey'	=> $user_row['user_actkey'],
						'user_regdate'	=> $user_row['user_regdate'],
					]);
				}

				// Perform account linking if necessary
				if (!empty($login_link_data))
				{
					$login_link_data['user_id'] = $user_id;

					$result = $auth_provider->link_account($login_link_data);

					if ($result)
					{
						$message = $message . '<br /><br />' . $this->language->lang($result);
					}
				}

				/**
				 * Perform additional actions after user registration
				 *
				 * @event core.ucp_register_register_after
				 * @var array		user_row	Array with user registration data
				 * @var array		cp_data		Array with custom profile fields data
				 * @var array		data		Array with current ucp registration data
				 * @var string		message		Message to be displayed to the user after registration
				 * @var string		server_url	Server URL
				 * @var int			user_id		New user ID
				 * @var string		user_actkey	User activation key
				 * @since 3.2.4-RC1
				 */
				$vars = [
					'user_row',
					'cp_data',
					'data',
					'message',
					'server_url',
					'user_id',
					'user_actkey',
				];
				extract($this->dispatcher->trigger_event('core.ucp_register_register_after', compact($vars)));

				$message = $message . '<br /><br />' . sprintf($this->language->lang('RETURN_INDEX'), '<a href="' . append_sid("{$this->root_path}index.$this->php_ext") . '">', '</a>');
				trigger_error($message);
			}
		}

		$s_hidden_fields = array_merge($s_hidden_fields, [
			'agreed'		=> 'true',
			'change_lang'	=> 0,
		]);

		if ($this->config['coppa_enable'])
		{
			$s_hidden_fields['coppa'] = $coppa;
		}

		if ($this->config['enable_confirm'])
		{
			$s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields());
		}

		// Visual Confirmation - Show images
		if ($this->config['enable_confirm'])
		{
			$this->template->assign_vars([
				'CAPTCHA_TEMPLATE'		=> $captcha->get_template(),
			]);
		}

		//
		$l_reg_cond = '';
		switch ($this->config['require_activation'])
		{
			case USER_ACTIVATION_SELF:
				$l_reg_cond = $this->language->lang('UCP_EMAIL_ACTIVATE');
			break;

			case USER_ACTIVATION_ADMIN:
				$l_reg_cond = $this->language->lang('UCP_ADMIN_ACTIVATE');
			break;
		}

		/* @var $provider_collection \phpbb\auth\provider_collection */
		$provider_collection = $phpbb_container->get('auth.provider_collection');
		$auth_provider = $provider_collection->get_provider();

		$auth_provider_data = $auth_provider->get_login_data();
		if ($auth_provider_data)
		{
			if (isset($auth_provider_data['VARS']))
			{
				$this->template->assign_vars($auth_provider_data['VARS']);
			}

			if (isset($auth_provider_data['BLOCK_VAR_NAME']))
			{
				foreach ($auth_provider_data['BLOCK_VARS'] as $block_vars)
				{
					$this->template->assign_block_vars($auth_provider_data['BLOCK_VAR_NAME'], $block_vars);
				}
			}

			$this->template->assign_vars([
				'PROVIDER_TEMPLATE_FILE' => $auth_provider_data['TEMPLATE_FILE'],
			]);
		}

		// Assign template vars for timezone select
		phpbb_timezone_select($template, $user, $data['tz'], true);

		$template_vars = [
			'USERNAME'			=> $data['username'],
			'PASSWORD'			=> $data['new_password'],
			'PASSWORD_CONFIRM'	=> $data['password_confirm'],
			'EMAIL'				=> $data['email'],

			'L_REG_COND'				=> $l_reg_cond,
			'L_USERNAME_EXPLAIN'		=> $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_name_chars'])),
			'L_PASSWORD_EXPLAIN'		=> $this->language->lang($this->config['pass_complex'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_pass_chars'])),

			'S_LANG_OPTIONS'	=> language_select($data['lang']),
			'S_TZ_PRESELECT'	=> !$submit,
			'S_CONFIRM_REFRESH'	=> ($this->config['enable_confirm'] && $this->config['confirm_refresh']) ? true : false,
			'S_REGISTRATION'	=> true,
			'S_COPPA'			=> $coppa,
			'S_UCP_ACTION'		=> append_sid("{$this->root_path}ucp.$this->php_ext", 'mode=register'),

			'COOKIE_NAME'		=> $this->config['cookie_name'],
			'COOKIE_PATH'		=> $this->config['cookie_path'],
		];

		$tpl_name = 'ucp_register';

		/**
		 * Modify template data on the registration page
		 *
		 * @event core.ucp_register_modify_template_data
		 * @var array	template_vars		Array with template data
		 * @var array	data				Array with user data, read only
		 * @var array	error				Array with errors
		 * @var array	s_hidden_fields		Array with hidden field elements
		 * @var string	tpl_name			Template name
		 * @since 3.2.2-RC1
		 */
		$vars = [
			'template_vars',
			'data',
			'error',
			's_hidden_fields',
			'tpl_name',
		];
		extract($this->dispatcher->trigger_event('core.ucp_register_modify_template_data', compact($vars)));

		$template_vars = array_merge($template_vars, [
			'ERROR'				=> (count($error)) ? implode('<br />', $error) : '',
			'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
		]);

		$this->template->assign_vars($template_vars);

		//
		$this->user->profile_fields = [];

		// Generate profile fields -> Template Block Variable profile_fields
		$cp->generate_profile_fields('register', $this->user->get_iso_lang_id());

		//
		$this->tpl_name = $tpl_name;
	}

	/**
	 * Creates the login_link data array
	 *
	 * @return	array	Returns an array of all POST paramaters whose names
	 *					begin with 'login_link_'
	 */
	protected function get_login_link_data_array()
	{

		$var_names = $this->request->variable_names(\phpbb\request\request_interface::POST);
		$login_link_data = [];
		$string_start_length = strlen('login_link_');

		foreach ($var_names as $var_name)
		{
			if (strpos($var_name, 'login_link_') === 0)
			{
				$key_name = substr($var_name, $string_start_length);
				$login_link_data[$key_name] = $this->request->variable($var_name, '', false, \phpbb\request\request_interface::POST);
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
		$new_data = [];

		foreach ($data as $key => $value)
		{
			$new_data['login_link_' . $key] = $value;
		}

		return $new_data;
	}
}
