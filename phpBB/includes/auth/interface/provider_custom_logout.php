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
* This interface defines the functions for a custom logout function called during phpbb_session::kill_session()
*
* @package auth
*/
interface phpbb_auth_interface_provider_custom_logout
{

	/**
	 * Handles a non-standard logout of a user from phpBB.
	 *
	 * @param $data $this->data from phpbb_session
	 * @param $new_session The new session
	 */
	public function logout($data, $new_session);
}
