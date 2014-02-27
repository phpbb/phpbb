<?php
/**
*
* @package \phpbb\log\log
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\log;

/**
* The interface for the log-system.
*
* @package \phpbb\log\log
*/
interface log_interface
{
	/**
	* This function returns the state of the log system.
	*
	* @param	string	$type	The log type we want to check. Empty to get
	*							global log status.
	*
	* @return	bool	True if log for the type is enabled
	*/
	public function is_enabled($type = '');

	/**
	* Disable log
	*
	* This function allows disabling the log system or parts of it, for this
	* page call. When add_log is called and the type is disabled,
	* the log will not be added to the database.
	*
	* @param	mixed	$type	The log type we want to disable. Empty to
	*						disable all logs. Can also be an array of types.
	*
	* @return	null
	*/
	public function disable($type = '');

	/**
	* Enable log
	*
	* This function allows re-enabling the log system.
	*
	* @param	mixed	$type	The log type we want to enable. Empty to
	*						enable all logs. Can also be an array of types.
	*
	* @return	null
	*/
	public function enable($type = '');

	/**
	* Adds a log entry to the database
	*
	* @param	string	$mode				The mode defines which log_type is used and from which log the entry is retrieved
	* @param	int		$user_id			User ID of the user
	* @param	string	$log_ip				IP address of the user
	* @param	string	$log_operation		Name of the operation
	* @param	int		$log_time			Timestamp when the log entry was added, if empty time() will be used
	* @param	array	$additional_data	More arguments can be added, depending on the log_type
	*
	* @return	int|bool		Returns the log_id, if the entry was added to the database, false otherwise.
	*/
	public function add($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = array());

	/**
	* Grab the logs from the database
	*
	* @param	string	$mode			The mode defines which log_type is used and ifrom which log the entry is retrieved
	* @param	bool	$count_logs		Shall we count all matching log entries?
	* @param	int		$limit			Limit the number of entries that are returned
	* @param	int		$offset			Offset when fetching the log entries, f.e. when paginating
	* @param	mixed	$forum_id		Restrict the log entries to the given forum_id (can also be an array of forum_ids)
	* @param	int		$topic_id		Restrict the log entries to the given topic_id
	* @param	int		$user_id		Restrict the log entries to the given user_id
	* @param	int		$log_time		Only get log entries newer than the given timestamp
	* @param	string	$sort_by		SQL order option, e.g. 'l.log_time DESC'
	* @param	string	$keywords		Will only return log entries that have the keywords in log_operation or log_data
	*
	* @return	array			The result array with the logs
	*/
	public function get_logs($mode, $count_logs = true, $limit = 0, $offset = 0, $forum_id = 0, $topic_id = 0, $user_id = 0, $log_time = 0, $sort_by = 'l.log_time DESC', $keywords = '');

	/**
	* Get total log count
	*
	* @return	int			Returns the number of matching logs from the last call to get_logs()
	*/
	public function get_log_count();

	/**
	* Get offset of the last valid page
	*
	* @return	int			Returns the offset of the last valid page from the last call to get_logs()
	*/
	public function get_valid_offset();
}
