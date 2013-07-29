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
		global $config, $phpbb_container, $request, $template, $user;

		// Initialize necessary variables
		$login_error = null;
		$login_link_error = null;
		$login_username = null;

		// Build the data array
		$data = $this->get_login_link_data_array();

		// Ensure the person was sent here with login_link data
		if (empty($data))
		{
			$login_link_error = $user->lang['LOGIN_LINK_NO_DATA_PROVIDED'];
		}

		// Have the authentication provider check that all necessary data is available
		$result = $auth_provider->login_link_has_necessary_data($data);
		if ($result !== null)
		{
			$login_link_error = $user->lang[$result];
		}

		// Use the auth_provider requested even if different from configured
		$auth_provider = 'auth_provider.' . (array_key_exists('auth_provider', $data)) ? $data['auth_provider'] : $config['auth_method'];
		$auth_provider = $phpbb_container->get($auth_provider);

		// Perform link action if there is no error
		if (!$login_link_error)
		{
			if ($request->is_set_post('login'))
			{
				// We only care if there is or is not an error
				$login_error = $this->perform_login_action();

				if (!$login_error)
				{
					// The user is now logged in, attempt to link the user to the external account
					$result = $auth_provider->link_account($data);

					if ($result)
					{
						$login_link_error = $user->lang[$result];
					} else {
						// Perform a redirect as the account has been linked
					}
				}
			}
		}

		$register_link = redirect('ucp.php?mode=register', true);

		$template->assign_vars(array(
			// Common template elements
			'LOGIN_LINK_ERROR'		=> $login_link_error,
			'PASSWORD_CREDENTIAL'	=> 'login_password',
			'USERNAME_CREDENTIAL'	=> 'login_username',

			// Registration elements
			'REGISTER_LINK'	=>	$register_link,

			// Login elements
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
				$key_name = str_replace('login_link_', '', $var_name);
				$login_link_data[$key_name] = $request->variable($var_name, '', false, phpbb_request_interface::GET);
			}
		}

		return $login_link_data;
	}

	protected function perform_login_action()
	{
		global $auth, $config, $request, $template, $user;
		$login_username = $request->variable('login_username', '', false, phpbb_request_interface::POST);
		$login_password = $request->untrimmed_variable('password', '', true, phpbb_request_interface::POST);

		$result = $auth->login($login_username, $login_password);

		$login_error = null;

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
		}

		return $login_error;
	}
}
