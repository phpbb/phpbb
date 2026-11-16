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

use phpbb\auth\provider_collection;
use phpbb\captcha\factory;
use phpbb\captcha\plugins\confirm_type;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\event\dispatcher;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;

class oauth
{
	/** @var provider_collection Auth provider collection */
	protected $auth_collection;

	/** @var factory Captcha factory */
	protected $captcha_factory;

	/** @var config Config */
	protected $config;

	/** @var dispatcher Event dispatcher */
	protected $dispatcher;

	/** @var helper Controller helper */
	protected $helper;

	/** @var language Language */
	protected $language;

	/** @var request Request class instance */
	protected $request;

	/** @var template Template class */
	protected $template;

	/** @var user User class */
	protected $user;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP extension */
	protected $php_ext;

	/**
	 * Constructor for oauth controller
	 *
	 * @param provider_collection $auth_collection
	 * @param factory $captcha_factory
	 * @param config $config
	 * @param dispatcher $dispatcher
	 * @param helper $helper
	 * @param language $language
	 * @param request $request
	 * @param template $template
	 * @param user $user
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(provider_collection $auth_collection, factory $captcha_factory, config $config,
		dispatcher $dispatcher, helper $helper, language $language, request $request, template $template,
		user $user, string $phpbb_root_path, string $php_ext)
	{
		$this->auth_collection = $auth_collection;
		$this->captcha_factory = $captcha_factory;
		$this->config = $config;
		$this->dispatcher = $dispatcher;
		$this->helper = $helper;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Handle oauth authentication requests
	 *
	 * @param string $oauth_service Oauth service to authenticate with
	 * @return void
	 */
	public function authenticate(string $oauth_service)
	{
		$this->language->add_lang('ucp');

		$this->request->overwrite('oauth_service', $oauth_service);

		$auth_provider = $this->auth_collection->get_provider();

		// Check if auth provider supports linking
		$provider_data = $auth_provider->get_auth_link_data();
		if ($provider_data === null)
		{
			throw new http_exception(Response::HTTP_UNAUTHORIZED, 'UCP_AUTH_LINK_NOT_SUPPORTED');
		}

		if ($this->request->variable('link', false) || $this->user->data['is_registered'])
		{
			$link_data = ['link_method' => 'auth_link'];

			$error = $auth_provider->link_account($link_data);

			if ($error)
			{
				throw new http_exception(Response::HTTP_BAD_REQUEST, $error);
			}

			// Redirect to UCP page if there was no error linking the account
			if ($this->user->data['is_registered'])
			{
				redirect(append_sid("{$this->phpbb_root_path}ucp.$this->php_ext?i=ucp_auth_link&mode=auth_link"));
			} else
			{
				$query_params = [
					'login_link_oauth_service' => $this->request->variable('oauth_service', ''),
				];
				phpbb_redirect_to_controller('phpbb_ucp_oauth_link_account_controller', $query_params);
			}
		}
		else
		{
			// Redirect to login page to handle actual login process for oauth service
			$url_params = [
				'mode'			=> 'login',
				'login'			=> 'external',
				'oauth_service'	=> $this->request->variable('oauth_service', '')
			];

			if ($this->request->is_set('code'))
			{
				$url_params += [
					'code'			=> $this->request->variable('code', ''),
					'state'			=> $this->request->variable('state', ''),
					'scope'			=> $this->request->variable('scope', ''),
				];
			}
			redirect(append_sid("{$this->phpbb_root_path}ucp.$this->php_ext", $url_params));
		}
	}

	/**
	 * Handle login requests for oauth
	 *
	 * @param string $oauth_service Oauth service to login with
	 * @return void
	 */
	public function login(string $oauth_service)
	{
		$this->request->overwrite('oauth_service', $oauth_service);

		$provider = $this->auth_collection->get_provider();
		if (!$provider instanceof \phpbb\auth\provider\oauth\oauth)
		{
			// Handle differently once we move away from the shitty concept of having oauth as auth provider ...
			throw new http_exception(Response::HTTP_UNAUTHORIZED, 'NOT_AUTHORISED');
		}

		// Skip any potential login with username and password by supplying empty values.
		// Only oauth provider can reach this. Valid oauth login requests don't need a username and password and won't use it.
		// Requests that are not valid oauth login requests will be rejected due to the empty password.
		$result = $provider->login('', '');

		// The result parameter is always an array, holding the relevant information...
		if ($result['status'] == LOGIN_SUCCESS)
		{
			$redirect = $this->request->variable('redirect', "{$this->phpbb_root_path}index.{$this->php_ext}");

			/**
			 * This event allows an extension to modify the redirection when a user successfully logs in
			 *
			 * @event core.oauth_login_redirect
			 * @var  string	redirect	Redirect string
			 * @var	bool	admin		Is admin?
			 * @var	array	result		Result from auth provider
			 * @since 3.3.17
			 */
			$vars = array('redirect', 'admin', 'result');
			extract($this->dispatcher->trigger_event('core.oauth_login_redirect', compact($vars)));

			$redirect = reapply_sid($redirect);

			// Special case... the user is effectively banned, but we allow founders to login
			if (defined('IN_CHECK_BAN') && $result['user_row']['user_type'] != USER_FOUNDER)
			{
				return;
			}

			redirect($redirect);
		}

		if ($result['status'] == LOGIN_BREAK)
		{
			trigger_error($result['error_msg']);
		}

		switch ($result['status'])
		{
			case LOGIN_ERROR_ATTEMPTS:
				$captcha = $this->captcha_factory->get_instance($this->config['captcha_plugin']);
				$captcha->init(confirm_type::LOGIN);

				$this->template->assign_vars(array(
					'CAPTCHA_TEMPLATE'			=> $captcha->get_template(),
				));
			// no break;

			// Username, password, etc...
			default:
				$error = $this->language->lang($result['error_msg']);

				// Assign admin contact to some error messages
				if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
				{
					$error = $this->language->lang(
						$result['error_msg'],
						'<a href="' . append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=contactadmin') . '">', '</a>'
					);
				}

			break;
		}

		/**
		 * This event allows an extension to process when a user fails a login attempt
		 *
		 * @event core.oauth_login_failed
		 * @var array   result      Login result data
		 * @var string  username    User name used to login
		 * @var string  password    Password used to login
		 * @var string  error         Error message
		 * @since 3.1.3-RC1
		 */
		$vars = array('result', 'username', 'password', 'error');
		extract($this->dispatcher->trigger_event('core.oauth_login_failed', compact($vars)));

		trigger_error($error);
	}

	/**
	 * Handle linking accounts via Oauth
	 *
	 * @return Response
	 */
	public function link_account(): Response
	{
		$this->language->add_lang('ucp');

		// Initialize necessary variables
		$login_error = null;
		$login_link_error = null;
		$login_username = null;

		// Build the data array
		$data = $this->get_login_link_data_array();

		// Unable to continue without data for linking
		if (empty($data))
		{
			$login_link_error = $this->language->lang('LOGIN_LINK_NO_DATA_PROVIDED');
		}

		// Set the link_method to login_link
		$data['link_method'] = 'login_link';

		// Check if we have all necessary data for login
		$auth_provider = $this->auth_collection->get_provider();
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
				$login_username = $this->request->variable('login_username', '', true, request_interface::POST);
				$login_password = $this->request->untrimmed_variable('login_password', '', true, request_interface::POST);

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
						$this->user->session_create($login_result['user_row']['user_id']);
						redirect(append_sid("{$this->phpbb_root_path}index.$this->php_ext"));
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
			'REGISTER_ACTION'	=> append_sid("{$this->phpbb_root_path}ucp.{$this->php_ext}", 'mode=register'),

			// Login elements
			'LOGIN_ERROR'		=> $login_error,
			'LOGIN_USERNAME'	=> $login_username,
		];

		/**
		 * Event to perform additional actions before ucp_login_link is displayed
		 *
		 * @event core.ucp_login_link_template_after
		 * @var	array							data				Login link data
		 * @var	\phpbb\auth\provider\provider_interface	auth_provider		Auth provider
		 * @var	string							login_link_error	Login link error
		 * @var	string							login_error			Login error
		 * @var	string							login_username		Login username
		 * @var	array							tpl_ary				Template variables
		 * @since 3.2.4-RC1
		 * @changed 3.3.17 Moved to oauth controller
		 */
		$vars = ['data', 'auth_provider', 'login_link_error', 'login_error', 'login_username', 'tpl_ary'];
		extract($this->dispatcher->trigger_event('core.ucp_login_link_template_after', compact($vars)));

		$this->template->assign_vars($tpl_ary);

		return $this->helper->render('ucp_login_link.html', $this->language->lang('UCP_LOGIN_LINK'));
	}

	/**
	 * Builds the login_link data array
	 *
	 * @return	array	All login_link data. This is all GET data whose names
	 *					begin with 'login_link_'
	 */
	protected function get_login_link_data_array(): array
	{
		$var_names = $this->request->variable_names(request_interface::GET);
		$login_link_data = [];
		$string_start_length = strlen('login_link_');

		foreach ($var_names as $var_name)
		{
			if (strpos($var_name, 'login_link_') === 0)
			{
				$key_name = substr($var_name, $string_start_length);
				$login_link_data[$key_name] = $this->request->variable($var_name, '', false, request_interface::GET);
			}
		}

		return $login_link_data;
	}

	/**
	 * Processes the result array from the login process
	 * @param array $result	The login result array
	 * @return	string|null	If there was an error in the process, a string is
	 *						returned. If the login was successful, then null is
	 *						returned.
	 */
	protected function process_login_result(array $result): string|null
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
					$captcha = $this->captcha_factory->get_instance($this->config['captcha_plugin']);
					$captcha->init(confirm_type::LOGIN);

					$this->template->assign_vars(array(
						'CAPTCHA_TEMPLATE'			=> $captcha->get_template(),
					));

					$login_error = $this->language->lang($result['error_msg']);
					break;

				case LOGIN_ERROR_PASSWORD_CONVERT:
					$login_error = $this->language->lang(
						$result['error_msg'],
						($this->config['email_enable']) ? '<a href="' . append_sid("{$this->phpbb_root_path}ucp.{$this->php_ext}", 'mode=sendpassword') . '">' : '',
						($this->config['email_enable']) ? '</a>' : '',
						($this->config['board_contact']) ? '<a href="mailto:' . htmlspecialchars($this->config['board_contact'], ENT_COMPAT) . '">' : '',
						($this->config['board_contact']) ? '</a>' : ''
					);
					break;

				// Username, password, etc...
				default:
					$login_error = $this->language->lang($result['error_msg']);

					// Assign admin contact to some error messages
					if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
					{
						$login_error = (!$this->config['board_contact']) ? $this->language->lang($result['error_msg'], '', '') : $this->language->lang($result['error_msg'], '<a href="mailto:' . htmlspecialchars($this->config['board_contact'], ENT_COMPAT) . '">', '</a>');
					}

					break;
			}
		}

		return $login_error;
	}

	/**
	 * Builds the hidden fields string from the data array.
	 *
	 * @param array $data	This function only includes data in the array
	 *							that has a key that begins with 'login_link_'
	 * @return	string	A string of hidden fields that can be included in the
	 *					template
	 */
	protected function get_hidden_fields(array $data): string
	{
		$fields = array();

		foreach ($data as $key => $value)
		{
			$fields['login_link_' . $key] = $value;
		}

		return build_hidden_fields($fields);
	}
}
