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
class phpbb_auth_provider_ldap extends phpbb_auth_common_provider
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
			'NAME'		=> 'ldap',
			'OPTIONS'	=> array(
				'enabled'			=> array('setting' => $this->config['auth_provider_ldap_enabled'],		'lang' => 'AUTH_ENABLE',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => false),
				'admin'				=> array('setting' => $this->config['auth_provider_ldap_admin'],		'lang' => 'ALLOW_ADMIN_LOGIN',	'validate' => 'bool',	'type' => 'radio:yes_no',			'explain' => true),
				'ldap_server'		=> array('setting' => $this->config['auth_provider_ldap_server'],		'lang' => 'LDAP_SERVER',		'validate' => 'string',	'type' => 'text:40:255',			'explain' => true),
				'ldap_port'			=> array('setting' => $this->config['auth_provider_ldap_port'],			'lang' => 'LDAP_PORT',			'validate' => 'string',	'type' => 'text:40:255',			'explain' => true),
				'ldap_base_dn'		=> array('setting' => $this->config['auth_provider_ldap_base_dn'],		'lang' => 'LDAP_DN',			'validate' => 'string',	'type' => 'text:40:255',			'explain' => true),
				'ldap_uid'			=> array('setting' => $this->config['auth_provider_ldap_uid'],			'lang' => 'LDAP_UID',			'validate' => 'string',	'type' => 'text:40:255',			'explain' => true),
				'ldap_user_filter'	=> array('setting' => $this->config['auth_provider_ldap_user_filter'],	'lang' => 'LDAP_USER_FILTER',	'validate' => 'string',	'type' => 'text:40:255',			'explain' => true),
				'ldap_email'		=> array('setting' => $this->config['auth_provider_ldap_email'],		'lang' => 'LDAP_EMAIL',			'validate' => 'string',	'type' => 'text:40:255',			'explain' => true),
				'ldap_user'			=> array('setting' => $this->config['auth_provider_ldap_user'],			'lang' => 'LDAP_USER',			'validate' => 'string',	'type' => 'text:40:255',			'explain' => true),
				'ldap_password'		=> array('setting' => $this->config['auth_provider_ldap_password'],		'lang' => 'LDAP_PASSWORD',		'validate' => 'string',	'type' => 'text:40:255',			'explain' => true),
			),
		);
	}

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
				$this->internal_login($admin);
				$this->redirect($this->request->variable('redirect_to', ''));
				break;
			case 'register':
				break;
			default:
				throw new phpbb_auth_exception('INVALID_AUTH_ACTION');
		}
	}

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

			$password = $this->request->variable('olympus_password_' . $credential, '', true,  phpbb_request_interface::POST);
		}
		else
		{
			$password = $this->request->variable('olympus_password', '', true,  phpbb_request_interface::POST);
		}

		if ($password === '')
		{
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			throw new phpbb_auth_exception('NO_PASSWORD_SUPPLIED');
		}

		$username = $this->request->variable('olympus_username', '', true,  phpbb_request_interface::POST);
		if ($username === '')
		{
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
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

		if (!@extension_loaded('ldap'))
		{
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
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
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			throw new phpbb_auth_exception('LDAP_NO_SERVER_CONNECTION');
		}

		@ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		@ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

		if ($this->config['ldap_user'] || $this->config['ldap_password'])
		{
			if (!@ldap_bind($ldap, htmlspecialchars_decode($this->config['ldap_user']), htmlspecialchars_decode($this->config['ldap_password'])))
			{
				if ($admin && $this->user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
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

					if (!$this->login((int)$row['user_id'], $admin, $autologin, $viewonline))
					{
						$this->login_auth_fail((int)$row['user_id'], $username, utf8_clean_string($username));
					}

					return;
				}
				if ($admin && $this->user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
				throw new phpbb_auth_exception('USER_NOT_FOUND');
			}
			else
			{
				unset($ldap_result);
				@ldap_close($ldap);

				if ($admin && $this->user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
				$this->login_auth_fail(null, $username, utf8_clean_string($username));
				throw new phpbb_auth_exception('LOGIN_ERROR_PASSWORD');
			}
		}

		@ldap_close($ldap);

		if ($admin && $this->user->data['is_registered'])
		{
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
		}
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
