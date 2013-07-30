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
* Interface phpbb_session_banlist_interface
*/
interface phpbb_session_banlist_interface
{
	/** Gather a list of banned users
	*
	* @param string|false $user_email
	* @param string|false $user_ips
	* @param int|false $user_id
	* @param int $cache_ttl How long to keep a cached banlist
	* @return array - List of banned users
	*/
	function banlist($user_email, $user_ips, $user_id, $cache_ttl);
}
