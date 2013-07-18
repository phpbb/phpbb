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
	* Cached services once they has been created
	*
	* @var array Contains \OAuth\Common\Service\ServiceInterface or null
	*/
	protected $services;

	/**
	* All OAuth service providers
	*
	* @var array Contains phpbb_auth_provider_oauth_service_interface
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
	* @param	phpbb_auth_provider_oauth_service_interface	$service_providers
	*/
	public function __construct(phpbb_db_driver $db, phpbb_config $config, phpbb_request $request, phpbb_user $user, $auth_provider_oauth_token_storage_table, $auth_provider_oauth_token_account_assoc, phpbb_auth_provider_oauth_service_interface $service_providers)
	{
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->auth_provider_oauth_token_storage_table = $auth_provider_oauth_token_storage_table;
		$this->auth_provider_oauth_token_account_assoc = $auth_provider_oauth_token_account_assoc;
		$this->service_providers = $service_providers;
		$this->services = array();
	}

	/**
	* {@inheritdoc}
	*/
	public function login($username, $password)
	{
		// Requst the name of the OAuth service
		$service_name = $this->request->variable('oauth_service', '', false, phpbb_request_interface::POST);
		$service_name = strtolower($service_name);
		if ($service_name === '' && isset($this->services[$service_name]))
		{
			return array(
				'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
				// TODO: change error message
				'error_msg'		=> 'LOGIN_ERROR_EXTERNAL_AUTH_APACHE',
				'user_row'		=> array('user_id' => ANONYMOUS),
			);
		}

		// Get the service credentials for the given service
		$service_credentials = $this->services[$service_name]->get_credentials();

		$storage = new phpbb_auth_provider_oauth_token_storage($this->db, $this->user, $service_name, $this->auth_provider_oauth_token_storage_table);
		$service = $this->get_service($service_name, $storage, $service_credentials, $this->services[$service_name]->get_auth_scope());

		if ($this->request->is_set('code', phpbb_request_interface::GET))
		{
			$this->services[$service_name]->set_external_service_provider($service);
			$unique_id = $this->services[$service_name]->perform_auth_login();

			// Check to see if this provider is already assosciated with an account
			$data = array(
				'oauth_provider'	=> $service_name,
				'oauth_provider_id'	=> $unique_id
			);
			$sql = 'SELECT user_id FROM' . $this->auth_provider_oauth_token_account_assoc . '
				WHERE ' . $this->db->sql_build_array('SELECT', $data);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				// Account not tied to any existing account
				// TODO: determine action that should occur
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
	* @return	\OAuth\Common\Http\Uri\UriInterface
	*/
	protected function get_current_uri()
	{
		if ($this->current_uri)
		{
			return $this->current_uri;
		}

		$uri_factory = new \OAuth\Common\Http\Uri\UriFactory();
		$current_uri = $uri_factory->createFromSuperGlobalArray($this->request->get_super_global(phpbb_request_interface::SERVER));
		$current_uri->setQuery('');

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
	protected function get_service($service_name, phpbb_auth_oauth_token_storage $storage, array $service_credentials, array $scopes = array())
	{
		if ($this->services[$service_name])
		{
			return $this->services[$service_name];
		}

		$current_uri = $this->get_current_uri();

		// Setup the credentials for the requests
		$credentials = new Credentials(
			$service_credentials['key'],
			$service_credentials['secret'],
			$current_uri->getAbsoluteUri()
		);

		$service_factory = new \OAuth\ServiceFactory();
		$this->service[$service_name] = $service_factory->createService($service_name, $credentials, $storage, $scopes);

		return $this->service[$service_name];
	}
}
