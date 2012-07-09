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

	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config, phpbb_user $user)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
	}

	public function get_provider($auth_type)
	{
		$provider = 'phpbb_auth_provider_' . $auth_type;
		if (class_exists($provider))
		{
			return new $provider($this->request, $this->db, $this->config, $this->user);
		}
		else
		{
			throw new phpbb_auth_exception('Authentication provider, ' . $provider . ', not found.');
		}
	}

	// Return temporary set array of providers.
	public function get_registered_providers()
	{
		$providers = array(
			'native',
			'apache',
			'ldap',
			'openid',
			'facebook_connect',
		);

		foreach($providers as &$provider)
		{
			$provider = $this->get_provider($provider);
		}

		return $providers;
	}

	public function get_enabled_providers()
	{
		$providers = $this->get_registered_providers();

		$enabled_providers = array();
		foreach($providers as $provider) {
			$provider_config = $provider->get_configuration();
			if($provider_config['OPTIONS']['enabled']['setting'] == true)
			{
				$enabled_providers[] = $provider;
			}
		}

		return $enabled_providers;
	}

	public function get_common_providers()
	{
		$providers = $this->get_registered_providers();

		$common_providers = array();
		foreach($providers as $provider)
		{
			$provider_config = $provider->get_configuration();
			if ($provider_config['CUSTOM_LOGIN_BOX'] == false)
			{
				$common_providers[] = $provider;
			}
		}

		return $common_providers;
	}

	public function get_enabled_common_login_providers()
	{
		$providers = $this->get_registered_providers();

		$enabled_common_providers = array();
		foreach ($providers as $provider){
			$provider_config = $provider->get_configuration();
			if ($provider_config['CUSTOM_LOGIN_BOX'] == false && $provider_config['OPTIONS']['enabled']['setting'] == 1){
				$enabled_common_providers[] = $provider;
			}
		}

		return $enabled_common_providers;
	}

	public function get_enabled_common_registration_providers()
	{
		$providers = $this->get_registered_providers();

		$enabled_common_providers = array();
		foreach ($providers as $provider){
			$provider_config = $provider->get_configuration();
			if ($provider_config['CUSTOM_REGISTER'] == false && $provider_config['OPTIONS']['enabled']['setting'] == 1){
				$enabled_common_providers[] = $provider;
			}
		}

		return $enabled_common_providers;
	}

	public function generate_common_login_box(phpbb_template $template, $redirect = '', $admin = false, $s_display = true)
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
			'S_DISPLAY_FULL_LOGIN'	=> ($s_display) ? true : false,
			'S_LOGIN_ACTION'		=> $s_login_action,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,

			'U_SEND_PASSWORD'		=> $u_send_password,
			'U_RESEND_ACTIVATION'	=> $u_resend_activation,
		));

		$template->set_filenames(array(
			'login_body_common' => 'login_body_common.html')
		);
		$tpl = $template->assign_display('login_body_common', '', true);
		if (!$tpl)
		{
			return null;
		}
		return $tpl;
	}

	public function generate_common_registration_form(phpbb_template $template)
	{
		global $phpbb_root_path, $phpEx;
		if ($this->config['enable_confirm'])
		{
			$captcha = new phpbb_auth_captcha($this->db, $this->config, $this->user);
		}

		$coppa	= $this->request->is_set('coppa') ? (int) $this->request->variable('coppa', false) : false;
		$timezone = $this->config['board_timezone'];
		$data = array(
			'username'			=> utf8_normalize_nfc($this->request->variable('username', '', true)),
			'new_password'		=> $this->request->variable('new_password', '', true),
			'password_confirm'	=> $this->request->variable('password_confirm', '', true),
			'email'				=> strtolower($this->request->variable('email', '')),
			'lang'				=> basename($this->request->variable('lang', $this->user->lang_name)),
			'tz'				=> $this->request->variable('tz', (float) $timezone),
		);

		$s_hidden_fields = array(
			'agreed'		=> 'true',
			'change_lang'	=> 0,

			'auth_provider'	=> 'common',
			'auth_action'	=> 'register',
			'auth_step'		=> 'process',
		);

		if ($this->config['coppa_enable'])
		{
			$s_hidden_fields['coppa'] = $coppa;
		}

		if ($this->config['enable_confirm'])
		{
			$s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields(CONFIRM_REG));
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		// Visual Confirmation - Show images
		if ($this->config['enable_confirm'])
		{
			$template->assign_vars(array(
				'CAPTCHA_TEMPLATE'		=> $captcha->get_template(CONFIRM_REG),
			));
		}

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

		$timezone_selects = phpbb_timezone_select($user, $data['tz'], true);
		$template->assign_vars(array(
			'USERNAME'			=> $data['username'],
			'PASSWORD'			=> $data['new_password'],
			'PASSWORD_CONFIRM'	=> $data['password_confirm'],
			'EMAIL'				=> $data['email'],

			'L_REG_COND'				=> $l_reg_cond,
			'L_USERNAME_EXPLAIN'		=> $this->user->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->user->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->user->lang('CHARACTERS', (int) $this->config['max_name_chars'])),
			'L_PASSWORD_EXPLAIN'		=> $this->user->lang($this->config['pass_complex'] . '_EXPLAIN', $this->user->lang('CHARACTERS', (int) $this->config['min_pass_chars']), $this->user->lang('CHARACTERS', (int) $this->config['max_pass_chars'])),

			'S_LANG_OPTIONS'	=> language_select($data['lang']),
			'S_TZ_OPTIONS'			=> $timezone_selects['tz_select'],
			'S_TZ_DATE_OPTIONS'		=> $timezone_selects['tz_dates'],
			'S_CONFIRM_REFRESH'	=> ($this->config['enable_confirm'] && $this->config['confirm_refresh']) ? true : false,
			'S_REGISTRATION'	=> true,
			'S_COPPA'			=> $coppa,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'),
		));

		$this->user->profile_fields = array();

		// Generate profile fields -> Template Block Variable profile_fields
		$cp = new custom_profile();
		$cp->generate_profile_fields('register', $this->user->get_iso_lang_id());

		$template->set_filenames(array(
			'ucp_register_common' => 'ucp_register_common.html')
		);
		$tpl = $template->assign_display('ucp_register_common', '', true);
		if (!$tpl)
		{
			return null;
		}
		return $tpl;
	}
}
