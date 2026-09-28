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
use League\OAuth2\Client\Token\AccessTokenInterface;
use phpbb\auth\provider\oauth\provider\bitly as bitly_provider;

/**
 * Bitly OAuth service.
 */
class bitly extends base
{
	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config $config Config object.
	 */
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_service_credentials()
	{
		return [
			'key' => $this->config['auth_oauth_bitly_key'],
			'secret' => $this->config['auth_oauth_bitly_secret'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_provider(string $redirect_uri): AbstractProvider
	{
		$credentials = $this->get_service_credentials();

		return new bitly_provider([
			'clientId' => $credentials['key'],
			'clientSecret' => $credentials['secret'],
			'redirectUri' => $redirect_uri,
			'urlAuthorize' => 'https://bitly.com/oauth/authorize',
			'urlAccessToken' => 'https://api-ssl.bitly.com/oauth/access_token',
			'urlResourceOwnerDetails' => 'https://api-ssl.bitly.com/v4/user',
			'scopes' => [],
			'responseResourceOwnerId' => 'login',
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_user_id(AbstractProvider $provider, AccessTokenInterface $token): string
	{
		$login = $this->get_resource_owner($provider, $token)->getId();

		if (!is_string($login) || $login === '')
		{
			throw new exception('AUTH_PROVIDER_OAUTH_RETURN_ERROR');
		}

		return $login;
	}
}
