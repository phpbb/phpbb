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
 * OAuth service interface
 */
interface service_interface
{
	/**
	 * Returns an array of the scopes necessary for auth
	 *
	 * @return array	An array of the required scopes
	 */
	public function get_auth_scope();

	/**
	 * Returns an array containing the service credentials belonging to requested
	 * service.
	 *
	 * @return array	An array containing the 'key' and the 'secret' of the
	 *					service in the form:
	 *						array(
	 *							'key'		=> string
	 *							'secret'	=> string
	 *						)
	 */
	public function get_service_credentials();

	/**
	 * Returns the results of the authentication in json format
	 *
	 * @throws \phpbb\auth\provider\oauth\service\exception
	 * @return string	The unique identifier returned by the service provider
	 *					that is used to authenticate the user with phpBB.
	 */
	public function perform_auth_login();

	/**
	 * Returns the results of the authentication in json format
	 * Use this function when the user already has an access token
	 *
	 * @throws \phpbb\auth\provider\oauth\service\exception
	 * @return string	The unique identifier returned by the service provider
	 *					that is used to authenticate the user with phpBB.
	 */
	public function perform_token_auth();

	/**
	 * Returns the class of external library service provider that has to be used.
	 *
	 * @return string	If the string is a class, it will register the provided string as a class,
	 *						which later will be generated as the OAuth external service provider.
	 * 					If the string is not a class, it will use this string,
	 * 						trying to generate a service for the version 2 and 1 respectively:
	 * 						\OAuth\OAuth2\Service\<string>
	 * 					If the string is empty, it will default to OAuth's standard service classes,
	 * 						trying to generate a service for the version 2 and 1 respectively:
	 * 						\OAuth\OAuth2\Service\Facebook
	 */
	public function get_external_service_class();

	/**
	 * Returns the external library service provider once it has been set
	 */
	public function get_external_service_provider();

	/**
	 * Sets the external library service provider
	 *
	 * @param \OAuth\Common\Service\ServiceInterface	$service_provider
	 */
	public function set_external_service_provider(\OAuth\Common\Service\ServiceInterface $service_provider);
}
