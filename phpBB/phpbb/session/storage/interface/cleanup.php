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

	/** Cleanup login attempts older than ip_login_limit_time
	* 
	* @param int $ip_login_limit_time (in seconds) delete attempts older than time - this
	* @return null
	*/
	function cleanup_attempt_table($ip_login_limit_time);
}
