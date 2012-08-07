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
 * This class handles the selection of which authentication provider to use for
 * login and registration. It also provides functions to get enabled and
 * registered providers.
 *
 * @package auth
 */
class phpbb_auth_manager
{
	protected $request;
	protected $db;
	protected $config;
	protected $user;

	protected $providers = array();

	public function __construct($provider_names, phpbb_cache_service $phpbb_cache, phpbb_request $request, dbal $db, phpbb_config_db $config)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;
		$this->cache = $phpbb_cache;

		$this->load_providers($provider_names);
	}

	/**
	 * Loads tasks given by name, wraps them
	 * and puts them into $this->tasks.
	 *
	 * @param array|Traversable $provider_names		Array of strings
	 *
	 * @return void
	 */
	public function load_providers($provider_names)
	{
		foreach ($provider_names as $provider_name)
		{
			$provider = new $provider_name($this->request, $this->db, $this->config);
			$this->providers[$provider->name] = $provider;
		}
	}

	/**
	 * Sets $this->user so that the phpbb_auth_manager knows that the script
	 * needs to know information about the current user.
	 *
	 * @param phpbb_user $user
	 */
	public function set_user(phpbb_user $user)
	{
 		$this->user = $user;

		foreach ($this->providers as &$provider)
		{
			$provider->set_user($this->user);
		}
 	}

	/**
	 * Returns the requested authentication provider if it exists.
	 *
	 * @param string $auth_type
	 * @return \provider
	 * @throws phpbb_auth_exception
	 */
	public function get_provider($auth_type)
	{
		if (isset($this->providers[$auth_type]))
		{
			return $this->providers[$auth_type];
		}

		throw new phpbb_auth_exception('Authentication provider, phpbb_auth_provider_' . $auth_type . ', not found.');
	}

	/**
	 * Returns an array of all providers.
	 *
	 * @return array
	 */
	public function get_registered_providers()
	{
		return $this->providers;
	}

	/**
	 * Returns an array of all enabled providers.
	 *
	 * @return array
	 */
	public function get_enabled_providers()
	{
		// This will be the ultimately returned array.
		$enabled_providers = array();

		// Try to get the list from the cache first.
		$enabled_providers_list = $this->cache->get('auth_providers_enabled');
		if ($enabled_providers_list !== false)
		{
			foreach ($enabled_providers_list as $enabled_provider)
			{
				$enabled_providers[] = $this->providers[$enabled_provider];
			}
			return $enabled_providers;
		}

		$enabled_providers_list = array();
		foreach($this->providers as $provider)
		{
			$provider_config = $provider->get_configuration();
			if($provider_config['OPTIONS']['enabled']['setting'] == true)
			{
				$enabled_providers[] = $provider;
				$enabled_providers_list[] = $provider->name;
			}
		}

		$this->cache->put('auth_providers_enabled', $enabled_providers_list);

		return $enabled_providers;
	}

	/**
	 * Returns an array of all common login providers.
	 *
	 * @return array
	 */
	public function get_common_login_providers()
	{
		$common_providers = array();
		foreach($this->providers as $provider)
		{
			if (!($provider instanceof phpbb_auth_interface_provider_custom_login))
			{
				$common_providers[] = $provider;
			}
		}

		return $common_providers;
	}

	/**
	 * Returns an array of all enabled common login providers.
	 *
	 * @return array
	 */
	public function get_enabled_common_login_providers()
	{
		$enabled_common_providers = array();
		$enabled_providers = $this->get_enabled_providers();

		foreach ($enabled_providers as $provider)
		{
			if (!($provider instanceof phpbb_auth_interface_provider_custom_login))
			{
				$enabled_common_providers[] = $provider;
			}
		}

		return $enabled_common_providers;
	}

	/**
	 * Generates the common login tempalte for display.
	 *
	 * @global type $phpbb_root_path
	 * @global type $phpEx
	 * @param phpbb_template $template
	 * @param string $redirect
	 * @param boolean $admin
	 * @param boolean $full_login
	 * @return string|boolean Template string on success|False on template error
	 */
	public function generate_common_login_box(phpbb_template $template, $redirect = '', $admin = false, $full_login = true)
	{
		global $phpbb_root_path, $phpEx;

		$s_login_action = ((!defined('ADMIN_START')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') : append_sid("index.$phpEx", false, true, $this->user->session_id));
		$s_autologin_enabled = ($this->config['allow_autologin']) ? true : false;

		$s_hidden_fields = array(
			'sid'			=> $this->user->session_id,
			'auth_provider'	=> 'common',
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
		$username_credential = 'username';

		$password_credential = ($admin) ? 'password_' . $credential : 'password';

		$u_send_password = ($this->config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') : '';
		$u_resend_activation = ($this->config['require_activation'] == USER_ACTIVATION_SELF && $this->config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=resend_act') : '';

		$template->assign_vars(array(
			'ADMIN'					=> $admin,
			'USERNAME'				=> $username,
			'USERNAME_CREDENTIAL'	=> $username_credential,
			'PASSWORD_CREDENTIAL'	=> $password_credential,

			'S_AUTOLOGIN_ENABLED'	=> $s_autologin_enabled,
			'S_DISPLAY_FULL_LOGIN'	=> ($full_login) ? true : false,
			'S_LOGIN_ACTION'		=> $s_login_action,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,

			'U_SEND_PASSWORD'		=> $u_send_password,
			'U_RESEND_ACTIVATION'	=> $u_resend_activation,
		));

		$template->set_filenames(array(
			'login_body_common' => 'login_body_common.html')
		);
		$tpl = $template->assign_display('login_body_common', '', true);
		return $tpl;
	}
}
