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
* Google OAuth service
*/
class google extends base
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
	* @param	\phpbb\request\request_interface 	$request
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\request\request_interface $request)
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
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
		}

		// This was a callback request, get the token
		$this->service_provider->requestAccessToken($this->request->variable('code', ''));

		// Send a request with it
		$result = json_decode($this->service_provider->request('https://www.googleapis.com/oauth2/v1/userinfo'), true);

		// Return the unique identifier
		return $result['id'];
	}

	/**
	* {@inheritdoc}
	*/
	public function perform_token_auth()
	{
		if (!($this->service_provider instanceof \OAuth\OAuth2\Service\Google))
		{
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
		}

		// Send a request with it
		$result = json_decode($this->service_provider->request('https://www.googleapis.com/oauth2/v1/userinfo'), true);

		// Return the unique identifier
		return $result['id'];
	}
}
