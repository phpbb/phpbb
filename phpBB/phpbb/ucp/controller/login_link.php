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

use phpbb\exception\http_exception;

/**
 * ucp_login_link
 * Allows users of external accounts link those accounts to their phpBB accounts
 * during an attempted login.
 */
class login_link
{
	/** @var \phpbb\captcha\factory */
	protected $captcha_factory;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\auth\provider_collection */
	protected $provider_collection;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\captcha\factory			$captcha_factory		Captcha factory object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\event\dispatcher			$dispatcher				Event dispatcher object
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$lang					Language object
	 * @param \phpbb\auth\provider_collection	$provider_collection	Auth provider collection
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 */
	public function __construct(
		\phpbb\captcha\factory $captcha_factory,
		\phpbb\config\config $config,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\auth\provider_collection $provider_collection,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext
	)
	{
		$this->captcha_factory		= $captcha_factory;
		$this->config				= $config;
		$this->dispatcher			= $dispatcher;
		$this->helper				= $helper;
		$this->lang					= $lang;
		$this->provider_collection	= $provider_collection;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
	}

	/**
	 * Generates and handle the login link process.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	function main()
	{
		if ($this->user->data['is_registered'])
		{
			return redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		// Initialize necessary variables
		$login_error = null;
		$login_username = null;
		$login_link_error = null;

		// Build the data array
		$data = $this->get_login_link_data_array();

		// Ensure the person was sent here with login_link data
		if (empty($data))
		{
			$login_link_error = $this->lang->lang('LOGIN_LINK_NO_DATA_PROVIDED');
		}

		// Use the auth_provider requested even if different from configured
		$auth_provider = $this->provider_collection->get_provider($this->request->variable('auth_provider', ''));

		// Set the link_method to login_link
		$data['link_method'] = 'login_link';

		// Have the authentication provider check that all necessary data is available
		$result = $auth_provider->login_link_has_necessary_data($data);

		if ($result !== null)
		{
			$login_link_error = $this->lang->lang($result);
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
						$login_link_error = $this->lang->lang($result);
					}
					else
					{
						// Finish login
						$this->user->session_create($login_result['user_row']['user_id'], false, false, true);

						// Perform a redirect as the account has been linked
						return redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
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
			'REGISTER_ACTION'		=> $this->helper->route('ucp_account', ['mode' => 'register']),

			// Login elements
			'LOGIN_ERROR'			=> $login_error,
			'LOGIN_USERNAME'		=> $login_username,
		];

		/**
		 * Event to perform additional actions before ucp_login_link is displayed
		 *
		 * @event core.ucp_login_link_template_after
		 * @var array									data				Login link data
		 * @var \phpbb\auth\provider\provider_interface	auth_provider		Auth provider
		 * @var string									login_link_error	Login link error
		 * @var string									login_error			Login error
		 * @var string									login_username		Login username
		 * @var array									tpl_ary				Template variables
		 * @since 3.2.4-RC1
		 */
		$vars = ['data', 'auth_provider', 'login_link_error', 'login_error', 'login_username', 'tpl_ary'];
		extract($this->dispatcher->trigger_event('core.ucp_login_link_template_after', compact($vars)));

		$this->template->assign_vars($tpl_ary);

		return $this->helper->render('ucp_login_link.html', $this->lang->lang('UCP_LOGIN_LINK'));
	}

	/**
	 * Builds the hidden fields string from the data array.
	 *
	 * @param array		$data		This function only includes data in the array
	 *								that has a key that begins with 'login_link_'
	 * @return string				A string of hidden fields that can be included in the template
	 */
	protected function get_hidden_fields(array $data)
	{
		$fields = [];

		foreach ($data as $key => $value)
		{
			$fields['login_link_' . $key] = $value;
		}

		return build_hidden_fields($fields);
	}

	/**
	 * Builds the login_link data array.
	 *
	 * @return array				All login_link data. This is all GET data whose names
	 *								begin with 'login_link_'
	 * @return array				The login_link data array
	 */
	protected function get_login_link_data_array()
	{
		$prefix = 'login_link_';
		$prefix_length = strlen($prefix);
		$login_link_data = [];

		$var_names = $this->request->variable_names(\phpbb\request\request_interface::GET);

		foreach ($var_names as $var_name)
		{
			if (strpos($var_name, $prefix) === 0)
			{
				$key_name = substr($var_name, $prefix_length);
				$login_link_data[$key_name] = $this->request->variable($var_name, '', false, \phpbb\request\request_interface::GET);
			}
		}

		return $login_link_data;
	}

	/**
	 * Processes the result array from the login process.
	 *
	 * @param array			$result		The login result array
	 * @return string|null				string when there was an error in the process,
	 *									null when the login was successful.
	 */
	protected function process_login_result(array $result)
	{
		$login_error = null;

		if ($result['status'] != LOGIN_SUCCESS)
		{
			// Handle all errors first
			if ($result['status'] == LOGIN_BREAK)
			{
				throw new http_exception(400, $result['error_msg']);
			}

			switch ($result['status'])
			{
				case LOGIN_ERROR_ATTEMPTS:

					$captcha = $this->captcha_factory->get_instance($this->config['captcha_plugin']);
					$captcha->init(CONFIRM_LOGIN);

					$this->template->assign_vars([
						'CAPTCHA_TEMPLATE'		=> $captcha->get_template(),
					]);

					$login_error = $this->lang->lang([$result['error_msg']]);
				break;

				case LOGIN_ERROR_PASSWORD_CONVERT:
					$login_error = $this->lang->lang(
						[$result['error_msg']],
						$this->config['email_enable'] ? '<a href="' . $this->helper->route('ucp_account', ['mode' => 'send_password']) . '">' : '',
						$this->config['email_enable'] ? '</a>' : '',
						$this->config['board_contact'] ? '<a href="mailto:' . htmlspecialchars($this->config['board_contact']) . '">' : '',
						$this->config['board_contact'] ? '</a>' : ''
					);
				break;

				// Username, password, etc...
				default:
					$login_error = $this->lang->lang([$result['error_msg']]);

					// Assign admin contact to some error messages
					if ($result['error_msg'] === 'LOGIN_ERROR_USERNAME' || $result['error_msg'] === 'LOGIN_ERROR_PASSWORD')
					{
						$login_error = !$this->config['board_contact'] ? $this->lang->lang([$result['error_msg']], '', '') : $this->lang->lang([$result['error_msg']], '<a href="mailto:' . htmlspecialchars($this->config['board_contact']) . '">', '</a>');
					}
				break;
			}
		}

		return $login_error;
	}
}
