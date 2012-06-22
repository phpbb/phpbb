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
	 * {@inheritDoc}
	 */
	public function get_configuration()
	{
		return array(
			'CUSTOM_ACP'=> false,
			'NAME'		=> 'openid',
			'OPTIONS'	=> array(
				'enabled'	=> array('setting' => $this->config['openid_enabled'], 'lang' => 'AUTH_ENABLE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
				'admin'		=> array('setting' => $this->config['openid_admin'], 'lang' => 'ALLOW_ADMIN_LOGIN', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_login_box($redirect = '', $admin = false, $s_display = true)
	{
		$provider_config = $this->get_configuration();
		if (!$provider_config['OPTIONS']['enabled']['setting']
				|| (!$provider_config['OPTIONS']['admin']['setting'] && $admin == true))
		{
			return null;
		}

		global $phpbb_root_path, $phpEx;

		$s_login_action = ((!defined('ADMIN_START')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') : append_sid("index.$phpEx", false, true, $user->session_id));
		$s_autologin_enabled = ($this->config['allow_autologin']) ? true : false;

		$s_hidden_fields = array(
			'sid'			=> $this->user->session_id,
			'auth_provider'	=> 'openid',
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
			$s_hidden_fields['credential'] = md5(unique_id());
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$board_url = generate_board_url() . '/';
		$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $phpbb_root_path;
		$theme_path = $web_path . 'styles/' . rawurlencode($this->user->theme['style_path']) . '/theme';

		$openid_identifier = 'openid_identifier';
		$tpl = '
		<dt>' . $this->user->lang['AUTH_PROVIDER_OPENID'] . '</dt>
		<dd>
			<div>
				<form action="' . $s_login_action . '" method="post" id="openid_login">
					<dl>
						<dt><label for="' . $openid_identifier . '"><img src="' . $theme_path . '/images/logo_openid_small.gif" alt="'. $this->user->lang['AUTH_PROVIDER_OPENID'] .'"/></label></dt>
						<dd><input type="text" tabindex="1" name="' . $openid_identifier . '" id="' . $openid_identifier . '" size="25" value="" class="inputbox autowidth" /></dd>
					</dl>';
		if (!$admin)
		{
			$tpl .= '
					<dl>';
			if ($s_autologin_enabled)
			{
				$tpl .= '
						<dd><label for="autologin"><input type="checkbox" name="autologin" id="autologin" tabindex="4" /> ' . $this->user->lang['LOG_ME_IN'] . '</label></dd>
						';
			}
		$tpl .= '
						<dd><label for="viewonline"><input type="checkbox" name="viewonline" id="viewonline" tabindex="5" /> ' . $this->user->lang['HIDE_ME'] . '</label></dd>
					</dl>';
		}
		$tpl .= '
					<dl>
						<dt>&nbsp;</dt>
						<dd>' . $s_hidden_fields . '<input type="submit" name="login" tabindex="6" value="' . $this->user->lang['LOGIN'] . '" class="button1" /></dd>
					</dl>
				</form>
			</div>
		</dd>
		';

		return $tpl;
	}

	/**
	 * Performs the login request on the external server specified by
	 * openid_identifier. Redirects the browser first to the external server
	 * for authentication then back to /check_auth_openid.php to complete
	 * login.
	 *
	 * @param boolean $admin Whether reauthentication is being requested for administrative login. This can be prevented by disabling admin reauthentication on the ACP.
	 */
	public function process($admin = false)
	{
		$provider_config = $this->get_configuration();
		if (!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}

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
			if($admin == true && $provider_config['OPTIONS']['admin']['setting'] == false)
			{
				throw new phpbb_auth_exception('AUTH_ADMIN_DISABLED');
			}

			$autologin = $this->request->variable('autologin', 'off');
			$viewonline = $this->request->variable('viewonline', 'off');

			global $phpEx;
			$redirect_to = $this->request->variable('redirect_to', 'index.' . $phpEx);
			$return_to = $this->user->page['page'].'?mode=login&auth_step=verify&auth_provider=openid&phpbb.auth_action=login&phpbb.autologin=' . $autologin . '&phpbb.viewonline=' . $viewonline . '&phpbb.redirect_to=' . $redirect_to . '&phpbb.admin=' . $admin;
			$extensions = null;
		}
		elseif ($auth_action === 'link')
		{
			if(!isset($this->user->data['user_id']))
			{
				throw new phpbb_auth_exception('You may only link a logged in phpBB user to an OpenID provider.');
			}
			$return_to = $this->user->page['page'].'?mode=register&auth_step=verify&auth_provider=openid&phpbb.auth_action=link&phpbb.user_id=' . $this->user->data['user_id'];
			$extensions = null;
		}
		elseif ($auth_action === 'register')
		{
			$return_to = $this->user->page['page'].'?auth_step=verify&auth_provider=openid&phpbb.auth_action=register';
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
		$provider_config = $this->get_configuration();
		if(!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}

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

				$admin = (bool)$this->request->variable('phpbb_admin', false);
				if($admin == true && $provider_config['OPTIONS']['admin']['setting'] == false)
				{
					throw new phpbb_auth_exception('AUTH_ADMIN_DISABLED');
				}

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
				$data['lang'] = $sreg_data['language'];
			}

			if (isset($sreg_data['timezone']))
			{
				$utc_dtz = new DateTimeZone('UTC');
				$registrant_dtz = new DateTimeZone($sreg_data['timezone']);

				$utc_dt = new DateTime('now', $utc_dtz);
				$registrant_dt = new DateTime('now', $registrant_dtz);

				// Timezone is in hours, not seconds.
				$data['tz'] = $utc_dt->getOffset($registrant_dt) / (60 * 60);
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
		$this->register($data);
	}
}
