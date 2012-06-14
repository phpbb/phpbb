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
class phpbb_auth_provider_openid extends phpbb_auth_common_provider
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

		$auth_action = $this->request->variable('auth_action', '');
		if ($auth_action === 'login')
		{
			$autologin = $this->request->variable('autologin', 'off');
			$viewonline = $this->request->variable('viewonline', 'off');
			$redirect_to = $this->request->variable('redirect_to', 'index.php');
			$return_to = 'check_auth_openid.php?phpbb.auth_action=login&phpbb.autologin=' . $autologin . '&phpbb.viewonline=' . $viewonline . '&phpbb.redirect_to=' . $redirect_to;
			$extensions = null;
		}
		elseif ($auth_action === 'link')
		{
			if(!isset($this->user->data['user_id']))
			{
				throw new phpbb_auth_exception('You may only link a logged in phpBB user to an OpenID provider.');
			}
			$return_to = 'check_auth_openid.php?phpbb.auth_action=link&phpbb.user_id=' . $this->user->data['user_id'];
			$extensions = null;
		}
		elseif ($auth_action === 'register')
		{
			$return_to = 'check_auth_openid.php?phpbb.auth_action=register';
			$extensions = array(
				'sreg'	=> new Zend\OpenId\Extension\Sreg($this->sreg_props, null, 1.0),
			);
		}
		else
		{
			throw new phpbb_auth_exception('OpenID does not support this authentication action.');
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

		$auth_action = $this->request->variable('phpbb_auth_action', '');
		if ($auth_action === 'register')
		{
			$extensions = array(
				'sreg'	=> new Zend\OpenId\Extension\Sreg($this->sreg_props, null, 1.0),
			);
		}
		elseif ($auth_action === 'login' | 'link')
		{
			$extensions = null;
		}
		else
		{
			throw new phpbb_auth_exception('Invalid authentication action.');
		}

		// Enable super globals so Zend Framework does not throw errors.
		$this->request->enable_super_globals();
		$id = '';
		if ($consumer->verify($_GET, $id, $extensions))
		{
			if ($auth_action == 'login')
			{
				// We no longer need super globals enabled.
				$this->request->disable_super_globals();

				// Check to see if a link exists.
				$link_manager = new phpbb_auth_link_manager($this->db);
				$link = $link_manager->get_link_by_index('openid', $this->request->variable('openid_identity', ''));
				if (!$link)
				{
					throw new phpbb_auth_exception('Can not find a link between ' . $this->request->variable('openid_identity', '') . ' and any known account.');
				}

				$admin = false; // OpenID can not be used for admin login.
				$autologin = (bool)$this->request->variable('phpbb_autologin', false);
				$viewonline = (bool)$this->request->variable('phpbb_viewonline', true);

				if (!$this->login((int) $link['user_id'], $admin, $autologin, $viewonline))
				{
					$this->login_auth_fail((int) $link['user_id']);
				}
				$this->redirect($this->request->variable('phpbb_redirect_to', ''));
			}
			elseif ($auth_action == 'register' && $this->register($extensions['sreg']->getProperties()))
			{
				// We no longer need super globals enabled.
				$this->request->disable_super_globals();
				return true; // TODO: Change this to a redirect.
			}
			elseif ($auth_action == 'link')
			{
				// We no longer need super globals enabled.
				$this->request->disable_super_globals();

				$user_id = $this->request->variable('phpbb_user_id', 0);
				$identity = $this->request->variable('openid_identity', '');
				$this->link($user_id, 'openid', $identity);
				return true; // TODO: Change this to a redirect.
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
	 * Register an OpenID user with phpBB. Supports both simple registration
	 * extension
	 * (https://openid.net/specs/openid-simple-registration-extension-1_0.html)
	 * and attribute exchange
	 * (https://openid.net/specs/openid-attribute-exchange-1_0.html).
	 *
	 * @param array $sreg_data Holds returned data from the OpenID provider that is needed to perform registration.
	 */
	protected function register($sreg_data)
	{
		// Data array to hold all returned values.
		$data = array();

		// Data that must be supplied in order for registration to occur.
		$req_data = array();

		// Handle OpenId simple registration extension (sreg) information from
		// the OpenID provider.
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
}
