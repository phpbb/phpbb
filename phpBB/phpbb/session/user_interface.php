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
* Interface phpbb_session_user_interface
*/
interface phpbb_session_user_interface
{
	/** Get session & user data associated with user_id
	*
	* @param int $user_id
	* @return array
	*/
	function get_with_user_id($user_id);

	/** Get user information from user_id
	*
	* @param int $user_id
	* @param bool $normal_founder_only Only gather if
	*        user is a USER_NORMAL or USER_FOUNDER
	* @return mixed
	*/
	function get_user_info($user_id, $normal_founder_only = false);

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

	/** Set user session visibility
	*
	* @param int $user_id sessions with user_id to change
	* @param bool $viewonline true: set visible, false: set invisible
	*/
	function set_viewonline($user_id, $viewonline);
}
