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

		// Use the auth_provider requested even if different from configured
		$auth_provider = 'auth.provider.' . $request->variable('auth_provider', $config['auth_method']);
		$auth_provider = $phpbb_container->get($auth_provider);

		// Have the authentication provider check that all necessary data is available
		$result = $auth_provider->login_link_has_necessary_data($data);
		if ($result !== null)
		{
			$login_link_error = $user->lang[$result];
		}

		// Perform link action if there is no error
		if (!$login_link_error)
		{
			if ($request->is_set_post('login'))
			{
				$login_username = $request->variable('login_username', '', false, phpbb_request_interface::POST);
				$login_password = $request->untrimmed_variable('login_password', '', true, phpbb_request_interface::POST);

				$login_result = $auth_provider->login($login_username, $login_password);

				// We only care if there is or is not an error
				$login_error = $this->process_login_result($login_result);

				if (!$login_error)
				{
					// Give the user_id to the data
					$data['user_id'] = $login_result['user_row']['user_id'];

					// The user is now logged in, attempt to link the user to the external account
					$result = $auth_provider->link_account($data);

					if ($result)
					{
						$login_link_error = $user->lang[$result];
					} else {
						// Finish login
						$result = $user->session_create($login_result['user_row']['user_id'], false, false, true);

						// Perform a redirect as the account has been linked
						$this->perform_redirect();
					}
				}
			}
		}

		$template->assign_vars(array(
			// Common template elements
			'LOGIN_LINK_ERROR'		=> $login_link_error,
			'PASSWORD_CREDENTIAL'	=> 'login_password',
			'USERNAME_CREDENTIAL'	=> 'login_username',
			'S_HIDDEN_FIELDS'		=> $this->get_hidden_fields(),

			// Registration elements
			'REGISTER_ACTION'	=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register'),

			// Login elements
			'LOGIN_ERROR'		=> $login_error,
			'LOGIN_USERNAME'	=> $login_username,
		));

		$this->tpl_name = 'ucp_login_link';
		$this->page_title = 'UCP_LOGIN_LINK';
	}

	protected function get_register_hidden_fields($data)
	{
		global $config, $phpbb_root_path, $phpEx, $request;

		$fields = array();

		foreach ($data as $key => $value)
		{
			$fields['login_link_' . $key] = $value;
		}

		return build_hidden_fields($s_hidden_fields);
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

	protected function process_login_result($result)
	{
		global $config, $request, $template, $user;

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

	protected function perform_redirect()
	{
		// TODO: Make redirect to same page as login would have
		redirect('index.php');
	}
}
