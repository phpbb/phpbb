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
* Interface phpbb_session_storage_interface_cleanup
*/
interface phpbb_session_storage_interface_cleanup
{
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

	/** Delete sessions longer than max_autologin_time
	*
	* @param int $max_autologin_time (in seconds)
 	* @return null
	*/
	function cleanup_long_session_keys($max_autologin_time);

	/** Cleanup login attempts older than ip_login_limit_time
	* 
	* @param int $ip_login_limit_time (in seconds) delete attempts older than time - this
	* @return null
	*/
	function cleanup_attempt_table($ip_login_limit_time);
}
