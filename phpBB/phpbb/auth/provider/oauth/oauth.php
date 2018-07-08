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

use OAuth\Common\Consumer\Credentials;

/**
* OAuth authentication provider for phpBB3
*/
class oauth extends \phpbb\auth\provider\base
{
	/**
	* Database driver
	*
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* phpBB config
	*
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* phpBB passwords manager
	*
	* @var \phpbb\passwords\manager
	*/
	protected $passwords_manager;

	/**
	* phpBB request object
	*
	* @var \phpbb\request\request_interface
	*/
	protected $request;

	/**
	* phpBB user
	*
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* OAuth token table
	*
	* @var string
	*/
	protected $auth_provider_oauth_token_storage_table;

	/**
	* OAuth state table
	*
	* @var string
	*/
	protected $auth_provider_oauth_state_table;

	/**
	* OAuth account association table
	*
	* @var string
	*/
	protected $auth_provider_oauth_token_account_assoc;

	/**
	* All OAuth service providers
	*
	* @var \phpbb\di\service_collection Contains \phpbb\auth\provider\oauth\service_interface
	*/
	protected $service_providers;

	/**
	* Users table
	*
	* @var string
	*/
	protected $users_table;

	/**
	* Cached current uri object
	*
	* @var \OAuth\Common\Http\Uri\UriInterface|null
	*/
	protected $current_uri;

	/**
	* DI container
	*
	* @var \Symfony\Component\DependencyInjection\ContainerInterface
	*/
	protected $phpbb_container;

	/**
	* phpBB event dispatcher
	*
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $dispatcher;

	/**
	* phpBB root path
	*
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP file extension
	*
	* @var string
	*/
	protected $php_ext;

	/**
	* OAuth Authentication Constructor
	*
	* @param	\phpbb\db\driver\driver_interface	$db
	* @param	\phpbb\config\config	$config
	* @param	\phpbb\passwords\manager	$passwords_manager
	* @param	\phpbb\request\request_interface	$request
	* @param	\phpbb\user		$user
	* @param	string			$auth_provider_oauth_token_storage_table
	* @param	string			$auth_provider_oauth_state_table
	* @param	string			$auth_provider_oauth_token_account_assoc
	* @param	\phpbb\di\service_collection	$service_providers Contains \phpbb\auth\provider\oauth\service_interface
	* @param	string			$users_table
	* @param	\Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container DI container
	* @param	\phpbb\event\dispatcher_interface $dispatcher phpBB event dispatcher
	* @param	string			$phpbb_root_path
	* @param	string			$php_ext
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\passwords\manager $passwords_manager, \phpbb\request\request_interface $request, \phpbb\user $user, $auth_provider_oauth_token_storage_table, $auth_provider_oauth_state_table, $auth_provider_oauth_token_account_assoc, \phpbb\di\service_collection $service_providers, $users_table, \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container, \phpbb\event\dispatcher_interface $dispatcher, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->passwords_manager = $passwords_manager;
		$this->request = $request;
		$this->user = $user;
		$this->auth_provider_oauth_token_storage_table = $auth_provider_oauth_token_storage_table;
		$this->auth_provider_oauth_state_table = $auth_provider_oauth_state_table;
		$this->auth_provider_oauth_token_account_assoc = $auth_provider_oauth_token_account_assoc;
		$this->service_providers = $service_providers;
		$this->users_table = $users_table;
		$this->phpbb_container = $phpbb_container;
		$this->dispatcher = $dispatcher;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
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
				return $this->user->lang['AUTH_PROVIDER_OAUTH_ERROR_ELEMENT_MISSING'];
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
			$provider = new \phpbb\auth\provider\db($this->db, $this->config, $this->passwords_manager, $this->request, $this->user, $this->phpbb_container, $this->phpbb_root_path, $this->php_ext);
			return $provider->login($username, $password);
		}

		// Requst the name of the OAuth service
		$service_name_original = $this->request->variable('oauth_service', '', false);
		$service_name = 'auth.provider.oauth.service.' . strtolower($service_name_original);
		if ($service_name_original === '' || !array_key_exists($service_name, $this->service_providers))
		{
			return array(
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				'error_msg'		=> 'LOGIN_ERROR_OAUTH_SERVICE_DOES_NOT_EXIST',
				'user_row'		=> array('user_id' => ANONYMOUS),
			);
		}

		// Get the service credentials for the given service
		$service_credentials = $this->service_providers[$service_name]->get_service_credentials();

		$storage = new \phpbb\auth\provider\oauth\token_storage($this->db, $this->user, $this->auth_provider_oauth_token_storage_table, $this->auth_provider_oauth_state_table);
		$query = 'mode=login&login=external&oauth_service=' . $service_name_original;
		$service = $this->get_service($service_name_original, $storage, $service_credentials, $query, $this->service_providers[$service_name]->get_auth_scope());

		if (($service::OAUTH_VERSION === 2 && $this->request->is_set('code', \phpbb\request\request_interface::GET))
			|| ($service::OAUTH_VERSION === 1 && $this->request->is_set('oauth_token', \phpbb\request\request_interface::GET)))
		{
			$this->service_providers[$service_name]->set_external_service_provider($service);
			$unique_id = $this->service_providers[$service_name]->perform_auth_login();

			// Check to see if this provider is already assosciated with an account
			$data = array(
				'provider'	=> $service_name_original,
				'oauth_provider_id'	=> $unique_id
			);
			$sql = 'SELECT user_id FROM ' . $this->auth_provider_oauth_token_account_assoc . '
				WHERE ' . $this->db->sql_build_array('SELECT', $data);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			/**
			* Event is triggered before check if provider is already associated with an account
			*
			* @event core.oauth_login_after_check_if_provider_id_has_match
			* @var	array									row		User row
			* @var	array									data	Provider data
			* @var	\OAuth\Common\Service\ServiceInterface	service	OAuth service
			* @since 3.2.3-RC1
			*/
			$vars = array(
				'row',
				'data',
				'service',
			);
			extract($this->dispatcher->trigger_event('core.oauth_login_after_check_if_provider_id_has_match', compact($vars)));

			if (!$row)
			{
				// The user does not yet exist, ask to link or create profile
				return array(
					'status'		=> LOGIN_SUCCESS_LINK_PROFILE,
					'error_msg'		=> 'LOGIN_OAUTH_ACCOUNT_NOT_LINKED',
					'user_row'		=> array(),
					'redirect_data'	=> array(
						'auth_provider'				=> 'oauth',
						'login_link_oauth_service'	=> $service_name_original,
					),
				);
			}

			// Retrieve the user's account
			$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type, user_login_attempts
				FROM ' . $this->users_table . '
					WHERE user_id = ' . (int) $row['user_id'];
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				throw new \Exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_ENTRY');
			}

			// Update token storage to store the user_id
			$storage->set_user_id($row['user_id']);

			/**
			* Event is triggered after user is successfuly logged in via OAuth.
			*
			* @event core.auth_oauth_login_after
			* @var    array    row    User row
			* @since 3.1.11-RC1
			*/
			$vars = array(
				'row',
			);
			extract($this->dispatcher->trigger_event('core.auth_oauth_login_after', compact($vars)));

			// The user is now authenticated and can be logged in
			return array(
				'status'		=> LOGIN_SUCCESS,
				'error_msg'		=> false,
				'user_row'		=> $row,
			);
		}
		else
		{
			if ($service::OAUTH_VERSION === 1)
			{
				$token = $service->requestRequestToken();
				$url = $service->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
			}
			else
			{
				$url = $service->getAuthorizationUri();
			}
			header('Location: ' . $url);
		}
	}

	/**
	* Returns the cached current_uri object or creates and caches it if it is
	* not already created. In each case the query string is updated based on
	* the $query parameter.
	*
	* @param	string	$service_name	The name of the service
	* @param	string	$query			The query string of the current_uri
	*									used in redirects
	* @return	\OAuth\Common\Http\Uri\UriInterface
	*/
	protected function get_current_uri($service_name, $query)
	{
		if ($this->current_uri)
		{
			$this->current_uri->setQuery($query);
			return $this->current_uri;
		}

		$uri_factory = new \OAuth\Common\Http\Uri\UriFactory();
		$super_globals = $this->request->get_super_global(\phpbb\request\request_interface::SERVER);
		if (!empty($super_globals['HTTP_X_FORWARDED_PROTO']) && $super_globals['HTTP_X_FORWARDED_PROTO'] === 'https')
		{
			$super_globals['HTTPS'] = 'on';
			$super_globals['SERVER_PORT'] = 443;
		}
		$current_uri = $uri_factory->createFromSuperGlobalArray($super_globals);
		$current_uri->setQuery($query);

		$this->current_uri = $current_uri;
		return $current_uri;
	}

	/**
	* Returns a new service object
	*
	* @param	string	$service_name			The name of the service
	* @param	\phpbb\auth\provider\oauth\token_storage $storage
	* @param	array	$service_credentials	{@see \phpbb\auth\provider\oauth\oauth::get_service_credentials}
	* @param	string	$query					The query string of the
	*											current_uri used in redirection
	* @param	array	$scopes					The scope of the request against
	*											the api.
	* @return	\OAuth\Common\Service\ServiceInterface
	* @throws	\Exception
	*/
	protected function get_service($service_name, \phpbb\auth\provider\oauth\token_storage $storage, array $service_credentials, $query, array $scopes = array())
	{
		$current_uri = $this->get_current_uri($service_name, $query);

		// Setup the credentials for the requests
		$credentials = new Credentials(
			$service_credentials['key'],
			$service_credentials['secret'],
			$current_uri->getAbsoluteUri()
		);

		$service_factory = new \OAuth\ServiceFactory();
		$service = $service_factory->createService($service_name, $credentials, $storage, $scopes);

		if (!$service)
		{
			throw new \Exception('AUTH_PROVIDER_OAUTH_ERROR_SERVICE_NOT_CREATED');
		}

		return $service;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_login_data()
	{
		$login_data = array(
			'TEMPLATE_FILE'		=> 'login_body_oauth.html',
			'BLOCK_VAR_NAME'	=> 'oauth',
			'BLOCK_VARS'		=> array(),
		);

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			// Only include data if the credentials are set
			$credentials = $service_provider->get_service_credentials();
			if ($credentials['key'] && $credentials['secret'])
			{
				$actual_name = str_replace('auth.provider.oauth.service.', '', $service_name);
				$redirect_url = build_url(false) . '&login=external&oauth_service=' . $actual_name;
				$login_data['BLOCK_VARS'][$service_name] = array(
					'REDIRECT_URL'	=> redirect($redirect_url, true),
					'SERVICE_NAME'	=> $this->user->lang['AUTH_PROVIDER_OAUTH_SERVICE_' . strtoupper($actual_name)],
				);
			}
		}

		return $login_data;
	}

	/**
	* {@inheritdoc}
	*/
	public function acp()
	{
		$ret = array();

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			$actual_name = str_replace('auth.provider.oauth.service.', '', $service_name);
			$ret[] = 'auth_oauth_' . $actual_name . '_key';
			$ret[] = 'auth_oauth_' . $actual_name . '_secret';
		}

		return $ret;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_acp_template($new_config)
	{
		$ret = array(
			'BLOCK_VAR_NAME'	=> 'oauth_services',
			'BLOCK_VARS'		=> array(),
			'TEMPLATE_FILE'		=> 'auth_provider_oauth.html',
			'TEMPLATE_VARS'		=> array(),
		);

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			$actual_name = str_replace('auth.provider.oauth.service.', '', $service_name);
			$ret['BLOCK_VARS'][$actual_name] = array(
				'ACTUAL_NAME'	=> $this->user->lang['AUTH_PROVIDER_OAUTH_SERVICE_' . strtoupper($actual_name)],
				'KEY'			=> $new_config['auth_oauth_' . $actual_name . '_key'],
				'NAME'			=> $actual_name,
				'SECRET'		=> $new_config['auth_oauth_' . $actual_name . '_secret'],
			);
		}

		return $ret;
	}

	/**
	* {@inheritdoc}
	*/
	public function login_link_has_necessary_data($login_link_data)
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
			!in_array($link_data['link_method'], array(
				'auth_link',
				'login_link',
			)))
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

		$service_name = 'auth.provider.oauth.service.' . strtolower($link_data['oauth_service']);
		if (!array_key_exists($service_name, $this->service_providers))
		{
			return 'LOGIN_ERROR_OAUTH_SERVICE_DOES_NOT_EXIST';
		}

		switch ($link_data['link_method'])
		{
			case 'auth_link':
				return $this->link_account_auth_link($link_data, $service_name);
			case 'login_link':
				return $this->link_account_login_link($link_data, $service_name);
		}
	}

	/**
	* Performs the account linking for login_link
	*
	* @param	array	$link_data		The same variable given to {@see \phpbb\auth\provider\provider_interface::link_account}
	* @param	string	$service_name	The name of the service being used in
	*									linking.
	* @return	string|null	Returns a language constant (string) if an error is
	*						encountered, or null on success.
	*/
	protected function link_account_login_link(array $link_data, $service_name)
	{
		$storage = new \phpbb\auth\provider\oauth\token_storage($this->db, $this->user, $this->auth_provider_oauth_token_storage_table, $this->auth_provider_oauth_state_table);

		// Check for an access token, they should have one
		if (!$storage->has_access_token_by_session($service_name))
		{
			return 'LOGIN_LINK_ERROR_OAUTH_NO_ACCESS_TOKEN';
		}

		// Prepare the query string
		$query = 'mode=login_link&login_link_oauth_service=' . strtolower($link_data['oauth_service']);

		// Prepare for an authentication request
		$service_credentials = $this->service_providers[$service_name]->get_service_credentials();
		$scopes = $this->service_providers[$service_name]->get_auth_scope();
		$service = $this->get_service(strtolower($link_data['oauth_service']), $storage, $service_credentials, $query, $scopes);
		$this->service_providers[$service_name]->set_external_service_provider($service);

		// The user has already authenticated successfully, request to authenticate again
		$unique_id = $this->service_providers[$service_name]->perform_token_auth();

		// Insert into table, they will be able to log in after this
		$data = array(
			'user_id'			=> $link_data['user_id'],
			'provider'			=> strtolower($link_data['oauth_service']),
			'oauth_provider_id'	=> $unique_id,
		);

		$this->link_account_perform_link($data);
		// Update token storage to store the user_id
		$storage->set_user_id($link_data['user_id']);
	}

	/**
	* Performs the account linking for auth_link
	*
	* @param	array	$link_data		The same variable given to {@see \phpbb\auth\provider\provider_interface::link_account}
	* @param	string	$service_name	The name of the service being used in
	*									linking.
	* @return	string|null	Returns a language constant (string) if an error is
	*						encountered, or null on success.
	*/
	protected function link_account_auth_link(array $link_data, $service_name)
	{
		$storage = new \phpbb\auth\provider\oauth\token_storage($this->db, $this->user, $this->auth_provider_oauth_token_storage_table, $this->auth_provider_oauth_state_table);
		$query = 'i=ucp_auth_link&mode=auth_link&link=1&oauth_service=' . strtolower($link_data['oauth_service']);
		$service_credentials = $this->service_providers[$service_name]->get_service_credentials();
		$scopes = $this->service_providers[$service_name]->get_auth_scope();
		$service = $this->get_service(strtolower($link_data['oauth_service']), $storage, $service_credentials, $query, $scopes);

		if (($service::OAUTH_VERSION === 2 && $this->request->is_set('code', \phpbb\request\request_interface::GET))
			|| ($service::OAUTH_VERSION === 1 && $this->request->is_set('oauth_token', \phpbb\request\request_interface::GET)))
		{
			$this->service_providers[$service_name]->set_external_service_provider($service);
			$unique_id = $this->service_providers[$service_name]->perform_auth_login();

			// Insert into table, they will be able to log in after this
			$data = array(
				'user_id'			=> $this->user->data['user_id'],
				'provider'			=> strtolower($link_data['oauth_service']),
				'oauth_provider_id'	=> $unique_id,
			);

			$this->link_account_perform_link($data);
		}
		else
		{
			if ($service::OAUTH_VERSION === 1)
			{
				$token = $service->requestRequestToken();
				$url = $service->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
			}
			else
			{
				$url = $service->getAuthorizationUri();
			}
			header('Location: ' . $url);
		}
	}

	/**
	* Performs the query that inserts an account link
	*
	* @param	array	$data	This array is passed to db->sql_build_array
	*/
	protected function link_account_perform_link(array $data)
	{
		$sql = 'INSERT INTO ' . $this->auth_provider_oauth_token_account_assoc . '
			' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);

		/**
		 * Event is triggered after user links account.
		 *
		 * @event core.auth_oauth_link_after
		 * @var    array    data    User row
		 * @since 3.1.11-RC1
		 */
		$vars = array(
			'data',
		);
		extract($this->dispatcher->trigger_event('core.auth_oauth_link_after', compact($vars)));
	}

	/**
	* {@inheritdoc}
	*/
	public function logout($data, $new_session)
	{
		// Clear all tokens belonging to the user
		$storage = new \phpbb\auth\provider\oauth\token_storage($this->db, $this->user, $this->auth_provider_oauth_token_storage_table, $this->auth_provider_oauth_state_table);
		$storage->clearAllTokens();

		return;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_auth_link_data($user_id = 0)
	{
		$block_vars = array();

		// Get all external accounts tied to the current user
		$data = array(
			'user_id' => ($user_id <= 0) ? (int) $this->user->data['user_id'] : (int) $user_id,
		);
		$sql = 'SELECT oauth_provider_id, provider FROM ' . $this->auth_provider_oauth_token_account_assoc . '
			WHERE ' . $this->db->sql_build_array('SELECT', $data);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$oauth_user_ids = array();

		if ($rows !== false && count($rows))
		{
			foreach ($rows as $row)
			{
				$oauth_user_ids[$row['provider']] = $row['oauth_provider_id'];
			}
		}
		unset($rows);

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			// Only include data if the credentials are set
			$credentials = $service_provider->get_service_credentials();
			if ($credentials['key'] && $credentials['secret'])
			{
				$actual_name = str_replace('auth.provider.oauth.service.', '', $service_name);

				$block_vars[$service_name] = array(
					'HIDDEN_FIELDS'	=> array(
						'link'			=> (!isset($oauth_user_ids[$actual_name])),
						'oauth_service' => $actual_name,
					),

					'SERVICE_NAME'	=> $this->user->lang['AUTH_PROVIDER_OAUTH_SERVICE_' . strtoupper($actual_name)],
					'UNIQUE_ID'		=> (isset($oauth_user_ids[$actual_name])) ? $oauth_user_ids[$actual_name] : null,
				);
			}
		}

		return array(
			'BLOCK_VAR_NAME'	=> 'oauth',
			'BLOCK_VARS'		=> $block_vars,

			'TEMPLATE_FILE'	=> 'ucp_auth_link_oauth.html',
		);
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
		$sql = 'DELETE FROM ' . $this->auth_provider_oauth_token_account_assoc . "
			WHERE provider = '" . $this->db->sql_escape($link_data['oauth_service']) . "'
				AND user_id = " . (int) $user_id;
		$this->db->sql_query($sql);

		// Clear all tokens belonging to the user on this servce
		$service_name = 'auth.provider.oauth.service.' . strtolower($link_data['oauth_service']);
		$storage = new \phpbb\auth\provider\oauth\token_storage($this->db, $this->user, $this->auth_provider_oauth_token_storage_table, $this->auth_provider_oauth_state_table);
		$storage->clearToken($service_name);
	}
}
