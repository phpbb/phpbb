<?php
/**
*
* @package auth
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\auth\provider\oauth\service;

/**
* Bitly OAuth service
*
* @package auth
*/
class bitly extends \phpbb\auth\provider\oauth\service\base
{
	/**
	* phpBB config
	*
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* phpBB request
	*
	* @var \phpbb\request\request_interface
	*/
	protected $request;

	/**
	* Constructor
	*
	* @param	\phpbb\config\config				$config
	* @param	\phpbb\request\request_interface	$request
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\request\request_interface $request)
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
			throw new \phpbb\auth\provider\oauth\service\exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
		}

		// This was a callback request from bitly, get the token
		$this->service_provider->requestAccessToken($this->request->variable('code', ''));

		// Send a request with it
		$result = json_decode($this->service_provider->request('user/info'), true);

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
			throw new \phpbb\auth\provider\oauth\service\exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
		}

		// Send a request with it
		$result = json_decode($this->service_provider->request('user/info'), true);

		// Return the unique identifier returned from bitly
		return $result['data']['login'];
	}
}
