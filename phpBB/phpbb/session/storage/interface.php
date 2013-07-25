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
 * Class phpbb_session_storage
 */
interface phpbb_session_storage
{
	/**
	 * @param Int $time_now - Time to use in queries
	 */
	function set_time_now($time_now);

	/**
	 * @param phpbb_db_driver $db Driver to use in queries
	 */
	function set_db(phpbb_db_driver $db);

	/** Create a session in storage using data
	 * @param Array $session_data Data to insert in storage
	 */
	function create($session_data);

	/** Replace a session from session_id with session_data
	 * @param Int $session_id
	 * @param Array $session_data Data to replace session_id with
	 */
	function update($session_id, $session_data);

	/** Get session data associated with session_id
	 * @param Int $session_id
	 * @return Array
	 */
	function get($session_id);

	/** Get session & user data associated with user_id
	 * @param $user_id
	 * @return Array
	 */
	function get_with_user_id($user_id);

	/** Get user information from user_id
	 * @param int $user_id
	 * @param bool $normal_founder_only Only gather if
	 *        user is a USER_NORMAL or USER_FOUNDER
	 * @return mixed
	 */
	function get_user_info($user_id, $normal_founder_only=false);

	/** Get user information with user_id and session_key
	 * @param int $user_id
	 * @param string $session_key
	 * @return Array user information
	 */
	function get_user_info_with_key($user_id, $session_key);

	/** Get sessions associated with user_id sorted by newest
	 * @param int $user_id
	 * @return Array session data
	 */
	function get_newest($user_id);

	/** Delete all session associated with user_id
	 * @param $user_id
	 */
	function delete_by_user_id($user_id);

	/** Delete session from session_id and optionally user_id
	 * @param int $session_id
	 * @param bool $user_id
	 */
	function delete($session_id, $user_id = false);

	/** Count number of active sessions on board
	 * @param int $minutes_considered_active
	 * @return Int Count of number of active sessions that
	 *         where active within the last $minutes_considered_active
	 *		   (default: 60)
	 */
	function num_active_sessions($minutes_considered_active);

	/** Return count of sessions associated with user_id within max_time
	 * @param $user_id
	 * @param $max_time
	 * @return int number of sessions for user_id within max_time
	 */
	function num_sessions($user_id, $max_time);

	/** Remove admin status on session associated with session_id
	 * @param String $session_id session_id to remove
	 */
	function unset_admin($session_id);

	/** Set user session visability
	 * @param int $user_id sessions with user_id to change
	 * @param bool $viewonline true: set visible, false: set invisible
	 */
	function set_viewonline($user_id, $viewonline);

	/** Set last active session time and page for sessions with user_id
	 * @param int $time time to set
	 * @param int $user_id user_id for sessions to change
	 * @param string $page page to update as well
	 * 		  (false or '' to not update) (default: '')
	 */
	function update_last_visit($time, $user_id, $page='');

	/** Update form salt for user_id
	 * @param string $salt
	 * @param int $user_id
	 */
	function update_form_salt($salt, $user_id);

	/** Remove session_key for user_id
	 * @param $user_id
	 * @param string|bool $key if given, only remove this key
	 */
	function remove_session_key($user_id, $key=false);

	/** Change session_key for user_id
	 * @param $user_id user to change
	 * @param $key_id key to change
	 * @param $data new data
	 */
	function update_session_key($user_id, $key_id, $data);

	/** Insert session key information into storage
	 * @param array $data session key data to store
	 */
	function insert_session_key($data);

	/** Remove from storage all guest sessions older than session_length
	 * @param int $session_length (in seconds) remove sessions older than time - session_length
	 */
	function cleanup_guest_sessions($session_length);

	/** Remove from storage all sessions older than session_length
	 *
	 * If $user_ids is empty, nothing happens.
	 *
	 * @param array $user_ids
	 * @param int $session_length (in seconds) remove sessions older than time - session_legnth
	 */
	function cleanup_expired_sessions(array $user_ids, $session_length);

	/** For sessions older than length, run a function and collect results.
	 * @param int $session_length how old to search
	 * @param Callable $session_function function to run takes $row + $storage, outputs array
	 * @param int $batch_size Sql Paging size
	 * @return Array an array containing the results of $session_function
	 */
	function map_recently_expired($session_length, $session_function, $batch_size);

	/** Delete sessions longer than max_autologin_time
	 * @param int $max_autologin_time (in seconds)
	 */
	function cleanup_long_sessions($max_autologin_time);

	/** Cleanup login attempts older than ip_login_limit_time
	 * @param int $ip_login_limit_time (in seconds) delete attempts older than time - this
	 */
	function cleanup_attempt_table($ip_login_limit_time);

	/**
	 * @param string|false $user_email
	 * @param string|false $user_ips
	 * @param int|false $user_id
	 * @param int $cache_ttl How long to keep a cached banlist
	 * @return Array - List of banned users
	 */
	function banlist($user_email, $user_ips, $user_id, $cache_ttl);
}
