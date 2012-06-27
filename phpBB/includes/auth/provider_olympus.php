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
* This auth provider uses the legacy usernam/password system.
*
* @package auth
*/
class phpbb_auth_provider_olympus extends phpbb_auth_common_provider
{
	protected $request;
	protected $db;
	protected $config;
	protected $user;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config, phpbb_user $user)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_configuration()
	{
		return array(
			'CUSTOM_ACP'=> false,
			'NAME'		=> 'olympus',
			'OPTIONS'	=> array(
				'enabled'	=> array('setting' => $this->config['olympus_enabled'], 'lang' => 'AUTH_ENABLE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
				'admin'		=> array('setting' => $this->config['olympus_admin'], 'lang' => 'ALLOW_ADMIN_LOGIN', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
				'method'	=> array('setting' => $this->config['olympus_method'], 'lang' => 'AUTH_METHOD',	'validate' => 'string',	'type' => 'select', 'method' => 'select_auth_method', 'explain' => false),
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_login_box(phpbb_template $template, $redirect = '', $admin = false, $s_display = true)
	{
		$provider_config = $this->get_configuration();
		if (!$provider_config['OPTIONS']['enabled']['setting']
				|| (!$provider_config['OPTIONS']['admin']['setting'] && $admin == true))
		{
			return null;
		}

		global $phpbb_root_path, $phpEx;

		$s_login_action = ((!defined('ADMIN_START')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') : append_sid("index.$phpEx", false, true, $this->user->session_id));
		$s_autologin_enabled = ($this->config['allow_autologin']) ? true : false;

		$s_hidden_fields = array(
			'sid'			=> $this->user->session_id,
			'auth_provider'	=> 'olympus',
			'auth_action'	=> 'login',
		);
		if ($redirect)
		{
			$s_hidden_fields['redirect'] = $redirect;
		}
		else
		{
			$s_hidden_fields['redirect'] = build_url();
		}
		if ($admin)
		{
			$credential = md5(unique_id());
			$s_hidden_fields['credential'] = $credential;
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$username = ($admin) ? $this->user->data['username'] : '';
		$username_credential = 'olympus_username';

		$password_credential = ($admin) ? 'olympus_password_' . $credential : 'olympus_password';

		$u_send_password = ($this->config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') : '';
		$u_resend_activation = ($this->config['require_activation'] == USER_ACTIVATION_SELF && $this->config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=resend_act') : '';

		$template->assign_vars(array(
			'ADMIN'					=> $admin,
			'USERNAME'				=> $username,
			'USERNAME_CREDENTIAL'	=> $username_credential,
			'PASSWORD_CREDENTIAL'	=> $password_credential,

			'S_AUTOLOGIN_ENABLED'	=> $s_autologin_enabled,
			'S_DISPLAY_FULL_LOGIN'	=> ($s_display) ? true : false,
			'S_LOGIN_ACTION'		=> $s_login_action,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,

			'U_SEND_PASSWORD'		=> $u_send_password,
			'U_RESEND_ACTIVATION'	=> $u_resend_activation,
		));

		$template->set_filenames(array(
			'login_body_olympus' => 'login_body_olympus.html')
		);
		$tpl = $template->assign_display('login_body_olympus', '', true);
		if (!$tpl)
		{
			return null;
		}
		return $tpl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function process($admin = false)
	{
		$provider_config = $this->get_configuration();
		if(!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}

		$auth_action = $this->request->variable('auth_action', '', false, phpbb_request_interface::POST);

		switch($auth_action)
		{
			case 'login':
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

					$password = $this->request->variable('olympus_password_' . $credential, '', true);
				}
				else
				{
					$password = $this->request->variable('olympus_password', '', true);
				}

				if ($password === '')
				{
					throw new phpbb_auth_exception('NO_PASSWORD_SUPPLIED');
				}

				$username = $this->request->variable('olympus_username', '', true);
				if ($username === '')
				{
					throw new phpbb_auth_exception('NO_USERNAME_SUPPLIED');
				}

				$autologin	= $this->request->is_set_post('autologin');
				$viewonline = (int) !$this->request->is_set_post('viewonline');
				$admin 		= ($admin) ? 1 : 0;
				$viewonline = ($admin) ? $this->user->data['session_viewonline'] : $viewonline;

				// Check if the supplied username is equal to the one stored within the database if re-authenticating
				if ($admin && utf8_clean_string($username) != utf8_clean_string($this->user->data['username']))
				{
					// We log the attempt to use a different username...
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
					throw new phpbb_auth_exception('NO_AUTH_ADMIN_USER_DIFFER');
				}

				$login_process = 'login_db';//'login_' . $provider_config['OPTIONS']['method']['setting'];
				if (!method_exists($this, $login_process))
				{
					throw new phpbb_auth_exception('NO_OLYMPUS_PROCESS_FOUND');
				}

				$user_id = $this->$login_process($username, $password);

				if (!$this->login($user_id, $admin, $autologin, $viewonline))
				{
					$this->login_auth_fail($user_id, $username, utf8_clean_string($username));
				}
				$this->redirect($this->request->variable('redirect_to', ''));
				break;
			case 'register':
				break;
			default:
				throw new phpbb_auth_exception('INVALID_AUTH_ACTION');
		}
	}

	/**
	 * Processes an Olympus DB login.
	 *
	 * @global string $phpbb_root_path
	 * @global string $phpEx
	 * @param string $username The claimed username.
	 * @param string $password The claimed password.
	 * @return int	The user id of the user.
	 * @throws phpbb_auth_exception
	 */
	protected function login_db($username, $password)
	{
		$username_clean = utf8_clean_string($username);

		$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			$this->login_auth_fail(null, $username, $username_clean);
			throw new phpbb_auth_exception('LOGIN_ERROR_USERNAME');
		}

		// Get number of login attempts
		if (($this->user->ip && !$this->config['ip_login_limit_use_forwarded']) ||
		($this->user->forwarded_for && $this->config['ip_login_limit_use_forwarded']))
		{
			$sql = 'SELECT COUNT(*) AS attempts
				FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE attempt_time > ' . (time() - (int) $this->config['ip_login_limit_time']);
			if ($this->config['ip_login_limit_use_forwarded'])
			{
				$sql .= " AND attempt_forwarded_for = '" . $this->db->sql_escape($this->user->forwarded_for) . "'";
			}
			else
			{
				$sql .= " AND attempt_ip = '" . $this->db->sql_escape($this->user->ip) . "' ";
			}

			$result = $this->db->sql_query($sql);
			$attempts = (int) $this->db->sql_fetchfield('attempts');
			$this->db->sql_freeresult($result);
		}
		else
		{
			$attempts = 0;
		}
		$show_captcha = ($this->config['max_login_attempts'] && $row['user_login_attempts'] >= $this->config['max_login_attempts']) ||
						($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max']);

		// If there are too much login attempts, we need to check for an confirm image
		// Every auth module is able to define what to do by itself...
		if ($show_captcha)
		{
			// Visual Confirmation handling
			if (!class_exists('phpbb_captcha_factory', false))
			{
				global $phpbb_root_path, $phpEx;
				include ($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
			}

			$captcha = phpbb_captcha_factory::get_instance($this->config['captcha_plugin']);
			$captcha->init(CONFIRM_LOGIN);
			$vc_response = $captcha->validate($row);
			if ($vc_response)
			{
				throw new phpbb_auth_exception('LOGIN_ERROR_ATTEMPTS');
			}
			else
			{
				$captcha->reset();
			}

		}

		if ($row['user_pass_convert'])
		{
			$this->user_convert_password($password, $row);
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

			// Successful login... set user_login_attempts to zero...
			return (int)$row['user_id'];
		}

		// Give status about wrong password...
		$this->login_auth_fail((int)$row['user_id'], $username, utf8_clean_string($username));
		throw new phpbb_auth_exception('LOGIN_ERROR_PASSWORD');
	}

	/**
	 * Processes Olympus Apache login.
	 *
	 * @param string $username The claimed username
	 * @param string $password The claimed password
	 * @return int User id
	 * @throws phpbb_auth_exception
	 */
	protected function login_apache($username, $password)
	{
		if (!$this->request->is_set('PHP_AUTH_USER', phpbb_request_interface::SERVER))
		{
			throw new phpbb_auth_exception('LOGIN_ERROR_EXTERNAL_AUTH_APACHE');
		}

		$php_auth_user = htmlspecialchars_decode($this->request->server('PHP_AUTH_USER'));
		$php_auth_pw = htmlspecialchars_decode($this->request->server('PHP_AUTH_PW'));

		if (!empty($php_auth_user) && !empty($php_auth_pw))
		{
			if ($php_auth_user !== $username)
			{
				$this->login_auth_fail(null, $username, utf8_clean_string($username));
				throw new phpbb_auth_exception('LOGIN_ERROR_USERNAME');
			}

			$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type
				FROM ' . USERS_TABLE . "
				WHERE username = '" . $this->db->sql_escape($php_auth_user) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				// User inactive.
				if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
				{
					throw new phpbb_auth_exception('ACTIVE_ERROR');
				}

				return (int)$row['user_id'];
			}
		}

		throw new phpbb_auth_exception('LOGIN_ERROR_EXTERNAL_AUTH_APACHE');
	}

	/**
	 * Processes an Olympus ldap login.
	 *
	 * @param string $username The claimed username
	 * @param string $password The claimed password
	 * @return int User id
	 * @throws phpbb_auth_exception
	 */
	protected function login_ldap($username, $password)
	{
		if (!@extension_loaded('ldap'))
		{
			throw new phpbb_auth_exception('LDAP_NO_LDAP_EXTENSION');
		}

		$this->config['ldap_port'] = (int) $this->config['ldap_port'];
		if ($this->config['ldap_port'])
		{
			$ldap = @ldap_connect($this->config['ldap_server'], $this->config['ldap_port']);
		}
		else
		{
			$ldap = @ldap_connect($this->config['ldap_server']);
		}

		if (!$ldap)
		{
			throw new phpbb_auth_exception('LDAP_NO_SERVER_CONNECTION');
		}

		@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		@ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		if ($this->config['ldap_user'] || $this->config['ldap_password'])
		{
			if (!@ldap_bind($ldap, htmlspecialchars_decode($this->config['ldap_user']), htmlspecialchars_decode($this->config['ldap_password'])))
			{
				throw new phpbb_auth_exception($this->user->lang['LDAP_NO_SERVER_CONNECTION']);
			}
		}

		$search = @ldap_search(
			$ldap,
			htmlspecialchars_decode($this->config['ldap_base_dn']),
			$this->ldap_user_filter($username),
			(empty($this->config['ldap_email'])) ?
				array(htmlspecialchars_decode($this->config['ldap_uid'])) :
				array(htmlspecialchars_decode($this->config['ldap_uid']), htmlspecialchars_decode($this->config['ldap_email'])),
			0,
			1
		);

		$ldap_result = @ldap_get_entries($ldap, $search);

		if (is_array($ldap_result) && sizeof($ldap_result) > 1)
		{
			if (@ldap_bind($ldap, $ldap_result[0]['dn'], htmlspecialchars_decode($password)))
			{
				@ldap_close($ldap);

				$sql ='SELECT user_id, username, user_password, user_passchg, user_email, user_type
					FROM ' . USERS_TABLE . "
					WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row)
				{
					unset($ldap_result);

					// User inactive...
					if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
					{
						throw new phpbb_auth_exception('ACTIVE_ERROR');
					}

					return (int)$row['user_id'];
				}
				throw new phpbb_auth_exception('USER_NOT_FOUND');
			}
			else
			{
				unset($ldap_result);
				@ldap_close($ldap);

				$this->login_auth_fail(null, $username, utf8_clean_string($username));
				throw new phpbb_auth_exception('LOGIN_ERROR_PASSWORD');
			}
		}

		@ldap_close($ldap);

		$this->login_auth_fail(null, $username, utf8_clean_string($username));
		throw new phpbb_auth_exception('LOGIN_ERROR_USERNAME');
	}

	/**
	 * Generates a filter string for ldap_search to find a user
	 *
	 * @param str $username Username identifying the searched user
	 * @return str A filter string for ldap_search
	 */
	protected function ldap_user_filter($username)
	{
		global $config;

		$filter = '(' . $config['ldap_uid'] . '=' . ldap_escape(htmlspecialchars_decode($username)) . ')';
		if ($config['ldap_user_filter'])
		{
			$_filter = ($config['ldap_user_filter'][0] == '(' && substr($config['ldap_user_filter'], -1) == ')') ? $config['ldap_user_filter'] : "({$config['ldap_user_filter']})";
			$filter = "(&{$filter}{$_filter})";
		}
		return $filter;
	}

	/**
	 * Escapes an LDAP AttributeValue
	 *
	 * @return str A LDAP escaped string
	 */
	protected function ldap_escape($string)
	{
		return str_replace(array('*', '\\', '(', ')'), array('\\*', '\\\\', '\\(', '\\)'), $string);
	}

	/**
	 * Converts a user's password from its phpBB2 to its phpBB3 hash.
	 *
	 * @global str $phpbb_root_path
	 * @global str $phpEx
	 * @param str $password the password being claimed.
	 * @param array $row returned from a database call on the USERS_TABLE
	 * @return array $row as passed in with any necessary modifications made.
	 * @throws phpbb_auth_exception
	 */
	protected function user_convert_password($password, $row)
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
				global $phpbb_root_path, $phpEx;
				include($phpbb_root_path . 'includes/utf/data/recode_basic.' . $phpEx);
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

	/**
	 * Checks whether the user is identified to apache
	 * Only allow changing authentication to apache if the user is identified
	 * Called in acp_board while setting authentication plugins
	 *
	 * @return boolean|string false if the user is identified and else an error message
	 */
	public function init_apache()
	{
		if (!$this->request->is_set('PHP_AUTH_USER', phpbb_request_interface::SERVER) || $this->user->data['username'] !== htmlspecialchars_decode($this->request->server('PHP_AUTH_USER')))
		{
			return $this->user->lang['APACHE_SETUP_BEFORE_USE'];
		}
		return false;
	}

	/**
	 * Connect to ldap server
	 * Only allow changing authentication to ldap if we can connect to the ldap server
	 * Called in acp_board while setting authentication plugins
	 *
	 * @return boolean|string false if the user is identified and else an error message
	 */
	public function init_ldap()
	{
		if (!@extension_loaded('ldap'))
		{
			return $this->user->lang['LDAP_NO_LDAP_EXTENSION'];
		}

		$this->config['ldap_port'] = (int) $this->config['ldap_port'];
		if ($this->config['ldap_port'])
		{
			$ldap = @ldap_connect($this->config['ldap_server'], $this->config['ldap_port']);
		}
		else
		{
			$ldap = @ldap_connect($this->config['ldap_server']);
		}

		if (!$ldap)
		{
			return $this->user->lang['LDAP_NO_SERVER_CONNECTION'];
		}

		@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		@ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		if ($this->config['ldap_user'] || $this->config['ldap_password'])
		{
			if (!@ldap_bind($ldap, htmlspecialchars_decode($this->config['ldap_user']), htmlspecialchars_decode($this->config['ldap_password'])))
			{
				return $this->user->lang['LDAP_INCORRECT_USER_PASSWORD'];
			}
		}

		// ldap_connect only checks whether the specified server is valid, so the connection might still fail
		$search = @ldap_search(
			$ldap,
			htmlspecialchars_decode($this->config['ldap_base_dn']),
			ldap_user_filter($this->user->data['username']),
			(empty($this->config['ldap_email'])) ?
				array(htmlspecialchars_decode($this->config['ldap_uid'])) :
				array(htmlspecialchars_decode($this->config['ldap_uid']), htmlspecialchars_decode($this->config['ldap_email'])),
			0,
			1
		);

		if ($search === false)
		{
			return $this->user->lang['LDAP_SEARCH_FAILED'];
		}

		$result = @ldap_get_entries($ldap, $search);

		@ldap_close($ldap);


		if (!is_array($result) || sizeof($result) < 2)
		{
			return sprintf($this->user->lang['LDAP_NO_IDENTITY'], $this->user->data['username']);
		}

		if (!empty($this->config['ldap_email']) && !isset($result[0][htmlspecialchars_decode($this->config['ldap_email'])]))
		{
			return $this->user->lang['LDAP_NO_EMAIL'];
		}

		return false;
	}
}
