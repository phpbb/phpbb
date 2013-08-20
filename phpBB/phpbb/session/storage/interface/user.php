<?php
/**
*
* @package phpBB3
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
* Interface phpbb_session_storage_interface_user
*/
interface phpbb_session_storage_interface_user
{
	/** Get user information from user_id
	*
	* @param int $user_id
	* @param bool $normal_founder_only Only gather if
	*        user is a USER_NORMAL or USER_FOUNDER
	* @return mixed
	*/
	function get_user_info($user_id, $normal_founder_only = false);

	/** Set last active session time and page for sessions with user_id
	 *
	 * @param int $time time to set
	 * @param int $user_id user_id for sessions to change
	 * @param string $page page to update as well
	 * 		  (false or '' to not update) (default: '')
	 * @return null
	 */
	function update_last_visit($time, $user_id, $page='');

	/** Update form salt for user_id
	 *
	 * @param string $salt
	 * @param int $user_id
	 * @return null
	 */
	function update_form_salt($salt, $user_id);

	/**
	 * Map over all friends of user with user_id
	 *
	 * @param          $user_id		user_id of who we should find friends to map over
	 * @param callable $function	function to map with
	 * 									(function should take $user param containing friend of user)
	 *
	 * @return array	Array containing results of the function
	 */
	function map_friends_online($user_id, Closure $function);
}
