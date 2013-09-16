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
interface service_interface
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
	* @throws	\phpbb\auth\provider\oauth\service\exception
	* @return	string	The unique identifier returned by the service provider
	*					that is used to authenticate the user with phpBB.
	*/
	public function perform_auth_login();

	/**
	* Returns the results of the authentication in json format
	* Use this function when the user already has an access token
	*
	* @throws	\phpbb\auth\provider\oauth\service\exception
	* @return	string	The unique identifier returned by the service provider
	*					that is used to authenticate the user with phpBB.
	*/
	public function perform_token_auth();

	/**
	* Sets the external library service provider
	*
	* @param	\OAuth\Common\Service\ServiceInterface	$service
	*/
	public function set_external_service_provider(\OAuth\Common\Service\ServiceInterface $service_provider);
}
