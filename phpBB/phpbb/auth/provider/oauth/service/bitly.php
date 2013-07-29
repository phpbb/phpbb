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
* Bitly OAuth service
*
* @package auth
*/
class phpbb_auth_provider_oauth_service_bitly extends phpbb_auth_provider_oauth_service_base
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
	public function get_service_credentials()
	{
		return array(
			'key'		=> $this->config['auth_oauth_bitly_key'],
			'secret'	=> $this->config['auth_oauth_bitly_secret'],
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function perform_auth_login()
	{
		if (!($this->service_provider instanceof \OAuth\OAuth2\Service\Bitly))
		{
			// TODO: make exception class and use language constant
			throw new Exception('Invalid service provider type');
		}

		// This was a callback request from bitly, get the token
		$this->service_provider->requestAccessToken( $this->request->variable('code', '') );

		// Send a request with it
		$result = json_decode( $this->service_provider->request('user/info'), true );

		// Return the unique identifier returned from bitly
		return $result['data']['login'];
	}

	/**
	* {@inheritdoc}
	*/
	public function perform_token_auth()
	{
		if (!($this->service_provider instanceof \OAuth\OAuth2\Service\Bitly))
		{
			// TODO: make exception class and use language constant
			throw new Exception('Invalid service provider type');
		}

		// Send a request with it
		$result = json_decode( $this->service_provider->request('user/info'), true );

		// Return the unique identifier returned from bitly
		return $result['data']['login'];
	}
}
