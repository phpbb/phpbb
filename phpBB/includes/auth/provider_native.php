<?php
/**
*
* @package auth
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* This auth provider uses the SQL database with a username/password login scheme.
*
* @package auth
*/
class phpbb_auth_provider_native extends phpbb_auth_common_provider
{
	protected $request;
	protected $db;
	protected $config;
	protected $user;

	protected $phpbb_root_path;
	protected $phpEx;
	protected $SID;
	protected $_SID;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config, phpbb_user $user)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;

		global $phpbb_root_path, $phpEx, $SID, $_SID;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->SID = $SID;
		$this->_SID = $_SID;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_configuration()
	{
		return array(
			'CUSTOM_ACP'		=> false,
			'CUSTOM_LOGIN_BOX'	=> false,
			'CUSTOM_REGISTER'	=> false,

			'NAME'		=> 'native',
			'OPTIONS'	=> array(
				'enabled'	=> array('setting' => $this->config['auth_provider_native_enabled'],	'lang' => 'AUTH_ENABLE',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => false),
				'admin'		=> array('setting' => $this->config['auth_provider_native_admin'],		'lang' => 'ALLOW_ADMIN_LOGIN',	'validate' => 'bool',	'type' => 'radio:yes_no',			'explain' => true),
			),
		);
	}

	public function process($admin = false)
	{
		$provider_config = $this->get_configuration();
		if (!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}

		$auth_action = $this->request->variable('auth_action', '', false, phpbb_request_interface::POST);

		switch($auth_action)
		{
			case 'login':
				$this->internal_login($admin);
				break;
			case 'register':
				$this->internal_register();
				break;
			default:
				throw new phpbb_auth_exception('INVALID_AUTH_ACTION');
		}
	}

	public function generate_registration(phpbb_template $template)
	{
		if ($this->config['enable_confirm'])
		{
			$captcha = new phpbb_auth_captcha($this->db, $this->config, $this->user);
		}

		$timezone = $this->config['board_timezone'];
		$coppa = $this->request->is_set('coppa') ? (int) $this->request->variable('coppa', false) : false;
		$data = array(
			'username'			=> utf8_normalize_nfc($this->request->variable('username', '', true)),
			'new_password'		=> $this->request->variable('new_password', '', true),
			'password_confirm'	=> $this->request->variable('password_confirm', '', true),
			'email'				=> strtolower($this->request->variable('email', '')),
			'lang'				=> basename($this->request->variable('lang', $this->user->lang_name)),
			'tz'				=> $this->request->variable('tz', $timezone),
		);

		$s_hidden_fields = array(
			'agreed'		=> 'true',
			'change_lang'	=> 0,

			'auth_provider'	=> 'native',
			'auth_action'	=> 'register',
			'auth_step'		=> 'process',
		);

		if ($this->config['coppa_enable'])
		{
			$s_hidden_fields['coppa'] = $coppa;
		}

		if ($this->config['enable_confirm'])
		{
			$s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields(CONFIRM_REG));
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		// Visual Confirmation - Show images
		if ($this->config['enable_confirm'])
		{
			$template->assign_vars(array(
				'CAPTCHA_TEMPLATE'		=> $captcha->get_template(CONFIRM_REG),
			));
		}

		$l_reg_cond = '';
		switch ($this->config['require_activation'])
		{
			case USER_ACTIVATION_SELF:
				$l_reg_cond = $this->user->lang['UCP_EMAIL_ACTIVATE'];
			break;

			case USER_ACTIVATION_ADMIN:
				$l_reg_cond = $this->user->lang['UCP_ADMIN_ACTIVATE'];
			break;
		}

		$timezone_selects = phpbb_timezone_select($user, $data['tz'], true);
		$template->assign_vars(array(
			'USERNAME'			=> $data['username'],
			'PASSWORD'			=> $data['new_password'],
			'PASSWORD_CONFIRM'	=> $data['password_confirm'],
			'EMAIL'				=> $data['email'],

			'L_REG_COND'				=> $l_reg_cond,
			'L_USERNAME_EXPLAIN'		=> $this->user->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->user->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->user->lang('CHARACTERS', (int) $this->config['max_name_chars'])),
			'L_PASSWORD_EXPLAIN'		=> $this->user->lang($this->config['pass_complex'] . '_EXPLAIN', $this->user->lang('CHARACTERS', (int) $this->config['min_pass_chars']), $this->user->lang('CHARACTERS', (int) $this->config['max_pass_chars'])),

			'S_LANG_OPTIONS'	=> language_select($data['lang']),
			'S_TZ_OPTIONS'			=> $timezone_selects['tz_select'],
			'S_TZ_DATE_OPTIONS'		=> $timezone_selects['tz_dates'],
			'S_CONFIRM_REFRESH'	=> ($this->config['enable_confirm'] && $this->config['confirm_refresh']) ? true : false,
			'S_REGISTRATION'	=> true,
			'S_COPPA'			=> $coppa,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> append_sid("{$this->phpbb_root_path}ucp.$this->phpEx", 'mode=register'),
		));

		$this->user->profile_fields = array();

		// Generate profile fields -> Template Block Variable profile_fields
		$cp = new custom_profile();
		$cp->generate_profile_fields('register', $this->user->get_iso_lang_id());

		$template->set_filenames(array(
			'ucp_register_native' => 'ucp_register_native.html')
		);
		$tpl = $template->assign_display('ucp_register_native', '', true);
		return $tpl;
	}

	/**
	 * Processes the nitty, gritty, ugly part of login.
	 *
	 * @param boolean $admin Is this admin authentication?
	 * @return int	The user id of the user.
	 * @throws phpbb_auth_exception
	 */
	protected function internal_login($admin)
	{
		// Get credential
		if ($admin)
		{
			$credential = $this->request->variable('credential', '');

			if (strspn($credential, 'abcdef0123456789') !== strlen($credential) || strlen($credential) != 32)
			{
				if ($this->user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
				throw new phpbb_auth_exception('NO_AUTH_ADMIN');
			}

			$password = $this->request->variable('password_' . $credential, '', true,  phpbb_request_interface::POST);
		}
		else
		{
			$password = $this->request->variable('password', '', true,  phpbb_request_interface::POST);
		}

		if ($password === '')
		{
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			throw new phpbb_auth_exception('NO_PASSWORD_SUPPLIED');
		}

		$username = $this->request->variable('username', '', true,  phpbb_request_interface::POST);
		if ($username === '')
		{
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			throw new phpbb_auth_exception('NO_USERNAME_SUPPLIED');
		}

		$autologin	= $this->request->is_set_post('autologin');
		$viewonline = !$this->request->is_set_post('viewonline');
		$admin 		= ($admin) ? 1 : 0;
		$viewonline = ($admin) ? $this->user->data['session_viewonline'] : $viewonline;

		// Check if the supplied username is equal to the one stored within the database if re-authenticating
		if ($admin && utf8_clean_string($username) != utf8_clean_string($this->user->data['username']))
		{
			// We log the attempt to use a different username...
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			throw new phpbb_auth_exception('NO_AUTH_ADMIN_USER_DIFFER');
		}

		$username_clean = utf8_clean_string($username);

		$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			$this->login_auth_fail(null, $username, $username_clean);
			throw new phpbb_auth_exception('LOGIN_ERROR_USERNAME');
		}

		$captcha = new phpbb_auth_captcha($this->db, $this->config, $this->user);
		if ($captcha->need_captcha($row['user_login_attempts']))
		{
			if (!$captcha->confirm_visual_login_captcha())
			{
				if ($admin && $this->user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
				$this->login_auth_fail((int)$row['user_id'], $username, utf8_clean_string($username));
			}
		}

		if ($row['user_pass_convert'])
		{
			$row = $this->user_password_convert($password, $row);
		}

		// Check password ...
		if (!$row['user_pass_convert'] && phpbb_check_hash($password, $row['user_password']))
		{
			// Check for old password hash...
			if (strlen($row['user_password']) == 32)
			{
				$hash = phpbb_hash($password);

				// Update the password in the users table to the new format
				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_password = '" . $this->db->sql_escape($hash) . "',
						user_pass_convert = 0
					WHERE user_id = {$row['user_id']}";
				$this->db->sql_query($sql);

				$row['user_password'] = $hash;
			}

			$sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE user_id = ' . $row['user_id'];
			$this->db->sql_query($sql);

			if ($row['user_login_attempts'] != 0)
			{
				// Successful, reset login attempts (the user passed all stages)
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_login_attempts = 0
					WHERE user_id = ' . $row['user_id'];
				$this->db->sql_query($sql);
			}

			// User inactive...
			if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
			{
				throw new phpbb_auth_exception('ACTIVE_ERROR');
			}

			// Complete login.
			$this->login($row['user_id'], $admin, $autologin, $viewonline);
			$this->redirect($this->request->variable('redirect', ''));
		}

		// Give status about wrong password...
		if ($admin && $this->user->data['is_registered'])
		{
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
		}
		$this->login_auth_fail((int)$row['user_id'], $username, utf8_clean_string($username));
		throw new phpbb_auth_exception('LOGIN_ERROR_PASSWORD');
	}

	/**
	 * Converts a user's password from its phpBB2 to its phpBB3 hash.
	 *
	 * @param str $password the password being claimed.
	 * @param array $row returned from a database call on the USERS_TABLE
	 * @return array $row as passed in with any necessary modifications made.
	 * @throws phpbb_auth_exception
	 */
	protected function user_password_convert($password, $row)
	{
		// enable super globals to get literal value
		// this is needed to prevent unicode normalization
		$super_globals_disabled = $this->request->super_globals_disabled();
		if ($super_globals_disabled)
		{
			$this->request->enable_super_globals();
		}

		// in phpBB2 passwords were used exactly as they were sent, with addslashes applied
		$password_old_format = isset($_REQUEST['password']) ? (string) $_REQUEST['password'] : '';
		$password_old_format = (!STRIP) ? addslashes($password_old_format) : $password_old_format;
		$password_new_format = $this->request->variable('password', '', true);

		if ($super_globals_disabled)
		{
			$this->request->disable_super_globals();
		}

		if ($password == $password_new_format)
		{
			if (!function_exists('utf8_to_cp1252'))
			{
				include($this->phpbb_root_path . 'includes/utf/data/recode_basic.' . $this->phpEx);
			}

			// cp1252 is phpBB2's default encoding, characters outside ASCII range might work when converted into that encoding
			// plain md5 support left in for conversions from other systems.
			if ((strlen($row['user_password']) == 34 && (phpbb_check_hash(md5($password_old_format), $row['user_password']) || phpbb_check_hash(md5(utf8_to_cp1252($password_old_format)), $row['user_password'])))
				|| (strlen($row['user_password']) == 32  && (md5($password_old_format) == $row['user_password'] || md5(utf8_to_cp1252($password_old_format)) == $row['user_password'])))
			{
				$hash = phpbb_hash($password_new_format);

				// Update the password in the users table to the new format and remove user_pass_convert flag
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_password = \'' . $this->db->sql_escape($hash) . '\',
						user_pass_convert = 0
					WHERE user_id = ' . $row['user_id'];
				$this->db->sql_query($sql);

				$row['user_pass_convert'] = 0;
				$row['user_password'] = $hash;
			}
			else
			{
				throw new phpbb_auth_exception('LOGIN_ERROR_PASSWORD_CONVERT');
			}
		}

		return $row;
	}

	protected function internal_register()
	{
		if (!check_form_key('ucp_register'))
		{
			$error[] = $this->user->lang['FORM_INVALID'];
		}

		$coppa			= $this->request->is_set('coppa') ? (int) $this->request->variable('coppa', false) : false;

		$cp = new custom_profile();
		$error = $cp_data = array();

		$data = array(
			'username'			=> utf8_normalize_nfc($this->request->variable('username', '', true)),
			'new_password'		=> $this->request->variable('new_password', '', true),
			'password_confirm'	=> $this->request->variable('password_confirm', '', true),
			'email'				=> strtolower($this->request->variable('email', '')),
			'lang'				=> basename($this->request->variable('lang', $this->user->lang_name)),
			'tz'				=> $this->request->variable('tz', (float) $this->config['board_timezone']),
		);

		// Replace "error" strings with their real, localised form
		$error = array_map(array($this->user, 'lang'), $error);

		if ($this->config['enable_confirm'])
		{
			$captcha = new phpbb_auth_captcha($this->db, $this->config, $this->user);
			$captcha_response = $captcha->confirm_visual_registration_captcha($data);
			if ($captcha_response !== true)
			{
				$error = array_merge($error, $captcha_response);
			}
		}

		// DNSBL check
		if ($this->config['check_dnsbl'])
		{
			if (($dnsbl = $this->user->check_dnsbl('register')) !== false)
			{
				$error[] = sprintf($this->user->lang['IP_BLACKLISTED'], $this->user->ip, $dnsbl[1]);
			}
		}

		// validate custom profile fields
		$cp->submit_cp_field('register', $this->user->get_iso_lang_id(), $cp_data, $error);

		if (!sizeof($error))
		{
			if ($data['new_password'] != $data['password_confirm'])
			{
				$error[] = $this->user->lang['NEW_PASSWORD_ERROR'];
			}
		}

		if (!sizeof($error))
		{
			$this->register($data, $coppa, $cp_data);
			if ($this->config['enable_confirm'] && isset($captcha))
			{
				$captcha->reset_registration_captcha();
			}
			return;
		}

		throw new phpbb_auth_exception(implode('<br />', $error));
	}
}
