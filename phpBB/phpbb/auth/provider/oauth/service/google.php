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
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Google OAuth service.
 */
class google extends base
{
	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config $config
	 */
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_auth_scope()
	{
		return ['openid', 'email', 'profile'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_service_credentials()
	{
		return [
			'key' => $this->config['auth_oauth_google_key'],
			'secret' => $this->config['auth_oauth_google_secret'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_provider(string $redirect_uri): AbstractProvider
	{
		$credentials = $this->get_service_credentials();

		return new GenericProvider([
			'clientId' => $credentials['key'],
			'clientSecret' => $credentials['secret'],
			'redirectUri' => $redirect_uri,
			'urlAuthorize' => 'https://accounts.google.com/o/oauth2/v2/auth',
			'urlAccessToken' => 'https://oauth2.googleapis.com/token',
			'urlResourceOwnerDetails' => 'https://openidconnect.googleapis.com/v1/userinfo',
			'scopes' => $this->get_auth_scope(),
			'scopeSeparator' => ' ',
			'responseResourceOwnerId' => 'sub',
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_user_id(AbstractProvider $provider, AccessTokenInterface $token): string
	{
		$id = $this->get_resource_owner($provider, $token)->getId();

		if (!is_string($id) && !is_int($id))
		{
			throw new exception('AUTH_PROVIDER_OAUTH_RETURN_ERROR');
		}

		return (string) $id;
	}
}
