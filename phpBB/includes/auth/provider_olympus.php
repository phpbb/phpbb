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
class phpbb_auth_provider_olympus implements phpbb_auth_provider_interface
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
			'NAME'		=> 'olympus',
			'OPTIONS'	=> array(
				'enabled'	=> array('setting' => $this->config['olympus_enabled'], 'lang' => 'AUTH_ENABLE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
				'admin'		=> array('setting' => $this->config['olympus_admin'], 'lang' => 'ALLOW_ADMIN_LOGIN', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
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

		$s_login_action = ((!defined('ADMIN_START')) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login') : append_sid("index.$phpEx", false, true, $user->session_id));
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
			$s_hidden_fields['credential'] = md5(unique_id());
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$username = ($admin) ? $user->data['username'] : '';
		$username_credential = 'olympus_username';

		$password_credential = ($admin) ? 'password_' . $credential : 'password';

		$u_send_password = ($this->config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') : '';
		$u_resend_activation = ($this->config['require_activation'] == USER_ACTIVATION_SELF && $this->config['email_enable']) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=resend_act') : '';

		$template->assign_vars(array(
			'ADMIN'					=> $admin,
			'USERNAME'				=> $username,
			'USERNAME_CREDENTIAL'	=> $username_credential,
			'PASSWORD_CREDENTIAL'	=> $password_credential,

			'S_AUTOLOGIN_ENABLED'	=> $s_autologin_enabled,
			'S_DISPLAY'				=> $s_display,
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
	}

	/**
	 * {@inheritDoc}
	 */
	public function verify()
	{
		$provider_config = $this->get_configuration();
		if(!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}
	}
}
