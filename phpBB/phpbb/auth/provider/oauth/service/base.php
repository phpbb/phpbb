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

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Base OAuth abstract class that all OAuth services should implement.
 */
abstract class base implements service_interface
{
	/**
	 * {@inheritdoc}
	 */
	public function get_auth_scope()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_provider(string $redirect_uri): AbstractProvider
	{
		return new GenericProvider($this->get_provider_options($redirect_uri));
	}

	/**
	 * Return common GenericProvider options for a service.
	 *
	 * @param string $redirect_uri
	 * @return array
	 */
	protected function get_provider_options(string $redirect_uri): array
	{
		$credentials = $this->get_service_credentials();

		return [
			'clientId' => $credentials['key'],
			'clientSecret' => $credentials['secret'],
			'redirectUri' => $redirect_uri,
		];
	}

	/**
	 * Fetch the resource owner and translate provider failures to phpBB errors.
	 *
	 * @param AbstractProvider $provider
	 * @param AccessTokenInterface $token
	 * @return ResourceOwnerInterface
	 * @throws exception
	 */
	protected function get_resource_owner(AbstractProvider $provider, AccessTokenInterface $token): ResourceOwnerInterface
	{
		if (!($token instanceof AccessToken))
		{
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
		}

		try
		{
			return $provider->getResourceOwner($token);
		}
		catch (\Throwable $e)
		{
			throw new exception('AUTH_PROVIDER_OAUTH_ERROR_REQUEST', 0, $e);
		}
	}
}
