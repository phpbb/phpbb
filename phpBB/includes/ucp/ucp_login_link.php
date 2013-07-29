<?php
/**
*
* @package ucp
* @copyright (c) 2013 phpBB Group
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
* ucp_login_link
* Allows users of external accounts link those accounts to their phpBB accounts
* during an attempted login.
* @package ucp
*/
class ucp_login_link
{
	var $u_action;

	function main($id, $mode)
	{
		global $auth, $config, $phpbb_container, $request, $template, $user;

		$auth_provider = 'auth.provider.' . $request->variable('auth_provider', $config['auth_method']);
		$auth_provider = $phpbb_container->get($auth_provider);

		// Initialize necessary variables
		$login_link_error = null;

		// Build the data array
		$data = $this->get_login_link_data_array();

		// Ensure the person was sent here with login_link data
		if (empty($data))
		{
			$login_link_error = $user->lang['LOGIN_LINK_NO_DATA_PROVIDED'];
		}

		// Have the authentication provider check that all necessary data is available


		// Perform link action if there is no error
		if (!login_link_error)
		{
			if ($request->is_set_post('login'))
			{
				$login_username = $request->variable('login_username', '', false, phpbb_request_interface::POST);
				$login_password = $request->untrimmed_variable('password', '', true, phpbb_request_interface::POST);

				$result = $auth->login($login_username, $login_password);

				if ($result['status'] != LOGIN_SUCCESS)
				{
					// Handle all errors first
					if ($result['status'] == LOGIN_BREAK)
					{
						trigger_error($result['error_msg']);
					}

					switch ($result['status'])
					{
						case LOGIN_ERROR_ATTEMPTS:

							$captcha = phpbb_captcha_factory::get_instance($config['captcha_plugin']);
							$captcha->init(CONFIRM_LOGIN);

							$template->assign_vars(array(
								'CAPTCHA_TEMPLATE'			=> $captcha->get_template(),
							));

							$login_error = $user->lang[$result['error_msg']];
						break;

						case LOGIN_ERROR_PASSWORD_CONVERT:
							$login_error = sprintf(
								$user->lang[$result['error_msg']],
								($config['email_enable']) ? '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') . '">' : '',
								($config['email_enable']) ? '</a>' : '',
								($config['board_contact']) ? '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">' : '',
								($config['board_contact']) ? '</a>' : ''
							);
						break;

						// Username, password, etc...
						default:
							$login_error = $user->lang[$result['error_msg']];

							// Assign admin contact to some error messages
							if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
							{
								$login_error = (!$config['board_contact']) ? sprintf($user->lang[$result['error_msg']], '', '') : sprintf($user->lang[$result['error_msg']], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>');
							}

						break;
					}
				} else {
					// The user is now logged in, attempt to link the user to the external account
					$auth_provider->link_account($data);
				}
			}
		}

		// Common template elements
		$template->assign_vars(array(
			'LOGIN_LINK_ERROR'		=> $login_link_error,
			'PASSWORD_CREDENTIAL'	=> 'login_password',
			'USERNAME_CREDENTIAL'	=> 'login_username',
		));

		// Registration template
		$register_link = 'ucp.php?mode=register';

		$template->assign_vars(array(
			'REGISTER_LINK'	=>	redirect($register_link, true),
		));

		// Link to existing account template
		$template->assign_vars(array(
			'LOGIN_ERROR'		=> $login_error,
			'LOGIN_USERNAME'	=> $login_username,
		));

		$this->tpl_name = 'ucp_login_link';
		$this->page_title = 'UCP_LOGIN_LINK';
	}

	protected function get_login_link_data_array()
	{
		global $request;

		$var_names = $request->variable_names(phpbb_request_interface::GET);
		$login_link_data = array();

		foreach ($var_names as $var_name)
		{
			if (strpos($var_name, 'login_link_') === 0)
			{
				$login_link_data[$var_name] = $request->variable($var_name, '', false, phpbb_request_interface::GET);
			}
		}

		return $login_link_data;
	}
}
