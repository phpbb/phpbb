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
* This auth provider uses apache to authenticate users.
*
* @package auth
*/
class phpbb_auth_provider_apache extends phpbb_auth_common_provider
	implements phpbb_auth_provider_sso_interface, phpbb_auth_provider_acp_init_interface
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
	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;

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
			'NAME'		=> 'apache',
			'OPTIONS'	=> array(
				'enabled'	=> array('setting' => $this->config['auth_provider_apache_enabled'],	'lang' => 'AUTH_ENABLE',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => false),
				'admin'		=> array('setting' => $this->config['auth_provider_apache_admin'],		'lang' => 'ALLOW_ADMIN_LOGIN',	'validate' => 'bool',	'type' => 'radio:yes_no',			'explain' => true),
			),
		);
	}

	/**
	 * Processes a login attempt for this auth provider. If the account does not
	 * exist, internal_login will create it.
	 *
	 * @param boolean $admin
	 * @throws phpbb_auth_exception
	 */
	public function process($admin = false)
	{
		$provider_config = $this->get_configuration();
		if (!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}

		$auth_action = $this->request->variable('auth_action', '', false, phpbb_request_interface::POST);

		switch ($auth_action)
		{
			case 'login':
				$this->internal_login($admin);
				break;
			default:
				throw new phpbb_auth_exception('INVALID_AUTH_ACTION');
		}
	}

	/**
	 * Processes a login request and will create accounts for valid logins if
	 * one does not exist.
	 *
	 * @param boolean $admin
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

			$password = $this->request->variable('olympus_password_' . $credential, '', true);
		}
		else
		{
			$password = $this->request->variable('olympus_password', '', true);
		}

		if ($password === '')
		{
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			throw new phpbb_auth_exception('NO_PASSWORD_SUPPLIED');
		}

		$username = $this->request->variable('olympus_username', '', true);
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

		if (!$this->request->is_set('PHP_AUTH_USER', phpbb_request_interface::SERVER))
		{
			if ($admin && $this->user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			throw new phpbb_auth_exception('LOGIN_ERROR_EXTERNAL_AUTH_APACHE');
		}

		$php_auth_user = htmlspecialchars_decode($this->request->server('PHP_AUTH_USER'));
		$php_auth_pw = htmlspecialchars_decode($this->request->server('PHP_AUTH_PW'));

		if (!empty($php_auth_user) && !empty($php_auth_pw))
		{
			if ($php_auth_user !== $username)
			{
				if ($admin && $this->user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
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

				$this->login((int)$row['user_id'], $admin, $autologin, $viewonline);
				$this->redirect($this->request->variable('redirect', ''));
			}

			$user_id = $this->login_create_profile($this->login_user_row($php_auth_user, $php_auth_pw));
			$this->login((int)$user_id, $admin, $autologin, $viewonline);
			$this->redirect($this->request->variable('redirect', ''));
		}

		if ($admin && $this->user->data['is_registered'])
		{
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
		}
		throw new phpbb_auth_exception('LOGIN_ERROR_EXTERNAL_AUTH_APACHE');
	}

	/**
	 * Checks whether the user is identified to apache
	 * Only allow changing authentication to apache if the user is identified
	 * Called in acp_board while setting authentication plugins
	 *
	 * @throws phpbb_auth_exception On failure
	 */
	public function init()
	{
		if (!$this->request->is_set('PHP_AUTH_USER', phpbb_request_interface::SERVER) || $this->user->data['username'] !== htmlspecialchars_decode($this->request->server('PHP_AUTH_USER')))
		{
			throw new phpbb_auth_exception($this->user->lang['APACHE_SETUP_BEFORE_USE']);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate_session($user)
	{
		// Check if PHP_AUTH_USER is set and handle this case
		if ($this->request->is_set('PHP_AUTH_USER', phpbb_request_interface::SERVER))
		{
			$php_auth_user = $this->request->server('PHP_AUTH_USER');

			return ($php_auth_user === $user['username']) ? true : false;
		}

		// PHP_AUTH_USER is not set. A valid session is now determined by the user type (anonymous/bot or not)
		if ($user['user_type'] == USER_IGNORE)
		{
			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function autologin()
	{
		if (!$this->request->is_set('PHP_AUTH_USER', phpbb_request_interface::SERVER))
		{
			return array();
		}

		$php_auth_user = htmlspecialchars_decode($this->request->server('PHP_AUTH_USER'));
		$php_auth_pw = htmlspecialchars_decode($this->request->server('PHP_AUTH_PW'));

		if (!empty($php_auth_user) && !empty($php_auth_pw))
		{
			set_var($php_auth_user, $php_auth_user, 'string', true);
			set_var($php_auth_pw, $php_auth_pw, 'string', true);

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . "
				WHERE username = '" . $this->db->sql_escape($php_auth_user) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				return ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ? array() : $row;
			}

			if (!function_exists('user_add'))
			{
				global $phpbb_root_path, $phpEx;

				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			}

			// create the user if he does not exist yet
			$this->login_create_profile($this->login_user_row($php_auth_user, $php_auth_pw));

			$sql = 'SELECT *
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($php_auth_user)) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				return $row;
			}
		}

		return array();
	}
}
