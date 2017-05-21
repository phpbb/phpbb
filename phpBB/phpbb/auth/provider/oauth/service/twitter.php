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

namespace phpbb\auth\provider\oauth\service;

/**
* Twitter OAuth service
*/
class twitter extends \phpbb\auth\provider\oauth\service\base
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
			'key'		=> $this->config['auth_oauth_twitter_key'],
			'secret'	=> $this->config['auth_oauth_twitter_secret'],
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function perform_auth_login()
	{
		if (!($this->service_provider instanceof \OAuth\OAuth1\Service\Twitter))
		{
			throw new \phpbb\auth\provider\oauth\service\exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
		}

		$storage = $this->service_provider->getStorage();
		$token = $storage->retrieveAccessToken('Twitter');
		$tokensecret = $token->getRequestTokenSecret();

		// This was a callback request from twitter, get the token
		$this->service_provider->requestAccessToken(
			$this->request->variable('oauth_token', ''),
			$this->request->variable('oauth_verifier', ''),
			$tokensecret
		);

		// Send a request with it
		$result = json_decode($this->service_provider->request('account/verify_credentials.json'), true);

		// Return the unique identifier returned from twitter
		return $result['id'];
	}

	/**
	* {@inheritdoc}
	*/
	public function perform_token_auth()
	{
		if (!($this->service_provider instanceof \OAuth\OAuth1\Service\Twitter))
		{
			throw new \phpbb\auth\provider\oauth\service\exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
		}

		// Send a request with it
		$result = json_decode($this->service_provider->request('account/verify_credentials.json'), true);

		// Return the unique identifier returned from twitter
		return $result['id'];
	}
}
