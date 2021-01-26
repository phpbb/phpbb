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
 * Bitly OAuth service
 */
class bitly extends base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config		Config object
	 * @param \phpbb\request\request_interface	$request	Request object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\request\request_interface $request)
	{
		$this->config	= $config;
		$this->request	= $request;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_service_credentials()
	{
		return [
			'key'		=> $this->config['auth_oauth_bitly_key'],
			'secret'	=> $this->config['auth_oauth_bitly_secret'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function perform_auth_login()
	{
		if (!($this->service_provider instanceof \OAuth\OAuth2\Service\Bitly))
		{
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
		}

		try
		{
			// This was a callback request, get the token
			$this->service_provider->requestAccessToken($this->request->variable('code', ''));
		}
		catch (\OAuth\Common\Http\Exception\TokenResponseException $e)
		{
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
		}

		try
		{
			// Send a request with it
			$result = (array) json_decode($this->service_provider->request('user/info'), true);
		}
		catch (\OAuth\Common\Exception\Exception $e)
		{
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
		}

		// Prevent SQL error
		if (!isset($result['data']['login']))
		{
			throw new exception('AUTH_PROVIDER_OAUTH_RETURN_ERROR');
		}

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
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_INVALID_SERVICE_TYPE');
		}

		try
		{
			// Send a request with it
			$result = (array) json_decode($this->service_provider->request('user/info'), true);
		}
		catch (\OAuth\Common\Exception\Exception $e)
		{
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
		}

		// Prevent SQL error
		if (!isset($result['data']['login']))
		{
			throw new exception('AUTH_PROVIDER_OAUTH_RETURN_ERROR');
		}

		// Return the unique identifier
		return $result['data']['login'];
	}
}
