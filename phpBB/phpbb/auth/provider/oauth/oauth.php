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

namespace phpbb\auth\provider\oauth;

use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\ServiceFactory;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Service\ServiceInterface;
use OAuth\OAuth1\Service\AbstractService as OAuth1Service;
use OAuth\OAuth2\Service\AbstractService as OAuth2Service;
use phpbb\auth\provider\base;
use phpbb\auth\provider\db;
use phpbb\auth\provider\oauth\service\exception;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\di\service_collection;
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\user;

/**
 * OAuth authentication provider for phpBB3
 */
class oauth extends base
{
	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var db */
	protected $db_auth;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var language */
	protected $language;

	/** @var request_interface */
	protected $request;

	/** @var service_collection */
	protected $service_providers;

	/** @var user */
	protected $user;

	/** @var string OAuth table: token storage */
	protected $oauth_token_table;

	/** @var string OAuth table: state */
	protected $oauth_state_table;

	/** @var string OAuth table: account association */
	protected $oauth_account_table;

	/** @var string Users table */
	protected $users_table;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param config				$config					Config object
	 * @param driver_interface	$db						Database object
	 * @param db			$db_auth				DB auth provider
	 * @param dispatcher			$dispatcher				Event dispatcher object
	 * @param language			$language				Language object
	 * @param request_interface	$request				Request object
	 * @param service_collection		$service_providers		OAuth providers service collection
	 * @param user						$user					User object
	 * @param string							$oauth_token_table		OAuth table: token storage
	 * @param string							$oauth_state_table		OAuth table: state
	 * @param string							$oauth_account_table	OAuth table: account association
	 * @param string							$users_table			User table
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 */
	public function __construct(
		config $config,
		driver_interface $db,
		db $db_auth,
		dispatcher $dispatcher,
		language $language,
		request_interface $request,
		service_collection $service_providers,
		user $user,
		$oauth_token_table,
		$oauth_state_table,
		$oauth_account_table,
		$users_table,
		$root_path,
		$php_ext
	)
	{
		$this->config				= $config;
		$this->db					= $db;
		$this->db_auth				= $db_auth;
		$this->dispatcher			= $dispatcher;
		$this->language				= $language;
		$this->service_providers	= $service_providers;
		$this->request				= $request;
		$this->user					= $user;

		$this->oauth_token_table	= $oauth_token_table;
		$this->oauth_state_table	= $oauth_state_table;
		$this->oauth_account_table	= $oauth_account_table;
		$this->users_table			= $users_table;
		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
	}

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		// This does not test whether or not the key and secret provided are valid.
		foreach ($this->service_providers as $service_provider)
		{
			$credentials = $service_provider->get_service_credentials();

			if (($credentials['key'] && !$credentials['secret']) || (!$credentials['key'] && $credentials['secret']))
			{
				return $this->language->lang('AUTH_PROVIDER_OAUTH_ERROR_ELEMENT_MISSING');
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function login($username, $password)
	{
		// Temporary workaround for only having one authentication provider available
		if (!$this->request->is_set('oauth_service'))
		{
			return $this->db_auth->login($username, $password);
		}

		// Request the name of the OAuth service
		$provider = $this->request->variable('oauth_service', '', false);
		$service_name = $this->get_service_name($provider);

		if ($provider === '' || !$this->service_providers->offsetExists($service_name))
		{
			return [
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				'error_msg'		=> 'LOGIN_ERROR_OAUTH_SERVICE_DOES_NOT_EXIST',
				'user_row'		=> ['user_id' => ANONYMOUS],
			];
		}

		// Get the service credentials for the given service
		$storage = new token_storage($this->db, $this->user, $this->oauth_token_table, $this->oauth_state_table);
		$query = 'mode=login&login=external&oauth_service=' . $provider;

		try
		{
			/** @var OAuth1Service|OAuth2Service $service */
			$service = $this->get_service($provider, $storage, $query);
		}
		catch (\Exception $e)
		{
			return [
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				'error_msg'		=> $e->getMessage(),
				'user_row'		=> ['user_id' => ANONYMOUS],
			];
		}

		if ($this->is_set_code($service))
		{
			$this->service_providers[$service_name]->set_external_service_provider($service);

			try
			{
				$unique_id = $this->service_providers[$service_name]->perform_auth_login();
			}
			catch (exception $e)
			{
				return [
					'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
					'error_msg'		=> $e->getMessage(),
					'user_row'		=> ['user_id' => ANONYMOUS],
				];
			}

			/**
			 * Check to see if this provider is already associated with an account.
			 *
			 * Enforcing a data type to make sure it are strings and not integers,
			 * so values are quoted in the SQL WHERE statement.
			 */
			$data = [
				'provider'			=> (string) utf8_strtolower($provider),
				'oauth_provider_id'	=> (string) $unique_id
			];

			$sql = 'SELECT user_id 
				FROM ' . $this->oauth_account_table . '
				WHERE ' . $this->db->sql_build_array('SELECT', $data);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$redirect_data = array(
				'auth_provider'				=> 'oauth',
				'login_link_oauth_service'	=> $provider,
			);

			/**
			 * Event is triggered before check if provider is already associated with an account
			 *
			 * @event core.oauth_login_after_check_if_provider_id_has_match
			 * @var array				row				User row
			 * @var array				data			Provider data
			 * @var	array				redirect_data	Data to be appended to the redirect url
			 * @var ServiceInterface	service			OAuth service
			 * @since 3.2.3-RC1
			 * @changed 3.2.6-RC1						Added redirect_data
			 */
			$vars = [
				'row',
				'data',
				'redirect_data',
				'service',
			];
			extract($this->dispatcher->trigger_event('core.oauth_login_after_check_if_provider_id_has_match', compact($vars)));

			if (!$row)
			{
				// The user does not yet exist, ask to link or create profile
				return [
					'status'		=> LOGIN_SUCCESS_LINK_PROFILE,
					'error_msg'		=> 'LOGIN_OAUTH_ACCOUNT_NOT_LINKED',
					'user_row'		=> [],
					'redirect_data'	=> $redirect_data,
				];
			}

			// Retrieve the user's account
			$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_ip, user_type, user_login_attempts
				FROM ' . $this->users_table . '
				WHERE user_id = ' . (int) $row['user_id'];
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				return [
					'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
					'error_msg'		=> 'AUTH_PROVIDER_OAUTH_ERROR_INVALID_ENTRY',
					'user_row'		=> ['user_id' => ANONYMOUS],
				];
			}

			/**
			 * Check if the user is banned.
			 * The fourth parameter (return) has to be true, otherwise the OAuth login is still called and
			 * an uncaught exception is thrown as there is no token stored in the database.
			 */
			$ban = $this->user->check_ban($row['user_id'], $row['user_ip'], $row['user_email'], true);

			if (!empty($ban))
			{
				$till_date = !empty($ban['ban_end']) ? $this->user->format_date($ban['ban_end']) : '';
				$message = !empty($ban['ban_end']) ? 'BOARD_BAN_TIME' : 'BOARD_BAN_PERM';

				$contact_link = phpbb_get_board_contact_link($this->config, $this->root_path, $this->php_ext);

				$message = $this->language->lang($message, $till_date, '<a href="' . $contact_link . '">', '</a>');
				$message .= !empty($ban['ban_give_reason']) ? '<br /><br />' . $this->language->lang('BOARD_BAN_REASON', $ban['ban_give_reason']) : '';
				$message .= !empty($ban['ban_triggered_by']) ? '<br /><br /><em>' . $this->language->lang('BAN_TRIGGERED_BY_' . utf8_strtoupper($ban['ban_triggered_by'])) . '</em>' : '';

				return [
					'status'	=> LOGIN_BREAK,
					'error_msg'	=> $message,
					'user_row'	=> $row,
				];
			}

			// Update token storage to store the user_id
			$storage->set_user_id($row['user_id']);

			/**
			 * Event is triggered after user is successfully logged in via OAuth.
			 *
			 * @event core.auth_oauth_login_after
			 * @var array	row		User row
			 * @since 3.1.11-RC1
			 */
			$vars = [
				'row',
			];
			extract($this->dispatcher->trigger_event('core.auth_oauth_login_after', compact($vars)));

			// The user is now authenticated and can be logged in
			return [
				'status'		=> LOGIN_SUCCESS,
				'error_msg'		=> false,
				'user_row'		=> $row,
			];
		}
		else
		{
			return $this->set_redirect($service);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_login_data()
	{
		$login_data = [
			'TEMPLATE_FILE'		=> 'login_body_oauth.html',
			'BLOCK_VAR_NAME'	=> 'oauth',
			'BLOCK_VARS'		=> [],
		];

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			// Only include data if the credentials are set
			$credentials = $service_provider->get_service_credentials();

			if ($credentials['key'] && $credentials['secret'])
			{
				$provider = $this->get_provider($service_name);
				$redirect_url = generate_board_url() . '/ucp.' . $this->php_ext . '?mode=login&login=external&oauth_service=' . $provider;

				$login_data['BLOCK_VARS'][$service_name] = [
					'REDIRECT_URL'	=> redirect($redirect_url, true),
					'SERVICE_NAME'	=> $this->get_provider_title($provider),
				];
			}
		}

		return $login_data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function acp()
	{
		$ret = [];

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			$provider = $this->get_provider($service_name);

			$provider = utf8_strtolower($provider);

			$ret[] = 'auth_oauth_' . $provider . '_key';
			$ret[] = 'auth_oauth_' . $provider . '_secret';
		}

		return $ret;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_acp_template($new_config)
	{
		$ret = [
			'BLOCK_VAR_NAME'	=> 'oauth_services',
			'BLOCK_VARS'		=> [],
			'TEMPLATE_FILE'		=> 'auth_provider_oauth.html',
			'TEMPLATE_VARS'		=> [],
		];

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			$provider = $this->get_provider($service_name);

			$ret['BLOCK_VARS'][$provider] = [
				'NAME'			=> $provider,
				'ACTUAL_NAME'	=> $this->get_provider_title($provider),
				'KEY'			=> $new_config['auth_oauth_' . utf8_strtolower($provider) . '_key'],
				'SECRET'		=> $new_config['auth_oauth_' . utf8_strtolower($provider) . '_secret'],
			];
		}

		return $ret;
	}

	/**
	 * {@inheritdoc}
	 */
	public function login_link_has_necessary_data(array $login_link_data)
	{
		if (empty($login_link_data))
		{
			return 'LOGIN_LINK_NO_DATA_PROVIDED';
		}

		if (!array_key_exists('oauth_service', $login_link_data) || !$login_link_data['oauth_service'] ||
			!array_key_exists('link_method', $login_link_data) || !$login_link_data['link_method'])
		{
			return 'LOGIN_LINK_MISSING_DATA';
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function link_account(array $link_data)
	{
		// Check for a valid link method (auth_link or login_link)
		if (!array_key_exists('link_method', $link_data) ||
			!in_array($link_data['link_method'], ['auth_link', 'login_link']))
		{
			return 'LOGIN_LINK_MISSING_DATA';
		}

		// We must have an oauth_service listed, check for it two ways
		if (!array_key_exists('oauth_service', $link_data) || !$link_data['oauth_service'])
		{
			$link_data['oauth_service'] = $this->request->variable('oauth_service', '');

			if (!$link_data['oauth_service'])
			{
				return 'LOGIN_LINK_MISSING_DATA';
			}
		}

		$service_name = $this->get_service_name($link_data['oauth_service']);

		if (!$this->service_providers->offsetExists($service_name))
		{
			return 'LOGIN_ERROR_OAUTH_SERVICE_DOES_NOT_EXIST';
		}

		switch ($link_data['link_method'])
		{
			case 'auth_link':
				return $this->link_account_auth_link($link_data, $service_name);
			case 'login_link':
				return $this->link_account_login_link($link_data, $service_name);
			default:
				return 'LOGIN_LINK_MISSING_DATA';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function logout($data, $new_session)
	{
		// Clear all tokens belonging to the user
		$storage = new token_storage($this->db, $this->user, $this->oauth_token_table, $this->oauth_state_table);
		$storage->clearAllTokens();

		return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_auth_link_data($user_id = 0)
	{
		$user_ids	= [];
		$block_vars	= [];

		$sql = 'SELECT oauth_provider_id, provider
 			FROM ' . $this->oauth_account_table . '
			WHERE user_id = ' . ($user_id > 0 ? (int) $user_id : (int) $this->user->data['user_id']);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_ids[$row['provider']] = $row['oauth_provider_id'];
		}
		$this->db->sql_freeresult($result);

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			// Only include data if the credentials are set
			$credentials = $service_provider->get_service_credentials();

			if ($credentials['key'] && $credentials['secret'])
			{
				$provider = $this->get_provider($service_name);

				$block_vars[$service_name] = [
					'SERVICE_NAME'	=> $this->get_provider_title($provider),
					'UNIQUE_ID'		=> isset($user_ids[$provider]) ? $user_ids[$provider] : null,
					'HIDDEN_FIELDS'	=> [
						'link'			=> !isset($user_ids[$provider]),
						'oauth_service' => $provider,
					],
				];
			}
		}

		return [
			'BLOCK_VAR_NAME'	=> 'oauth',
			'BLOCK_VARS'		=> $block_vars,

			'TEMPLATE_FILE'		=> 'ucp_auth_link_oauth.html',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function unlink_account(array $link_data)
	{
		if (!array_key_exists('oauth_service', $link_data) || !$link_data['oauth_service'])
		{
			return 'LOGIN_LINK_MISSING_DATA';
		}

		// Remove user specified in $link_data if possible
		$user_id = isset($link_data['user_id']) ? $link_data['user_id'] : $this->user->data['user_id'];

		// Remove the link
		$sql = 'DELETE FROM ' . $this->oauth_account_table . "
			WHERE provider = '" . $this->db->sql_escape($link_data['oauth_service']) . "'
				AND user_id = " . (int) $user_id;
		$this->db->sql_query($sql);

		$service_name = $this->get_service_name($link_data['oauth_service']);

		// Clear all tokens belonging to the user on this service
		$storage = new token_storage($this->db, $this->user, $this->oauth_token_table, $this->oauth_state_table);
		$storage->clearToken($service_name);

		return false;
	}

	/**
	 * Performs the account linking for login_link.
	 *
	 * @param array		$link_data		The same variable given to
	 * 									{@see \phpbb\auth\provider\provider_interface::link_account}
	 * @param string	$service_name	The name of the service being used in linking.
	 * @return string|false				Returns a language key (string) if an error is encountered,
	 * 									or false on success.
	 */
	protected function link_account_login_link(array $link_data, $service_name)
	{
		$storage = new token_storage($this->db, $this->user, $this->oauth_token_table, $this->oauth_state_table);

		// Check for an access token, they should have one
		if (!$storage->has_access_token_by_session($service_name))
		{
			return 'LOGIN_LINK_ERROR_OAUTH_NO_ACCESS_TOKEN';
		}

		// Prepare for an authentication request
		$query = 'mode=login_link&login_link_oauth_service=' . $link_data['oauth_service'];

		try
		{
			$service = $this->get_service($link_data['oauth_service'], $storage, $query);
		}
		catch (\Exception $e)
		{
			return $e->getMessage();
		}

		$this->service_providers[$service_name]->set_external_service_provider($service);

		try
		{
			// The user has already authenticated successfully, request to authenticate again
			$unique_id = $this->service_providers[$service_name]->perform_token_auth();
		}
		catch (exception $e)
		{
			return $e->getMessage();
		}

		// Insert into table, they will be able to log in after this
		$data = [
			'user_id'			=> $link_data['user_id'],
			'provider'			=> utf8_strtolower($link_data['oauth_service']),
			'oauth_provider_id'	=> $unique_id,
		];

		$this->link_account_perform_link($data);

		// Update token storage to store the user_id
		$storage->set_user_id($link_data['user_id']);

		return false;
	}

	/**
	 * Performs the account linking for auth_link.
	 *
	 * @param array		$link_data		The same variable given to
	 * 									{@see \phpbb\auth\provider\provider_interface::link_account}
	 * @param string	$service_name	The name of the service being used in linking.
	 * @return string|false				Returns a language constant (string) if an error is encountered,
	 * 									or false on success.
	 */
	protected function link_account_auth_link(array $link_data, $service_name)
	{
		$storage = new token_storage($this->db, $this->user, $this->oauth_token_table, $this->oauth_state_table);
		$query = 'i=ucp_auth_link&mode=auth_link&link=1&oauth_service=' . $link_data['oauth_service'];

		try
		{
			/** @var OAuth1Service|OAuth2Service $service */
			$service = $this->get_service($link_data['oauth_service'], $storage, $query);
		}
		catch (\Exception $e)
		{
			return $e->getMessage();
		}

		if ($this->is_set_code($service))
		{
			$this->service_providers[$service_name]->set_external_service_provider($service);

			try
			{
				$unique_id = $this->service_providers[$service_name]->perform_auth_login();
			}
			catch (exception $e)
			{
				return $e->getMessage();
			}

			// Insert into table, they will be able to log in after this
			$data = [
				'user_id'			=> $this->user->data['user_id'],
				'provider'			=> utf8_strtolower($link_data['oauth_service']),
				'oauth_provider_id'	=> $unique_id,
			];

			$this->link_account_perform_link($data);

			return false;
		}
		else
		{
			return $this->set_redirect($service);
		}
	}

	/**
	 * Performs the query that inserts an account link
	 *
	 * @param	array	$data	This array is passed to db->sql_build_array
	 */
	protected function link_account_perform_link(array $data)
	{
		// Check if the external account is already associated with other user
		$sql = 'SELECT user_id
			FROM ' . $this->oauth_account_table . "
			WHERE provider = '" . $this->db->sql_escape($data['provider']) . "'
				AND oauth_provider_id = '" . $this->db->sql_escape($data['oauth_provider_id']) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			trigger_error('AUTH_PROVIDER_OAUTH_ERROR_ALREADY_LINKED');
		}

		// Link account
		$sql = 'INSERT INTO ' . $this->oauth_account_table . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);

		/**
		 * Event is triggered after user links account.
		 *
		 * @event core.auth_oauth_link_after
		 * @var array	data	User row
		 * @since 3.1.11-RC1
		 */
		$vars = [
			'data',
		];
		extract($this->dispatcher->trigger_event('core.auth_oauth_link_after', compact($vars)));
	}

	/**
	 * Returns a new service object.
	 *
	 * @param string			$provider		The name of the provider
	 * @param token_storage		$storage		Token storage object
	 * @param string			$query			The query string used for the redirect uri
	 * @return ServiceInterface
	 * @throws exception						When OAuth service was not created
	 */
	protected function get_service($provider, token_storage $storage, $query)
	{
		$service_name = $this->get_service_name($provider);

		/** @see \phpbb\auth\provider\oauth\service\service_interface::get_service_credentials */
		$service_credentials = $this->service_providers[$service_name]->get_service_credentials();

		/** @see \phpbb\auth\provider\oauth\service\service_interface::get_auth_scope */
		$scopes = $this->service_providers[$service_name]->get_auth_scope();

		$callback = generate_board_url() . "/ucp.{$this->php_ext}?{$query}";

		// Setup the credentials for the requests
		$credentials = new Credentials(
			$service_credentials['key'],
			$service_credentials['secret'],
			$callback
		);

		$service_factory = new ServiceFactory;

		// Allow providers to register a custom class or override the provider name
		if ($class = $this->service_providers[$service_name]->get_external_service_class())
		{
			if (class_exists($class))
			{
				try
				{
					$service_factory->registerService($provider, $class);
				}
				catch (\OAuth\Common\Exception\Exception $e)
				{
					throw new exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
				}
			}
			else
			{
				$provider = $class;
			}
		}

		$service = $service_factory->createService($provider, $credentials, $storage, $scopes);

		if (!$service)
		{
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_SERVICE_NOT_CREATED');
		}

		return $service;
	}

	/**
	 * Returns the service name for an OAuth provider name.
	 *
	 * @param string	$provider		The OAuth provider name
	 * @return string					The service name
	 */
	protected function get_service_name($provider)
	{
		if (strpos($provider, 'auth.provider.oauth.service.') !== 0)
		{
			$provider = 'auth.provider.oauth.service.' . utf8_strtolower($provider);
		}

		return $provider;
	}

	/**
	 * Returns the OAuth provider name from a service name.
	 *
	 * @param string	$service_name	The service name
	 * @return string					The OAuth provider name
	 */
	protected function get_provider($service_name)
	{
		return str_replace('auth.provider.oauth.service.', '', $service_name);
	}

	/**
	 * Returns the localized title for the OAuth provider.
	 *
	 * @param string	$provider		The OAuth provider name
	 * @return string					The OAuth provider title
	 */
	protected function get_provider_title($provider)
	{
		return $this->language->lang('AUTH_PROVIDER_OAUTH_SERVICE_' . utf8_strtoupper($provider));
	}

	/**
	 * Returns whether or not the authorization code is set.
	 *
	 * @param OAuth1Service|OAuth2Service	$service	The external OAuth service
	 * @return bool										Whether or not the authorization code is set in the URL
	 *                       							for the respective OAuth service's version
	 */
	protected function is_set_code($service)
	{
		switch ($service::OAUTH_VERSION)
		{
			case 1:
				return $this->request->is_set('oauth_token', request_interface::GET);

			case 2:
				return $this->request->is_set('code', request_interface::GET);

			default:
				return false;
		}
	}

	/**
	 * Sets a redirect to the authorization uri.
	 *
	 * @param OAuth1Service|OAuth2Service $service		The external OAuth service
	 * @return array|false								Array if an error occurred,
	 *                            						false on success
	 */
	protected function set_redirect($service)
	{
		$parameters = [];

		if ($service::OAUTH_VERSION === 1)
		{
			try
			{
				$token		= $service->requestRequestToken();
				$parameters	= ['oauth_token' => $token->getRequestToken()];
			}
			catch (TokenResponseException $e)
			{
				return [
					'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
					'error_msg'		=> $e->getMessage(),
					'user_row'		=> ['user_id' => ANONYMOUS],
				];
			}
		}

		redirect($service->getAuthorizationUri($parameters), false, true);

		return false;
	}
}
