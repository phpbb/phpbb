<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\ucp\controller;

/**
 * Allows users of external accounts link those accounts to their phpBB accounts
 * during an attempted login.
 */
class login_link
{
	/**
	 * @var string
	 */
	public $u_action;

	/**
	 * Generates the ucp_login_link page and handles login link process
	 *
	 * @param	int		$id
	 * @param	string	$mode
	 */
	public function main($id, $mode)
	{

		// Initialize necessary variables
		$login_error = null;
		$login_link_error = null;
		$login_username = null;

		// Build the data array
		$data = $this->get_login_link_data_array();

		// Ensure the person was sent here with login_link data
		if (empty($data))
		{
			$login_link_error = $this->language->lang('LOGIN_LINK_NO_DATA_PROVIDED');
		}

		// Use the auth_provider requested even if different from configured
		/* @var $provider_collection \phpbb\auth\provider_collection */
		$provider_collection = $phpbb_container->get('auth.provider_collection');
		$auth_provider = $provider_collection->get_provider($this->request->variable('auth_provider', ''));

		// Set the link_method to login_link
		$data['link_method'] = 'login_link';

		// Have the authentication provider check that all necessary data is available
		$result = $auth_provider->login_link_has_necessary_data($data);
		if ($result !== null)
		{
			$login_link_error = $this->language->lang($result);
		}

		// Perform link action if there is no error
		if (!$login_link_error)
		{
			if ($this->request->is_set_post('login'))
			{
				$login_username = $this->request->variable('login_username', '', true, \phpbb\request\request_interface::POST);
				$login_password = $this->request->untrimmed_variable('login_password', '', true, \phpbb\request\request_interface::POST);

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
						$login_link_error = $this->language->lang($result);
					}
					else
					{
						// Finish login
						$this->user->session_create($login_result['user_row']['user_id'], false, false, true);

						// Perform a redirect as the account has been linked
						$this->perform_redirect();
					}
				}
			}
		}

		$tpl_ary = [
			// Common template elements
			'LOGIN_LINK_ERROR'		=> $login_link_error,
			'PASSWORD_CREDENTIAL'	=> 'login_password',
			'USERNAME_CREDENTIAL'	=> 'login_username',
			'S_HIDDEN_FIELDS'		=> $this->get_hidden_fields($data),

			// Registration elements
			'REGISTER_ACTION'	=> append_sid("{$this->root_path}ucp.$this->php_ext", 'mode=register'),

			// Login elements
			'LOGIN_ERROR'		=> $login_error,
			'LOGIN_USERNAME'	=> $login_username,
		];

		/**
		 * Event to perform additional actions before ucp_login_link is displayed
		 *
		 * @event core.ucp_login_link_template_after
		 * @var array							data				Login link data
		 * @var \phpbb\auth\provider_interface	auth_provider		Auth provider
		 * @var string							login_link_error	Login link error
		 * @var string							login_error			Login error
		 * @var string							login_username		Login username
		 * @var array							tpl_ary				Template variables
		 * @since 3.2.4-RC1
		 */
		$vars = ['data', 'auth_provider', 'login_link_error', 'login_error', 'login_username', 'tpl_ary'];
		extract($this->dispatcher->trigger_event('core.ucp_login_link_template_after', compact($vars)));

		$this->template->assign_vars($tpl_ary);

		$this->tpl_name = 'ucp_login_link';
		$this->page_title = 'UCP_LOGIN_LINK';
	}

	/**
	 * Builds the hidden fields string from the data array.
	 *
	 * @param	array	$data	This function only includes data in the array
	 *							that has a key that begins with 'login_link_'
	 * @return	string	A string of hidden fields that can be included in the
	 *					template
	 */
	protected function get_hidden_fields($data)
	{
		$fields = [];

		foreach ($data as $key => $value)
		{
			$fields['login_link_' . $key] = $value;
		}

		return build_hidden_fields($fields);
	}

	/**
	 * Builds the login_link data array
	 *
	 * @return	array	All login_link data. This is all GET data whose names
	 *					begin with 'login_link_'
	 */
	protected function get_login_link_data_array()
	{

		$var_names = $this->request->variable_names(\phpbb\request\request_interface::GET);
		$login_link_data = [];
		$string_start_length = strlen('login_link_');

		foreach ($var_names as $var_name)
		{
			if (strpos($var_name, 'login_link_') === 0)
			{
				$key_name = substr($var_name, $string_start_length);
				$login_link_data[$key_name] = $this->request->variable($var_name, '', false, \phpbb\request\request_interface::GET);
			}
		}

		return $login_link_data;
	}

	/**
	 * Processes the result array from the login process
	 * @param	array	$result	The login result array
	 * @return	string|null	If there was an error in the process, a string is
	 *						returned. If the login was successful, then null is
	 *						returned.
	 */
	protected function process_login_result($result)
	{

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

					$captcha = $phpbb_container->get('captcha.factory')->get_instance($this->config['captcha_plugin']);
					$captcha->init(CONFIRM_LOGIN);

					$this->template->assign_vars([
						'CAPTCHA_TEMPLATE'			=> $captcha->get_template(),
					]);

					$login_error = $this->language->lang[$result['error_msg']];
				break;

				case LOGIN_ERROR_PASSWORD_CONVERT:
					$login_error = sprintf(
						$this->language->lang[$result['error_msg']],
						($this->config['email_enable']) ? '<a href="' . append_sid("{$this->root_path}ucp.$this->php_ext", 'mode=sendpassword') . '">' : '',
						($this->config['email_enable']) ? '</a>' : '',
						($this->config['board_contact']) ? '<a href="mailto:' . htmlspecialchars($this->config['board_contact']) . '">' : '',
						($this->config['board_contact']) ? '</a>' : ''
					);
				break;

				// Username, password, etc...
				default:
					$login_error = $this->language->lang[$result['error_msg']];

					// Assign admin contact to some error messages
					if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
					{
						$login_error = (!$this->config['board_contact']) ? sprintf($this->language->lang[$result['error_msg']], '', '') : sprintf($this->language->lang[$result['error_msg']], '<a href="mailto:' . htmlspecialchars($this->config['board_contact']) . '">', '</a>');
					}

				break;
			}
		}

		return $login_error;
	}

	/**
	 * Performs a post login redirect
	 */
	protected function perform_redirect()
	{
		$url = append_sid($this->root_path . 'index.' . $this->php_ext);
		redirect($url);
	}
}
