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
* Interface phpbb_session_storage_interface
*/
interface phpbb_session_storage_interface
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
}
