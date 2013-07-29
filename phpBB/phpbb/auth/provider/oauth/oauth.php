<?php
/**
*
* @package auth
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

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\Uri;

/**
* OAuth authentication provider for phpBB3
*
* @package auth
*/
class phpbb_auth_provider_oauth extends phpbb_auth_provider_base
{
	/**
	* Database driver
	*
	* @var phpbb_db_driver
	*/
	protected $db;

	/**
	* phpBB config
	*
	* @var phpbb_config
	*/
	protected $config;

	/**
	* phpBB request object
	*
	* @var phpbb_request
	*/
	protected $request;

	/**
	* phpBB user
	*
	* @var phpbb_user
	*/
	protected $user;

	/**
	* OAuth token table
	*
	* @var string
	*/
	protected $auth_provider_oauth_token_storage_table;

	/**
	* OAuth account association table
	*
	* @var string
	*/
	protected $auth_provider_oauth_token_account_assoc;

	/**
	* All OAuth service providers
	*
	* @var phpbb_di_service_collection Contains phpbb_auth_provider_oauth_service_interface
	*/
	protected $service_providers;

	/**
	* Cached current uri object
	*
	* @var \OAuth\Common\Http\Uri\UriInterface|null
	*/
	protected $current_uri;

	/**
	* OAuth Authentication Constructor
	*
	* @param	phpbb_db_driver $db
	* @param	phpbb_config 	$config
	* @param	phpbb_request 	$request
	* @param	phpbb_user 		$user
	* @param	string			$auth_provider_oauth_token_storage_table
	* @param	string			$auth_provider_oauth_token_account_assoc
	* @param	phpbb_di_service_collection	$service_providers Contains phpbb_auth_provider_oauth_service_interface
	*/
	public function __construct(phpbb_db_driver $db, phpbb_config $config, phpbb_request $request, phpbb_user $user, $auth_provider_oauth_token_storage_table, $auth_provider_oauth_token_account_assoc, phpbb_di_service_collection $service_providers)
	{
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->auth_provider_oauth_token_storage_table = $auth_provider_oauth_token_storage_table;
		$this->auth_provider_oauth_token_account_assoc = $auth_provider_oauth_token_account_assoc;
		$this->service_providers = $service_providers;
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
			// TODO: Remove before merging
			global $phpbb_root_path, $phpEx;
			$provider = new phpbb_auth_provider_db($this->db, $this->config, $this->request, $this->user, $phpbb_root_path, $phpEx);
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

		$storage = new phpbb_auth_provider_oauth_token_storage($this->db, $this->user, $service_name, $this->auth_provider_oauth_token_storage_table);
		$service = $this->get_service($service_name_original, $storage, $service_credentials, $this->service_providers[$service_name]->get_auth_scope());

		if ($this->request->is_set('code', phpbb_request_interface::GET))
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

			if (!$row)
			{
				// The user does not yet exist, ask to link or create profile
				return array(
					'status'		=> LOGIN_SUCCESS_LINK_PROFILE,
					'error_msg'		=> 'LOGIN_OAUTH_ACCOUNT_NOT_LINKED',
					'user_row'		=> array(),
					'redirect_data'	=> array(
						'auth_provider'	=> 'oauth',
						'oauth_service'	=> $service_name_original,
					),
				);
			}

			// Retrieve the user's account
			$sql = 'SELECT user_id, username, user_password, user_passchg, user_pass_convert, user_email, user_type, user_login_attempts
			FROM ' . USERS_TABLE . "
			WHERE user_id = '" . $this->db->sql_escape($row['user_id']) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				// TODO: Update exception type and change it to language constant
				throw new Exception('Invalid entry in ' . $this->auth_provider_oauth_token_account_assoc);
			}

			// Update token storage to store the user_id
			$storage->set_user_id($row['user_id']);

			// The user is now authenticated and can be logged in
			return array(
				'status'		=> LOGIN_SUCCESS,
				'error_msg'		=> false,
				'user_row'		=> $row,
			);
		} else {
			$url = $service->getAuthorizationUri();
			// TODO: modify $url for the appropriate return points
			header('Location: ' . $url);
		}
	}

	/**
	* Returns the cached current_uri object or creates and caches it if it is
	* not already created
	*
	* @param	string	$service_name			The name of the service
	* @return	\OAuth\Common\Http\Uri\UriInterface
	*/
	protected function get_current_uri($service_name)
	{
		if ($this->current_uri)
		{
			return $this->current_uri;
		}

		$uri_factory = new \OAuth\Common\Http\Uri\UriFactory();
		$current_uri = $uri_factory->createFromSuperGlobalArray($this->request->get_super_global(phpbb_request_interface::SERVER));
		$current_uri->setQuery('mode=login&login=external&oauth_service=' . $service_name);

		$this->current_uri = $current_uri;
		return $current_uri;
	}

	/**
	* Returns the cached service object or creates a new one
	*
	* @param	string	$service_name			The name of the service
	* @param	phpbb_auth_oauth_token_storage $storage
	* @param	array	$service_credentials	{@see phpbb_auth_provider_oauth::get_service_credentials}
	* @param	array	$scope					The scope of the request against
	*											the api.
	* @return	\OAuth\Common\Service\ServiceInterface
	*/
	protected function get_service($service_name, phpbb_auth_provider_oauth_token_storage $storage, array $service_credentials, array $scopes = array())
	{
		$current_uri = $this->get_current_uri($service_name);

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
			// Update to an actual error message
			throw new Exception('Service not created: ' . $service_name);
		}

		return $service;
	}

	/**
	* Returns an array of login data for all enabled OAuth services.
	*
	* @return	array
	*/
	public function get_login_data()
	{
		$login_data = array();

		foreach ($this->service_providers as $service_name => $service_provider)
		{
			// Only include data if the credentials are set
			$credentials = $service_provider->get_service_credentials();
			if ($credentials['key'] && $credentials['secret'])
			{
				$actual_name = str_replace('auth.provider.oauth.service.', '', $service_name);
				$redirect_url = build_url(false) . '&login=external&oauth_service=' . $actual_name;
				$login_data[$service_name] = array(
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

		if (!array_key_exists('oauth_service', $login_link_data) || !$login_link_data['oauth_service'])
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
		// We must have an oauth_service listed, check for it two ways
		if (!array_key_exists('oauth_service', $link_data) || !$link_data['oauth_service'])
		{
			if (!$link_data['oauth_service'] && $this->request->is_set('oauth_service'))
			{
				$link_data['oauth_service'] = $this->request->variable('oauth_service', '');
			}

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

		$storage = new phpbb_auth_provider_oauth_token_storage($this->db, $this->user, $service_name, $this->auth_provider_oauth_token_storage_table);

		// Check for an access token, they should have one
		if (!$storage->has_access_token_by_sesion())
		{
			return 'LOGIN_LINK_ERROR_OAUTH_NO_ACCESS_TOKEN';
		}

		$token = $storage->retrieve_access_token_by_session();
	}
}
