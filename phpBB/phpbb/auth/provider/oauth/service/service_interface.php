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

/**
 * OAuth service interface.
 */
interface service_interface
{
	/**
	 * Returns the scopes required for authentication.
	 *
	 * @return array
	 */
	public function get_auth_scope();

	/**
	 * Returns the service credentials.
	 *
	 * @return array
	 */
	public function get_service_credentials();

	/**
	 * Return a provider configured for the supplied callback URI.
	 *
	 * @param string $redirect_uri
	 * @return AbstractProvider
	 */
	public function get_provider(string $redirect_uri): AbstractProvider;

	/**
	 * Return the immutable provider identifier for an access token.
	 *
	 * @param AbstractProvider $provider
	 * @param AccessTokenInterface $token
	 * @return string
	 * @throws exception
	 */
	public function get_user_id(AbstractProvider $provider, AccessTokenInterface $token): string;
}
