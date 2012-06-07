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
class phpbb_auth_provider_open_id implements phpbb_auth_provider_interface
{
	protected $request;
	protected $db;
	protected $sreg_props;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(phpbb_request $request, dbal $db)
	{
		$this->request = $request;
		$this->db = $db;
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
	 * open_id_identifier. Redirects the browser first to the external server
	 * for authentication then back to /check_auth_openid.php to complete
	 * login.
	 */
	public function process()
	{
		$storage = new phpbb_auth_zend_storage($this->db);
		$this->consumer = $consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);

		if ($this->request->variable('open_id_identifier', '') == '')
		{
			throw new phpbb_auth_exception('No OpenID identifier supplied.');
		}
		else
		{
			$identifier = $this->request->variable('open_id_identifier', '');
		}

		$root = 'http://192.168.0.112/';

		if ($this->request->variable('Login', ''))
		{
			// Login
			$autologin = $request('autologin', 'off');
			$viewonline = $request('viewonline', 'off');
			$return_to = 'phpBB/check_auth_openid.php?phpbb_process=login&phpbb_autologin=' . $autologin . '&phpbb_viewonline=' . $viewonline;
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
		$consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);

		$process = $this->request->variable('phpbb_process', '');
		if ($process == 'register')
		{
			$extensions = array(
				'sreg'	=> new Zend\OpenId\Extension\Sreg($this->sreg_props, null, 1.0),
			);
		}
		elseif ($process == 'login')
		{
			$extensions = null;
		}
		else
		{
			throw new phpbb_auth_exception('Unknown authentication process.');
		}

		// Enable super globals so Zend Framework does not throw errors.
		$this->request->enable_super_globals();
		$id = '';
		if ($consumer->verify($_GET, $id, $extensions))
		{
			if ($process == 'login')
			{
				// Check to see if a link exists.
				$link_manager = new phpbb_auth_link_manager($this->db);
				$link = $link_manager->get_link_by_index('open_id', $request->variable('openid_identity', ''));
				if (!$link)
				{
					throw new phpbb_auth_exception('Can not find a link between ' . $request->variable('openid_identity', '') . ' and any known account.');
				}

				// Login
			}
			elseif ($$process == 'register')
			{
				// Data array to hold all returned values.
				$data = array();

				// Data that must be supplied in order for registration to occur.
				$req_data = array();

				// Handle sreg data.
				$sreg_data = $$extensions['sreg']->getProperties();
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
						$UTC_dtz = new DateTimeZone('UTC');
						$registrant_dtz = new DateTimeZone($sreg_data['timezone']);

						$UTC_dt = new DateTime('now', $UTC_dtz);
						$registrant_dt = new DateTime('now', $registrant_dtz);

						// Timezone is in hours, not seconds.
						$data['timezone'] = $UTC_dt->getOffset($registrant_dt) / (60 * 60);
					}
				}

				if (!empty($req_data))
				{
					// TODO HTTP request the missing, required data.
					// Temporary exception.
					throw new phpbb_auth_exception($req_data);
				}

				// Perform registration.
			}
		}
		else
		{
			$this->request->disable_super_globals();
			throw new phpbb_auth_exception('OpenID authentication failed.');
		}
		$this->request->disable_super_globals();

		return true;
	}
}
