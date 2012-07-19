<?php
/**
*
* @package auth
* @copyright (c) 2012 phpBB Group
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
* This interface defines functions for single sign on providers
*
* @package auth
*/
interface phpbb_auth_provider_sso_interface {
	/**
	 * The session validation function checks whether the user is still logged in
	 *
	 * @return boolean true if the given user is authenticated or false if the session should be closed
	 */
	public function validate_session(&$user);
}
