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

		$tpl = '
		<script type="text/javascript">
		// <![CDATA[
			onload_functions.push(\'document.getElementById("';
		if ($admin)
		{
			$tpl .= $password_credential;
		}
		else
		{
			$tpl .= $username_credential;
		}
		$tpl .= '").focus();\');
		// ]]>
		</script>
		<form action="' . $s_login_action . '" method="post" id="olympus_login">
			<div>
				<fieldset <!-- IF not S_CONFIRM_CODE -->class="fields1"<!-- ELSE -->class="fields2"<!-- ENDIF -->>
					<dl>
						<dt><label for="' . $username_credential . '">' . $this->user->lang['USERNAME'] . ':</label></dt>
						<dd><input type="text" tabindex="1" name="' . $username_credential . '" id="' . $username_credential . '" size="25" value="' . $username . '" class="inputbox autowidth" /></dd>
					</dl>
					<dl>
						<dt><label for="' . $password_credential . '">' . $this->user->lang['PASSWORD'] . ':</label></dt>
						<dd><input type="password" tabindex="2" id="' . $password_credential . '" name="' . $password_credential . '" size="25" class="inputbox autowidth" /></dd>';

		if ($s_display)
		{
			if ($u_send_password)
			{
				$tpl .= '
						<dd><a href="' . $u_send_password . '">' . $this->user->lang['FORGOT_PASS'] . '</a></dd>';
			}
			if ($u_resend_activation)
			{
				$tpl .= '
						<dd><a href="' . $u_resend_activation . '">' . $this->user->lang['RESEND_ACTIVATION'] . '</a></dd>';
			}
		}

		$tpl .= '
					</dl>';
		/* Disabled until Olympus is refactored into this provider class
		$tpl .= '
					<!-- IF CAPTCHA_TEMPLATE and S_CONFIRM_CODE -->
						<!-- DEFINE $CAPTCHA_TAB_INDEX = 3 -->
						<!-- INCLUDE {CAPTCHA_TEMPLATE} -->
					<!-- ENDIF -->';*/
		if ($s_display)
		{
			$tpl .= '
					<dl>';
			if ($s_autologin_enabled)
			{
				$tpl .= '
						<dd><label for="autologin"><input type="checkbox" name="autologin" id="autologin" tabindex="4" /> ' . $this->user->lang['LOG_ME_IN'] . '</label></dd>';
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
				</fieldset>
			</div>
		</form>';

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
