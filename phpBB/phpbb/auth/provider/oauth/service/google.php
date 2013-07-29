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

/**
* Google OAuth service
*
* @package auth
*/
class phpbb_auth_provider_oauth_service_google extends phpbb_auth_provider_oauth_service_base
{
	/**
	* phpBB config
	*
	* @var phpbb_config
	*/
	protected $config;

	/**
	* phpBB request
	*
	* @var phpbb_request
	*/
	protected $request;

	/**
	* Constructor
	*
	* @param	phpbb_config 	$config
	* @param	phpbb_request 	$request
	*/
	public function __construct(phpbb_config $config, phpbb_request $request)
	{
		$this->config = $config;
		$this->request = $request;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_auth_scope()
	{
		return array(
			'userinfo_email',
			'userinfo_profile',
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_service_credentials()
	{
		return array(
			'key'		=> $this->config['auth_oauth_google_key'],
			'secret'	=> $this->config['auth_oauth_google_secret'],
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function perform_auth_login()
	{
		if (!($this->service_provider instanceof \OAuth\OAuth2\Service\Google))
		{
			// TODO: make exception class and use language constant
			throw new Exception('Invalid service provider type');
		}

		// This was a callback request, get the token
		$this->service_provider->requestAccessToken( $this->request->variable('code', '') );

		// Send a request with it
		$result = json_decode( $this->service_provider->request('https://www.googleapis.com/oauth2/v1/userinfo'), true );

		// Return the unique identifier returned from bitly
		return $result['id'];
	}

	/**
	* {@inheritdoc}
	*/
	public function perform_auth_link()
	{
		if (!($this->service_provider instanceof \OAuth\OAuth2\Service\Google))
		{
			// TODO: make exception class and use language constant
			throw new Exception('Invalid service provider type');
		}

		// Send a request with it
		$result = json_decode( $this->service_provider->request('https://www.googleapis.com/oauth2/v1/userinfo'), true );

		// Return the unique identifier returned from bitly
		return $result['id'];
	}
}
