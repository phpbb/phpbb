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
* OAuth service interface
*
* @package auth
*/
interface phpbb_auth_provider_oauth_service_interface
{
	/**
	* Returns an array of the scopes necessary for auth
	*
	* @return	array	An array of the required scopes
	*/
	public function get_auth_scope();

	/**
	* Returns the external library service provider once it has been set
	*
	* @param	\OAuth\Common\Service\ServiceInterface|null
	*/
	public function get_external_service_provider();

	/**
	* Returns an array containing the service credentials belonging to requested
	* service.
	*
	* @return	array	An array containing the 'key' and the 'secret' of the
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
	* @return	string	The unique identifier returned by the service provider
	*					that is used to authenticate the user with phpBB.
	*/
	public function perform_auth_login();

	/**
	* Sets the external library service provider
	*
	* @param	\OAuth\Common\Service\ServiceInterface	$service
	*/
	public function set_external_service_provider(\OAuth\Common\Service\ServiceInterface $service_provider);
}
