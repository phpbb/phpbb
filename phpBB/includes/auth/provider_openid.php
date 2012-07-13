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

	protected $phpbb_root_path;
	protected $phpEx;
	protected $SID;
	protected $_SID;

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
			"dob"		=> false,
			"gender"	=> false,
			"language"	=> false,
			"timezone"	=> false,
		);

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
			'CUSTOM_LOGIN_BOX'	=> true,
			'CUSTOM_REGISTER'	=> true,

			'NAME'		=> 'openid',
			'OPTIONS'	=> array(
				'enabled'	=> array('setting' => $this->config['auth_provider_openid_enabled'],	'lang' => 'AUTH_ENABLE',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => false),
				'admin'		=> array('setting' => $this->config['auth_provider_openid_admin'],		'lang' => 'ALLOW_ADMIN_LOGIN',	'validate' => 'bool',	'type' => 'radio:yes_no',			'explain' => true),
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

		$template->assign_vars(array(
			'ADMIN'					=> $admin,
			'OPENID_IDENTIFIER'		=> $openid_identifier,
			'THEME_PATH'			=> $theme_path,

			'S_AUTOLOGIN_ENABLED'	=> $s_autologin_enabled,
			'S_LOGIN_ACTION'		=> $s_login_action,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
		));

		$template->set_filenames(array(
			'login_body_openid' => 'login_body_openid.html')
		);
		$tpl = $template->assign_display('login_body_openid', '', true);
		return $tpl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_registration($template)
	{
		$provider_config = $this->get_configuration();
		if (!$provider_config['OPTIONS']['enabled']['setting']
				|| (!$provider_config['OPTIONS']['admin']['setting'] && $admin == true))
		{
			return null;
		}

		global $phpbb_root_path, $phpEx;

		$s_hidden_fields = array(
			'agreed'		=> 'true',
			'change_lang'	=> 0,

			'auth_provider'	=> 'openid',
			'auth_action'	=> 'register',
			'auth_step'		=> 'process',
		);

		$coppa = $this->request->is_set('coppa') ? (int) $this->request->variable('coppa', false) : false;
		if ($this->config['coppa_enable'])
		{
			$s_hidden_fields['coppa'] = $coppa;
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

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

		$board_url = generate_board_url() . '/';
		$web_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $phpbb_root_path;
		$theme_path = $web_path . 'styles/' . rawurlencode($this->user->theme['style_path']) . '/theme';

		$openid_identifier = 'openid_identifier';

		$template->assign_vars(array(
			'L_REG_COND'				=> $l_reg_cond,

			'S_REGISTRATION'	=> true,
			'S_COPPA'			=> $coppa,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'),

			'OPENID_IDENTIFIER'		=> $openid_identifier,
			'THEME_PATH'			=> $theme_path,
		));

		$template->set_filenames(array(
			'ucp_register_openid' => 'ucp_register_openid.html')
		);
		$tpl = $template->assign_display('ucp_register_openid', '', true);
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

		$protocol = ($this->request->server('HTTPS') == '') ? 'http://' : 'https://';
		$root = $protocol . $this->request->server('HTTP_HOST');

		$auth_action = $this->request->variable('auth_action', '');
		if ($auth_action === 'login')
		{
			if ($admin == true && $provider_config['OPTIONS']['admin']['setting'] == false)
			{
				throw new phpbb_auth_exception('AUTH_ADMIN_DISABLED');
			}

			$autologin = $this->request->variable('autologin', 'off');
			$viewonline = $this->request->variable('viewonline', 'off');
			$redirect_to = $this->request->variable('redirect', 'index.' . $this->phpEx);

			$return_to = ($admin) ? 'index.' . $this->phpEx : 'ucp.' . $this->phpEx ;
			$return_to .= '?mode=login&auth_step=verify&auth_provider=openid&auth_action=login&phpbb.autologin=' . $autologin . '&phpbb.viewonline=' . $viewonline . '&phpbb.redirect_to=' . $redirect_to . '&phpbb.admin=' . $admin;
			$extensions = null;
		}
		else if ($auth_action === 'link')
		{
			if (!isset($this->user->data['user_id']))
			{
				throw new phpbb_auth_exception('You may only link a logged in phpBB user to an OpenID provider.');
			}
			$return_to = $this->user->page['page'].'?auth_step=verify&auth_provider=openid&auth_action=linkphpbb.user_id=' . $this->user->data['user_id'];
			$extensions = null;
		}
		else if ($auth_action === 'register')
		{
			global $phpEx;
			$coppa = $this->request->is_set('coppa') ? (int) $this->request->variable('coppa', false) : false;
			$return_to = 'ucp.' . $this->phpEx . '?mode=register&auth_step=verify&auth_provider=openid&auth_action=register&coppa=' . (int)$coppa . '&agreed=1';
			$extensions = array(
				'sreg'	=> new Zend\OpenId\Extension\Sreg($this->sreg_props, null, 1.1),
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

		$auth_action = $this->request->variable('auth_action', '');
		if ($auth_action === 'register')
		{
			$extensions = array(
				'sreg'	=> new Zend\OpenId\Extension\Sreg($this->sreg_props, null, 1.1),
			);
		}
		else if ($auth_action === 'login' || $auth_action === 'link')
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

				$admin = (bool)$this->request->variable('phpbb_admin', false);
				if ($admin == true && $provider_config['OPTIONS']['admin']['setting'] == false)
				{
					throw new phpbb_auth_exception('AUTH_ADMIN_DISABLED');
				}

				// Check to see if a link exists.
				$link_manager = new phpbb_auth_link_manager($this->db);
				$link = $link_manager->get_link_by_index('openid', $this->request->variable('openid_identity', ''));
				if (!$link)
				{
					throw new phpbb_auth_exception('Can not find a link between ' . $this->request->variable('openid_identity', '') . ' and any known account.');
				}

				$autologin = (bool)$this->request->variable('phpbb_autologin', false);
				$viewonline = (bool)$this->request->variable('phpbb_viewonline', true);

				$this->login((int) $link['user_id'], $admin, $autologin, $viewonline);
				$this->redirect($this->request->variable('phpbb_redirect_to', ''));
			}
			else if ($auth_action == 'register')
			{
				$sreg_data = $extensions['sreg']->getProperties();
				// We no longer need super globals enabled.
				$this->request->disable_super_globals();

				$user_id = $this->internal_register($sreg_data);

				if ($user_id instanceof phpbb_auth_data_request)
				{
					return $user_id;
				}

				$identity = $this->request->variable('openid_identity', '');
				$this->link($user_id, 'openid', $identity);
				return true; // TODO: Change this to a redirect.
			}
			else if ($auth_action == 'link')
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
			throw new phpbb_auth_exception('OpenID authentication failed: ' . $consumer->getError());
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
	protected function internal_register($sreg_data)
	{
		// Data array to hold all returned values.
		$data = array();

		// Data that must be supplied in order for registration to occur.
		$req_data = array();

		// Handle OpenId simple registration extension (sreg) information from
		// the OpenID provider.
		if (!empty($sreg_data))
		{
			if (!isset($sreg_data['email']))
			{
				$req_data['EMAIL'] = 'NO_EMAIL_SUPPLIED';
			}
			else
			{
				$error = validate_email($sreg_data['email']);
				if ($error)
				{
					$req_data['EMAIL'] = $error;
				}
				$data['email'] = $sreg_data['email'];
			}

			if (isset($sreg_data['dob']))
			{
				$dob = explode('-', $sreg_data['dob']);
				$dob = $dob[2] . '-' . $dob[1] . '-' . $dob[0];
				$error = validate_date($dob);
				if (!$error)
				{
					$data['dob'] = $dob;
				}
			}

			if (!isset($sreg_data['nickname']))
			{
				$req_data['USERNAME'] = 'NO_USERNAME_SUPPLIED';
			}
			else
			{
				$error = validate_username($sreg_data['nickname']);
				if ($error)
				{
					$req_data['USERNAME'] = $error;
				}
				$data['username'] = $sreg_data['nickname'];
			}

			if (isset($sreg_data['gender']))
			{
				$data['gender'] = $sreg_data['gender'];
			}

			if (isset($sreg_data['language']))
			{
				$lang = strtolower($sreg_data['language']);
				if (!validate_language_iso_name($lang))
				{
					$lang = $this->user->lang_name;
				}
				$data['lang'] = $lang;
			}

			if (isset($sreg_data['timezone']))
			{
				$registrant_dtz = new DateTimeZone($sreg_data['timezone']);

				$registrant_dt = new DateTime('now', $registrant_dtz);

				// Timezone is in hours, not seconds.
				$data['tz'] = ($registrant_dt->getOffset()) / (60 * 60);
			}
		}

		if (!empty($req_data))
		{
			$data_request = new phpbb_auth_data_request($this->user, $req_data);
			return $data_request;
		}

		// Perform registration.
		$user_id = $this->register($data);
		return $user_id;
	}
}
