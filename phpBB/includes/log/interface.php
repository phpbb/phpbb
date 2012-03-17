<?php
/**
*
* @package phpbb_log
* @copyright (c) 2012 phpBB Group
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
* The interface for the log-system.
*
* @package phpbb_log
*/
interface phpbb_log_interface
{
	/**
	* This function returns the state of the log-system.
	*
	* @return	bool	True if log is enabled
	*/
	public function is_enabled();

	/**
	* This function allows disable the log-system. When add_log is called, the log will not be added to the database.
	*/
	public function disable();

	/**
	* This function allows re-enable the log-system.
	*/
	public function enable();

	/**
	* Adds a log to the database
	*
	* @param	string	$mode				The mode defines which log_type is used and in which log the entry is displayed.
	* @param	int		$user_id			User ID of the user
	* @param	string	$log_ip				IP address of the user
	* @param	string	$log_operation		Name of the operation
	* @param	int		$log_time			Timestamp when the log was added.
	* @param	array	$additional_data	More arguments can be added, depending on the log_type
	*
	* @return	int|bool		Returns the log_id, if the entry was added to the database, false otherwise.
	*/
	public function add($mode, $user_id, $log_ip, $log_operation, $log_time, $additional_data);
}
