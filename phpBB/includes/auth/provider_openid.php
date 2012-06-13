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
* This auth provider uses the OpenID form of login.
*
* @package auth
*/
class phpbb_auth_provider_openid implements phpbb_auth_provider_interface
{
	protected $request;
	protected $db;
	protected $config;
	protected $user;

	/**
	 * This is the array of configurable OpenID Simple Registration Extension
	 * data items that will be requested during registration using a third party
	 * provider.
	 * https://openid.net/specs/openid-simple-registration-extension-1_0.html
	 *
	 * @var array
	 */
	protected $sreg_props;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config, phpbb_user $user)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->sreg_props = array(
			"nickname"	=> true,
			"email"		=> true,
			"dob"		=> true,
			"gender"	=> false,
			"language"	=> false,
			"timezone"	=> false,
		);
	}

	/**
	 * Performs the login request on the external server specified by
	 * openid_identifier. Redirects the browser first to the external server
	 * for authentication then back to /check_auth_openid.php to complete
	 * login.
	 */
	public function process()
	{
		$storage = new phpbb_auth_zend_storage($this->db);
		$consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);

		if ($this->request->variable('openid_identifier', '') == '')
		{
			throw new phpbb_auth_exception('No OpenID identifier supplied.');
		}
		else
		{
			$identifier = $this->request->variable('openid_identifier', '');
		}

		$root = 'http://192.168.0.112/';

		if ($this->request->variable('login', ''))
		{
			// Login
			$autologin = $this->request->variable('autologin', 'off');
			$viewonline = $this->request->variable('viewonline', 'off');
			$return_to = 'check_auth_openid.php?phpbb.process=login&phpbb.autologin=' . $autologin . '&phpbb.viewonline=' . $viewonline;
			$extensions = null;
		}
		elseif ($this->request->variable('Link', ''))
		{
			if(!isset($this->user->data['user_id']))
			{
				throw new phpbb_auth_exception('You may only link a logged in phpBB user to an OpenID provider.');
			}
			$return_to = 'check_auth_openid.php?phpbb.process=link&phpbb.user_id=' . $this->user->data['user_id'];
			$extensions = null;
		}
		else
		{
			// Register
			$return_to = 'phpBB/check_auth_openid.php?phpbb_process=register';
			$extensions = array(
				'sreg'	=> new Zend\OpenId\Extension\Sreg($this->sreg_props, null, 1.0),
			);
		}

		// Enable super globals so Zend Framework does not throw errors.
		$this->request->enable_super_globals();

		if (!$consumer->login($identifier, $return_to, $root, $extensions))
		{
			$this->request->disable_super_globals();
			throw new phpbb_auth_exception($consumer->getError());
		}
		$this->request->disable_super_globals();
	}

	/**
	 * Verifies the returned values from the external server.
	 * Performs login and registration.
	 * Registration supports both sreg
	 * (https://openid.net/specs/openid-simple-registration-extension-1_0.html)
	 * and attribute exchange
	 * (https://openid.net/specs/openid-attribute-exchange-1_0.html).
	 */
	public function verify()
	{
		$storage = new phpbb_auth_zend_storage($this->db);
		$storage->purgeNonces(time());
		$consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);

		$process = $this->request->variable('phpbb.process', 'login');
		if ($process == 'register')
		{
			$extensions = array(
				'sreg'	=> new Zend\OpenId\Extension\Sreg($this->sreg_props, null, 1.0),
			);
		}
		else
		{
			$extensions = null;
		}

		// Enable super globals so Zend Framework does not throw errors.
		$this->request->enable_super_globals();
		$id = '';
		if ($consumer->verify($_GET, $id, $extensions))
		{
			if ($process == 'login' && $this->login())
			{
				$this->request->disable_super_globals();
				return true;
			}
			elseif ($process == 'register' && $this->register())
			{
				$this->request->disable_super_globals();
				return true;
			}
			elseif ($process == 'link' && $this->link())
			{
				$this->request->disable_super_globals();
				return true;
			}
		}
		else
		{
			$this->request->disable_super_globals();
			throw new phpbb_auth_exception('OpenID authentication failed.');
		}

		$this->request->disable_super_globals();
		return false;
	}

	/**
	 * Perform phpBB login from data gathered returned from a third party
	 * provider.
	 * 
	 * @return true on success
	 */
	protected function login()
	{
		// Check to see if a link exists.
		$link_manager = new phpbb_auth_link_manager($this->db);
		$link = $link_manager->get_link_by_index('openid', $this->request->variable('openid_identity', ''));
		if (!$link)
		{
			throw new phpbb_auth_exception('Can not find a link between ' . $this->request->variable('openid_identity', '') . ' and any known account.');
		}

		// Get user
		$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $link['user_id'];
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Delete login attemps
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
			$this->request->disable_super_globals();
			throw new phpbb_auth_exception('Only actived users may login.');
		}

		// Create a non-admin session.
		$autologin = (bool)$this->request->variable('phpbb.autologin', false);
		$viewonline = (bool)$this->request->variable('phpbb.viewonline', true);
		$result = $this->user->session_create($row['user_id'], false , $autologin, $viewonline);
		if ($result === true)
		{
			return true;
		}
		else
		{
			$this->request->disable_super_globals();
			throw new phpbb_auth_exception($result);
		}
	}

	/**
	 * Register an OpenID user with phpBB. Supports both simple registration
	 * extension
	 * (https://openid.net/specs/openid-simple-registration-extension-1_0.html)
	 * and attribute exchange
	 * (https://openid.net/specs/openid-attribute-exchange-1_0.html).
	 */
	protected function register()
	{
		// Data array to hold all returned values.
		$data = array();

		// Data that must be supplied in order for registration to occur.
		$req_data = array();

		// Handle OpenId simple registration extension (sreg) information from
		// the OpenID provider.
		$sreg_data = $extensions['sreg']->getProperties();
		if (!is_empty($sreg_data))
		{
			if (!isset($sreg_data['email']))
			{
				$req_data[] = 'email';
			}
			else
			{
				$data['email'] = $sreg_data['email'];
			}

			if (!isset($sreg_data['dob']))
			{
				$req_data[] = 'dob';
			}
			else
			{
				$data['dob'] = $sreg_data['dob'];
			}

			if (!isset($sreg_data['nickname']))
			{
				$req_data[] = 'nickname';
			}
			else
			{
				// Check to see if the username already exists.
				$sql = 'SELECT username
					FROM ' . USERS_TABLE . "
					WHERE username_clean = '" . $this->db->sql_escape($sreg_data['nickname']) . "'";
				$result = $this->db->sql_query($sql);
				if ($result)
				{
					$req_data['Username already exists'] = 'nickname';
				}
				$data['username'] = $sreg_data['nickname'];
			}

			if (isset($sreg_data['gender']))
			{
				$data['gender'] = $sreg_data['gender'];
			}

			if (isset($sreg_data['language']))
			{
				$data['language'] = $sreg_data['language'];
			}

			if (isset($sreg_data['timezone']))
			{
				$utc_dtz = new DateTimeZone('UTC');
				$registrant_dtz = new DateTimeZone($sreg_data['timezone']);

				$utc_dt = new DateTime('now', $utc_dtz);
				$registrant_dt = new DateTime('now', $registrant_dtz);

				// Timezone is in hours, not seconds.
				$data['timezone'] = $utc_dt->getOffset($registrant_dt) / (60 * 60);
			}
		}

		if (!empty($req_data))
		{
			// TODO HTTP request the missing, required data.
			// Temporary exception.
			$this->request->disable_super_globals();
			throw new phpbb_auth_exception($req_data);
		}

		// Perform registration.
	}

	/**
	 * Link an existing phpBB account to an OpenID provider.
	 */
	protected function link()
	{
		$user_id = $this->request->variable('phpbb.user_id', 0);
		if ($user_id === 0 || !is_int($user_id))
		{
			throw new phpbb_auth_exception('No phpbb user id or non-integer user id returned by the OpenID provider.');
		}

		// Verify that the user actually exists.
		$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user_id;
		$result = $this->db->sql_query($sql);
		if (!$result)
		{
			throw new phpbb_auth_exception('User id returned by provider does not resolve to any known phpBB user.');
		}
		$this->db->sql_freeresult($result);

		$link_manager = new phpbb_auth_link_manager($this->db);
		$link_manager->add_link('openid', $user_id, $this->request->variable('openid_identity', ''));

		return true;
	}
}
