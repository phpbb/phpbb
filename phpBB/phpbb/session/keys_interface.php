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
* Interface phpbb_session_keys_interface
*/
interface phpbb_session_keys_interface
{
	/** Get user information with user_id and session_key
	*
	* @param int $user_id
	* @param string $session_key
	* @return array user information
	*/
	function get_user_info_with_key($user_id, $session_key);

	/** Remove session_key for user_id
	*
	* @param int $user_id
	* @param string|bool $key if given, only remove this key
	* @return null
	*/
	function remove_session_key($user_id, $key = false);

	/** Change session_key for user_id
	*
	* @param int $user_id user to change
	* @param string $key_id key to change
	* @param array $data new data
	* @return null
	*/
	function update_session_key($user_id, $key_id, array $data);

	/** Insert session key information into storage
	*
	* @param array $data session key data to store
	* @return null
	*/
	function insert_session_key($data);
}
