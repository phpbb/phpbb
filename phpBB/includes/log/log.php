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
* This class is used to add entries into the log table.
*
* @package phpbb_log
*/
class phpbb_log implements phpbb_log_interface
{
	/**
	* Keeps the status of the log-system. Is the log enabled or disabled?
	*/
	private $enabled;

	/**
	* The table we use to store our logs.
	*/
	private $log_table;

	/**
	* Constructor
	*/
	public function __construct($log_table)
	{
		$this->log_table = $log_table;
		$this->enable();
	}

	/**
	* This function returns the state of the log-system.
	*
	* @return	bool	True if log is enabled
	*/
	public function is_enabled()
	{
		return $this->enabled;
	}

	/**
	* This function allows disable the log-system. When add_log is called, the log will not be added to the database.
	*/
	public function disable()
	{
		$this->enabled = false;
	}

	/**
	* This function allows re-enable the log-system.
	*/
	public function enable()
	{
		$this->enabled = true;
	}

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
	public function add($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = array())
	{
		if (!$this->is_enabled())
		{
			return false;
		}

		global $db;
		/**
		* @todo: enable when events are merged
		*
		global $db, $phpbb_dispatcher;
		*/

		if ($log_time == false)
		{
			$log_time = time();
		}

		$sql_ary = array(
			'user_id'		=> $user_id,
			'log_ip'		=> $log_ip,
			'log_time'		=> $log_time,
			'log_operation'	=> $log_operation,
		);

		switch ($mode)
		{
			case 'admin':
				$sql_ary += array(
					'log_type'		=> LOG_ADMIN,
					'log_data'		=> (!sizeof($additional_data)) ? '' : serialize($additional_data),
				);
			break;

			case 'mod':
				$forum_id = (int) $additional_data['forum_id'];
				unset($additional_data['forum_id']);
				$topic_id = (int) $additional_data['topic_id'];
				unset($additional_data['topic_id']);
				$sql_ary += array(
					'log_type'		=> LOG_MOD,
					'forum_id'		=> $forum_id,
					'topic_id'		=> $topic_id,
					'log_data'		=> (!sizeof($additional_data)) ? '' : serialize($additional_data),
				);
			break;

			case 'user':
				$reportee_id = (int) $additional_data['reportee_id'];
				unset($additional_data['reportee_id']);

				$sql_ary += array(
					'log_type'		=> LOG_USERS,
					'reportee_id'	=> $reportee_id,
					'log_data'		=> (!sizeof($additional_data)) ? '' : serialize($additional_data),
				);
			break;

			case 'critical':
				$sql_ary += array(
					'log_type'		=> LOG_CRITICAL,
					'log_data'		=> (!sizeof($additional_data)) ? '' : serialize($additional_data),
				);
			break;

			default:
				/**
				* @todo: enable when events are merged
				*
				if ($phpbb_dispatcher != null)
				{
					$vars = array('mode', 'user_id', 'log_ip', 'log_time', 'additional_data', 'sql_ary');
					$event = new phpbb_event_data(compact($vars));
					$phpbb_dispatcher->dispatch('core.add_log_case', $event);
					extract($event->get_data_filtered($vars));
				}
				*/

				// We didn't find a log_type, so we don't save it in the database.
				if (!isset($sql_ary['log_type']))
				{
					return false;
				}
		}

		/**
		* @todo: enable when events are merged
		*
		if ($phpbb_dispatcher != null)
		{
			$vars = array('mode', 'user_id', 'log_ip', 'log_time', 'additional_data', 'sql_ary');
			$event = new phpbb_event_data(compact($vars));
			$phpbb_dispatcher->dispatch('core.add_log', $event);
			extract($event->get_data_filtered($vars));
		}
		*/

		$db->sql_query('INSERT INTO ' . $this->log_table . ' ' . $db->sql_build_array('INSERT', $sql_ary));

		return $db->sql_nextid();
	}
}
