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
}
