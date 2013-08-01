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
* Interface phpbb_session_storage_interface_session
*/
interface phpbb_session_storage_interface_session
{
	/** Update the time used in session storage
	*
	* @param int $time_now - Time to use in queries
	* @return null
	*/
	function set_time_now($time_now);

	/** Create a session in storage using data
	*
	* @param array $session_data Data to insert in storage
	* @return null
	*/
	function create($session_data);

	/** Replace a session from session_id with session_data
	*
	* @param int $session_id
	* @param array $session_data Data to replace session_id with
	* @return null
	*/
	function update($session_id, $session_data);

	/** Get session data associated with session_id
	*
	* @param int $session_id
	* @return array
	*/
	function get($session_id);

	/** Delete session from session_id and optionally user_id
	*
	* @param int $session_id
	* @param bool $user_id
	*/
	function delete($session_id, $user_id = false);

	// Functions below involving user_ids

	/** Count number of active sessions on board
	*
	* @param int $minutes_considered_active
	* @return int Count of number of active sessions that
	*         where active within the last $minutes_considered_active
	*		   (default: 60)
	*/
	function num_active_sessions($minutes_considered_active);

	/** Remove admin status on session associated with session_id
	*
	* @param String $session_id session_id to remove
	*/
	function unset_admin($session_id);


	/** Get sessions associated with user_id sorted by newest
	 *
	 * @param int $user_id
	 * @return array session data
	 */
	function get_newest($user_id);

	/** Delete all session associated with user_id
	 *
	 * @param int $user_id
	 */
	function delete_by_user_id($user_id);

	/** Return count of sessions associated with user_id within max_time
	 *
	 * @param int $user_id
	 * @param int $max_time
	 * @return int number of sessions for user_id within max_time
	 */
	function num_sessions($user_id, $max_time);

	/** Get session & user data associated with user_id
	 *
	 * @param int $user_id
	 * @return array
	 */
	function get_with_user_id($user_id);
}
