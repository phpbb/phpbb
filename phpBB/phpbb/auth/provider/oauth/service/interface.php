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
}
