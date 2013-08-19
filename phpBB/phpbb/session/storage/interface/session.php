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

	/**
	* Delete session data
	*
	* @param bool|string   $session_id optional if given, id to delete
	* @param array|string 	$user_id    optional if given, only delete
	* 											this or these user's sessions
	*
	* @throws InvalidArgumentException Thrown if neither args are given
	* @return bool True if rows were deleted
	*/
	function delete($session_id = false, $user_id = false);

	/**
	* Completely delete all session data being used for phpbb
	*/
	function delete_all_sessions();

	/**
	* Get ip address from session_id
	*
	* @param $session_id
	*
	* @return null|string -- Either the ip address or null if none found
	*/
	public function get_user_ip_from_session($session_id);

	/**
	* Get newest user and session data for this $user_id
	*
	* @param $user_id -- user id to get user and session data for
	*
	* @return user and session data as an array
	*/
	public function get_newest_session($user_id);

	/**
	 * Get the longest session, and visibility for $user_id
	 *
	 * @param int 	$user_id	User id
	 *
	 * @return array Array containing user_id, online_time, viewonline
	 */
	function get_user_online_time($user_id);

	// Functions below involving user_ids

	/** Count number of active sessions on board
	*
	* @param int $minutes_considered_active
	* @return int Count of number of active sessions that
	*         where active within the last $minutes_considered_active
	*		   (default: 60)
	*/
	function num_active_sessions($minutes_considered_active);

	/**
	* Queries the session table to get information about online users
	*
	* @param int $item_id Limits the search to the item with this id
	* @param string $item The name of the item which is stored in the session table as session_{$item}_id
	*
	* @return array An array containing the ids of online, hidden and visible users, as well as statistical info
	*/
	function obtain_users_online($item_id = 0, $item = 'forum');

	/**
	* Queries the session table to get information about online guests
	*
	* @param int $item_id Limits the search to the item with this id
	* @param string $item The name of the item which is stored in the session table as session_{$item}_id
	*
	* @return int The number of active distinct guest sessions
	*/
	function obtain_guest_count($item_id = 0, $item = 'forum');

	/**
	* Get a list of all users active after online_time.
	*
	* @param $show_guests			Include anonymous users
	* @param $online_time			Include sessions active in a time greater than this
	* @param $order_by				order_by sql
	* @param $phpbb_dispatcher
	*
	* @return array				List of all rows containing users that matched
	*/
	function get_users_online($show_guests, $online_time, $order_by, $phpbb_dispatcher);

	/**
	 * Map over users in list within the last $session_length using $function
	 *
	 * @param          $user_list 		-- List of users to map over
	 * @param          $session_length 	-- get users within the last number of seconds
	 * @param callable $function		    -- function used in mapping over users.
	 *										 should take a ($row) param containing user_id & $session_time
	 *
	 * @return array -- Array of function results
	 */
	function map_users_online($user_list, $session_length, Closure $function);

	/**
	 * Map over users in list using $function
	 * @param          $user_list -- List of users to map over
	 * @param callable $function -- Function used in mapping over users
	 *								 should take a ($row) param containing user_id & $session_time
	 *
	 * @return array
	 */
	function map_certain_users_with_time($user_list, Closure $function);

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

	/** Set user session visibility
	*
	* @param int $user_id sessions with user_id to change
	* @param bool $viewonline true: set visible, false: set invisible
	*/
	function set_viewonline($user_id, $viewonline);

	// Cleanup functions

	/** Remove from storage all guest sessions older than session_length
	*
	* @param int $session_length (in seconds) remove sessions older than time - session_length
	* @return null
	*/
	function cleanup_guest_sessions($session_length);

	/** Remove from storage all sessions older than session_length
	*
	* If $user_ids is empty, nothing happens.
	*
	* @param array $user_ids
	* @param int $session_length (in seconds) remove sessions older than time - session_length
	* @return null
	*/
	function cleanup_expired_sessions(array $user_ids, $session_length);

	/** For sessions older than length, run a function and collect results.
	*
	* @param int $session_length how old to search
	* @param Closure $session_function function to run takes $row, outputs array
	* @param int $batch_size Sql Paging size
	* @return array an array containing the results of $session_function
	*/
	function map_recently_expired($session_length, Closure $session_function, $batch_size);
}
