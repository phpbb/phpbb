<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\log;

/**
* This class is used to add entries into the log table.
*/
class log implements \phpbb\log\log_interface
{
	/**
	* If set, administrative user profile links will be returned and messages
	* will not be censored.
	* @var bool
	*/
	protected $is_in_admin;

	/**
	* An array with the disabled log types. Logs of such types will not be
	* added when add() is called.
	* @var array
	*/
	protected $disabled_types;

	/**
	* Keeps the total log count of the last call to get_logs()
	* @var int
	*/
	protected $entry_count;

	/**
	* Keeps the offset of the last valid page of the last call to get_logs()
	* @var int
	*/
	protected $last_page_offset;

	/**
	* The table we use to store our logs.
	* @var string
	*/
	protected $log_table;

	/**
	* Database object
	* @var \phpbb\db\driver\driver
	*/
	protected $db;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Auth object
	* @var \phpbb\auth\auth
	*/
	protected $auth;

	/**
	* Event dispatcher object
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $dispatcher;

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* Admin root path
	* @var string
	*/
	protected $phpbb_admin_path;

	/**
	* PHP Extension
	* @var string
	*/
	protected $php_ext;

	/**
	* Constructor
	*
	* @param	\phpbb\db\driver\driver_interface	$db		Database object
	* @param	\phpbb\user		$user	User object
	* @param	\phpbb\auth\auth		$auth	Auth object
	* @param	\phpbb\event\dispatcher_interface	$phpbb_dispatcher	Event dispatcher
	* @param	string		$phpbb_root_path		Root path
	* @param	string		$relative_admin_path	Relative admin root path
	* @param	string		$php_ext			PHP Extension
	* @param	string		$log_table		Name of the table we use to store our logs
	*/
	public function __construct($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, $relative_admin_path, $php_ext, $log_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->auth = $auth;
		$this->dispatcher = $phpbb_dispatcher;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $this->phpbb_root_path . $relative_admin_path;
		$this->php_ext = $php_ext;
		$this->log_table = $log_table;

		/*
		* IN_ADMIN is set after the session is created,
		* so we need to take ADMIN_START into account as well, otherwise
		* it will not work for the \phpbb\log\log object we create in common.php
		*/
		$this->set_is_admin((defined('ADMIN_START') && ADMIN_START) || (defined('IN_ADMIN') && IN_ADMIN));
		$this->enable();
	}

	/**
	* Set is_in_admin in order to return administrative user profile links
	* in get_logs()
	*
	* @param	bool	$is_in_admin		Are we called from within the acp?
	* @return	null
	*/
	public function set_is_admin($is_in_admin)
	{
		$this->is_in_admin = (bool) $is_in_admin;
	}

	/**
	* Returns the is_in_admin option
	*
	* @return	bool
	*/
	public function get_is_admin()
	{
		return $this->is_in_admin;
	}

	/**
	* Set table name
	*
	* @param	string	$log_table		Can overwrite the table to use for the logs
	* @return	null
	*/
	public function set_log_table($log_table)
	{
		$this->log_table = $log_table;
	}

	/**
	* {@inheritDoc}
	*/
	public function is_enabled($type = '')
	{
		if ($type == '' || $type == 'all')
		{
			return !isset($this->disabled_types['all']);
		}
		return !isset($this->disabled_types[$type]) && !isset($this->disabled_types['all']);
	}

	/**
	* {@inheritDoc}
	*/
	public function disable($type = '')
	{
		if (is_array($type))
		{
			foreach ($type as $disable_type)
			{
				$this->disable($disable_type);
			}
			return;
		}

		// Empty string is an equivalent for all types.
		if ($type == '')
		{
			$type = 'all';
		}
		$this->disabled_types[$type] = true;
	}

	/**
	* {@inheritDoc}
	*/
	public function enable($type = '')
	{
		if (is_array($type))
		{
			foreach ($type as $enable_type)
			{
				$this->enable($enable_type);
			}
			return;
		}

		if ($type == '' || $type == 'all')
		{
			$this->disabled_types = array();
			return;
		}
		unset($this->disabled_types[$type]);
	}

	/**
	* {@inheritDoc}
	*/
	public function add($mode, $user_id, $log_ip, $log_operation, $log_time = false, $additional_data = array())
	{
		if (!$this->is_enabled($mode))
		{
			return false;
		}

		if ($log_time === false)
		{
			$log_time = time();
		}

		$sql_ary = array(
			'user_id'		=> !empty($user_id) ? $user_id : ANONYMOUS,
			'log_ip'		=> !empty($log_ip) ? $log_ip : '',
			'log_time'		=> $log_time,
			'log_operation'	=> $log_operation,
		);

		switch ($mode)
		{
			case 'admin':
				$sql_ary += array(
					'log_type'		=> LOG_ADMIN,
					'log_data'		=> (!empty($additional_data)) ? serialize($additional_data) : '',
				);
			break;

			case 'mod':
				$forum_id = isset($additional_data['forum_id']) ? (int) $additional_data['forum_id'] : 0;
				unset($additional_data['forum_id']);
				$topic_id = isset($additional_data['topic_id']) ? (int) $additional_data['topic_id'] : 0;
				unset($additional_data['topic_id']);
				$post_id = isset($additional_data['post_id']) ? (int) $additional_data['post_id'] : 0;
				unset($additional_data['post_id']);
				$sql_ary += array(
					'log_type'		=> LOG_MOD,
					'forum_id'		=> $forum_id,
					'topic_id'		=> $topic_id,
					'post_id'		=> $post_id,
					'log_data'		=> (!empty($additional_data)) ? serialize($additional_data) : '',
				);
			break;

			case 'user':
				$reportee_id = (int) $additional_data['reportee_id'];
				unset($additional_data['reportee_id']);

				$sql_ary += array(
					'log_type'		=> LOG_USERS,
					'reportee_id'	=> $reportee_id,
					'log_data'		=> (!empty($additional_data)) ? serialize($additional_data) : '',
				);
			break;

			case 'critical':
				$sql_ary += array(
					'log_type'		=> LOG_CRITICAL,
					'log_data'		=> (!empty($additional_data)) ? serialize($additional_data) : '',
				);
			break;
		}

		/**
		* Allows to modify log data before we add it to the database
		*
		* NOTE: if sql_ary does not contain a log_type value, the entry will
		* not be stored in the database. So ensure to set it, if needed.
		*
		* @event core.add_log
		* @var	string	mode			Mode of the entry we log
		* @var	int		user_id			ID of the user who triggered the log
		* @var	string	log_ip			IP of the user who triggered the log
		* @var	string	log_operation	Language key of the log operation
		* @var	int		log_time		Timestamp, when the log was added
		* @var	array	additional_data	Array with additional log data
		* @var	array	sql_ary			Array with log data we insert into the
		*							database. If sql_ary[log_type] is not set,
		*							we won't add the entry to the database.
		* @since 3.1.0-a1
		*/
		$vars = array(
			'mode',
			'user_id',
			'log_ip',
			'log_operation',
			'log_time',
			'additional_data',
			'sql_ary',
		);
		extract($this->dispatcher->trigger_event('core.add_log', compact($vars)));

		// We didn't find a log_type, so we don't save it in the database.
		if (!isset($sql_ary['log_type']))
		{
			return false;
		}

		$this->db->sql_query('INSERT INTO ' . $this->log_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));

		return $this->db->sql_nextid();
	}

	/**
	* {@inheritDoc}
	*/
	public function delete($mode, $conditions = array())
	{
		switch ($mode)
		{
			case 'admin':
				$log_type = LOG_ADMIN;
				break;

			case 'mod':
				$log_type = LOG_MOD;
				break;

			case 'user':
				$log_type = LOG_USERS;
				break;

			case 'users':
				$log_type = LOG_USERS;
				break;

			case 'critical':
				$log_type = LOG_CRITICAL;
				break;

			default:
				$log_type = false;
		}

		/**
		* Allows to modify log data before we delete it from the database
		*
		* NOTE: if sql_ary does not contain a log_type value, the entry will
		* not be deleted in the database. So ensure to set it, if needed.
		*
		* @event core.delete_log
		* @var	string	mode			Mode of the entry we log
		* @var	string	log_type		Type ID of the log (should be different than false)
		* @var	array	conditions		An array of conditions, 3 different  forms are accepted
		* 								1) <key> => <value> transformed into 'AND <key> = <value>' (value should be an integer)
		*								2) <key> => array(<operator>, <value>) transformed into 'AND <key> <operator> <value>' (values can't be an array)
		*								3) <key> => array('IN' => array(<values>)) transformed into 'AND <key> IN <values>'
		*								A special field, keywords, can also be defined. In this case only the log entries that have the keywords in log_operation or log_data will be deleted.
		* @since 3.1.0-b4
		*/
		$vars = array(
			'mode',
			'log_type',
			'conditions',
		);
		extract($this->dispatcher->trigger_event('core.delete_log', compact($vars)));

		if ($log_type === false)
		{
			return;
		}

		$sql_where = 'WHERE log_type = ' . $log_type;

		if (isset($conditions['keywords']))
		{
			$sql_where .= $this->generate_sql_keyword($conditions['keywords'], '');

			unset($conditions['keywords']);
		}

		foreach ($conditions as $field => $field_value)
		{
			$sql_where .= ' AND ';

			if (is_array($field_value) && count($field_value) == 2 && !is_array($field_value[1]))
			{
				$sql_where .= $field . ' ' . $field_value[0] . ' ' . $field_value[1];
			}
			else if (is_array($field_value) && isset($field_value['IN']) && is_array($field_value['IN']))
			{
				$sql_where .= $this->db->sql_in_set($field, $field_value['IN']);
			}
			else
			{
				$sql_where .= $field . ' = ' . $field_value;
			}
		}

		$sql = 'DELETE FROM ' . $this->log_table . "
					$sql_where";
		$this->db->sql_query($sql);

		$this->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLEAR_' . strtoupper($mode));
	}

	/**
	* {@inheritDoc}
	*/
	public function get_logs($mode, $count_logs = true, $limit = 0, $offset = 0, $forum_id = 0, $topic_id = 0, $user_id = 0, $log_time = 0, $sort_by = 'l.log_time DESC', $keywords = '')
	{
		$this->entry_count = 0;
		$this->last_page_offset = $offset;

		$topic_id_list = $reportee_id_list = array();

		$profile_url = ($this->get_is_admin() && $this->phpbb_admin_path) ? append_sid("{$this->phpbb_admin_path}index.{$this->php_ext}", 'i=users&amp;mode=overview') : append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=viewprofile');

		switch ($mode)
		{
			case 'admin':
				$log_type = LOG_ADMIN;
				$sql_additional = '';
			break;

			case 'mod':
				$log_type = LOG_MOD;
				$sql_additional = '';

				if ($topic_id)
				{
					$sql_additional = 'AND l.topic_id = ' . (int) $topic_id;
				}
				else if (is_array($forum_id))
				{
					$sql_additional = 'AND ' . $this->db->sql_in_set('l.forum_id', array_map('intval', $forum_id));
				}
				else if ($forum_id)
				{
					$sql_additional = 'AND l.forum_id = ' . (int) $forum_id;
				}
			break;

			case 'user':
				$log_type = LOG_USERS;
				$sql_additional = 'AND l.reportee_id = ' . (int) $user_id;
			break;

			case 'users':
				$log_type = LOG_USERS;
				$sql_additional = '';
			break;

			case 'critical':
				$log_type = LOG_CRITICAL;
				$sql_additional = '';
			break;

			default:
				$log_type = false;
				$sql_additional = '';
		}

		/**
		* Overwrite log type and limitations before we count and get the logs
		*
		* NOTE: if log_type is false, no entries will be returned.
		*
		* @event core.get_logs_modify_type
		* @var	string	mode		Mode of the entries we display
		* @var	bool	count_logs	Do we count all matching entries?
		* @var	int		limit		Limit the number of entries
		* @var	int		offset		Offset when fetching the entries
		* @var	mixed	forum_id	Limit entries to the forum_id,
		*							can also be an array of forum_ids
		* @var	int		topic_id	Limit entries to the topic_id
		* @var	int		user_id		Limit entries to the user_id
		* @var	int		log_time	Limit maximum age of log entries
		* @var	string	sort_by		SQL order option
		* @var	string	keywords	Will only return entries that have the
		*							keywords in log_operation or log_data
		* @var	string	profile_url	URL to the users profile
		* @var	int		log_type	Limit logs to a certain type. If log_type
		*							is false, no entries will be returned.
		* @var	string	sql_additional	Additional conditions for the entries,
		*								e.g.: 'AND l.forum_id = 1'
		* @since 3.1.0-a1
		*/
		$vars = array(
			'mode',
			'count_logs',
			'limit',
			'offset',
			'forum_id',
			'topic_id',
			'user_id',
			'log_time',
			'sort_by',
			'keywords',
			'profile_url',
			'log_type',
			'sql_additional',
		);
		extract($this->dispatcher->trigger_event('core.get_logs_modify_type', compact($vars)));

		if ($log_type === false)
		{
			$this->last_page_offset = 0;
			return array();
		}

		$sql_keywords = '';
		if (!empty($keywords))
		{
			// Get the SQL condition for our keywords
			$sql_keywords = $this->generate_sql_keyword($keywords);
		}

		$get_logs_sql_ary = array(
			'SELECT' => 'l.*, u.username, u.username_clean, u.user_colour',
			'FROM' => array(
						$this->log_table => 'l',
						USERS_TABLE => 'u',
					),
			'WHERE' => 'l.log_type = ' . (int) $log_type . "
					AND l.user_id = u.user_id
					$sql_keywords
					$sql_additional",

			'ORDER_BY' => $sort_by,
		);

		if ($log_time)
		{
			$get_logs_sql_ary['WHERE'] = 'l.log_time >= ' . (int) $log_time . '
					AND ' . $get_logs_sql_ary['WHERE'];
		}

		/**
		* Modify the query to obtain the logs data
		*
		* @event core.get_logs_main_query_before
		* @var	array	get_logs_sql_ary	The array in the format of the query builder with the query
		*									to get the log count and the log list
		* @var	string	mode				Mode of the entries we display
		* @var	bool	count_logs			Do we count all matching entries?
		* @var	int		limit				Limit the number of entries
		* @var	int		offset				Offset when fetching the entries
		* @var	mixed	forum_id			Limit entries to the forum_id,
		*									can also be an array of forum_ids
		* @var	int		topic_id			Limit entries to the topic_id
		* @var	int		user_id				Limit entries to the user_id
		* @var	int		log_time			Limit maximum age of log entries
		* @var	string	sort_by				SQL order option
		* @var	string	keywords			Will only return entries that have the
		*									keywords in log_operation or log_data
		* @var	string	profile_url			URL to the users profile
		* @var	int		log_type			Limit logs to a certain type. If log_type
		*									is false, no entries will be returned.
		* @var	string	sql_additional		Additional conditions for the entries,
		*									e.g.: 'AND l.forum_id = 1'
		* @since 3.1.5-RC1
		*/
		$vars = array(
			'get_logs_sql_ary',
			'mode',
			'count_logs',
			'limit',
			'offset',
			'forum_id',
			'topic_id',
			'user_id',
			'log_time',
			'sort_by',
			'keywords',
			'profile_url',
			'log_type',
			'sql_additional',
		);
		extract($this->dispatcher->trigger_event('core.get_logs_main_query_before', compact($vars)));

		if ($count_logs)
		{
			$count_logs_sql_ary = $get_logs_sql_ary;

			$count_logs_sql_ary['SELECT'] = 'COUNT(l.log_id) AS total_entries';
			unset($count_logs_sql_ary['ORDER_BY']);

			$sql = $this->db->sql_build_query('SELECT', $count_logs_sql_ary);
			$result = $this->db->sql_query($sql);
			$this->entry_count = (int) $this->db->sql_fetchfield('total_entries');
			$this->db->sql_freeresult($result);

			if ($this->entry_count == 0)
			{
				// Save the queries, because there are no logs to display
				$this->last_page_offset = 0;
				return array();
			}

			// Return the user to the last page that is valid
			while ($this->last_page_offset >= $this->entry_count)
			{
				$this->last_page_offset = max(0, $this->last_page_offset - $limit);
			}
		}

		$sql = $this->db->sql_build_query('SELECT', $get_logs_sql_ary);
		$result = $this->db->sql_query_limit($sql, $limit, $this->last_page_offset);

		$i = 0;
		$log = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['forum_id'] = (int) $row['forum_id'];
			if ($row['topic_id'])
			{
				$topic_id_list[] = (int) $row['topic_id'];
			}

			if ($row['reportee_id'])
			{
				$reportee_id_list[] = (int) $row['reportee_id'];
			}

			$log_entry_data = array(
				'id'				=> (int) $row['log_id'],

				'reportee_id'			=> (int) $row['reportee_id'],
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> (int) $row['user_id'],
				'username'			=> $row['username'],
				'username_full'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, $profile_url),

				'ip'				=> $row['log_ip'],
				'time'				=> (int) $row['log_time'],
				'forum_id'			=> (int) $row['forum_id'],
				'topic_id'			=> (int) $row['topic_id'],
				'post_id'			=> (int) $row['post_id'],

				'viewforum'			=> ($row['forum_id'] && $this->auth->acl_get('f_read', $row['forum_id'])) ? append_sid("{$this->phpbb_root_path}viewforum.{$this->php_ext}", 'f=' . $row['forum_id']) : false,
				'action'			=> (isset($this->user->lang[$row['log_operation']])) ? $row['log_operation'] : '{' . ucfirst(str_replace('_', ' ', $row['log_operation'])) . '}',
			);

			/**
			* Modify the entry's data before it is returned
			*
			* @event core.get_logs_modify_entry_data
			* @var	array	row			Entry data from the database
			* @var	array	log_entry_data	Entry's data which is returned
			* @since 3.1.0-a1
			*/
			$vars = array('row', 'log_entry_data');
			extract($this->dispatcher->trigger_event('core.get_logs_modify_entry_data', compact($vars)));

			$log[$i] = $log_entry_data;

			if (!empty($row['log_data']))
			{
				$log_data_ary = unserialize($row['log_data']);
				$log_data_ary = ($log_data_ary !== false) ? $log_data_ary : array();

				if (isset($this->user->lang[$row['log_operation']]))
				{
					// Check if there are more occurrences of % than
					// arguments, if there are we fill out the arguments
					// array. It doesn't matter if we add more arguments than
					// placeholders.
					$num_args = 0;
					if (!is_array($this->user->lang[$row['log_operation']]))
					{
						$num_args = substr_count($this->user->lang[$row['log_operation']], '%');
					}
					else
					{
						foreach ($this->user->lang[$row['log_operation']] as $case => $plural_string)
						{
							$num_args = max($num_args, substr_count($plural_string, '%'));
						}
					}

					if (($num_args - count($log_data_ary)) > 0)
					{
						$log_data_ary = array_merge($log_data_ary, array_fill(0, $num_args - count($log_data_ary), ''));
					}

					$lang_arguments = array_merge(array($log[$i]['action']), $log_data_ary);
					$log[$i]['action'] = call_user_func_array(array($this->user, 'lang'), array_values($lang_arguments));

					// If within the admin panel we do not censor text out
					if ($this->get_is_admin())
					{
						$log[$i]['action'] = bbcode_nl2br($log[$i]['action']);
					}
					else
					{
						$log[$i]['action'] = bbcode_nl2br(censor_text($log[$i]['action']));
					}
				}
				else if (!empty($log_data_ary))
				{
					$log[$i]['action'] .= '<br />' . implode('', $log_data_ary);
				}

				/* Apply make_clickable... has to be seen if it is for good. :/
				// Seems to be not for the moment, reconsider later...
				$log[$i]['action'] = make_clickable($log[$i]['action']);
				*/
			}
			else
			{
				$log[$i]['action'] = $this->user->lang($log[$i]['action']);
			}

			$i++;
		}
		$this->db->sql_freeresult($result);

		/**
		* Get some additional data after we got all log entries
		*
		* @event core.get_logs_get_additional_data
		* @var	array	log			Array with all our log entries
		* @var	array	topic_id_list		Array of topic ids, for which we
		*									get the permission data
		* @var	array	reportee_id_list	Array of additional user IDs we
		*									get the username strings for
		* @since 3.1.0-a1
		*/
		$vars = array('log', 'topic_id_list', 'reportee_id_list');
		extract($this->dispatcher->trigger_event('core.get_logs_get_additional_data', compact($vars)));

		if (count($topic_id_list))
		{
			$topic_auth = $this->get_topic_auth($topic_id_list);

			foreach ($log as $key => $row)
			{
				$log[$key]['viewtopic'] = (isset($topic_auth['f_read'][$row['topic_id']])) ? append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", 't=' . $row['topic_id']) : false;
				$log[$key]['viewpost'] = (isset($topic_auth['f_read'][$row['topic_id']]) && $row['post_id']) ? append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", 'p=' . $row['post_id'] . '#p' . $row['post_id']) : false;
				$log[$key]['viewlogs'] = (isset($topic_auth['m_'][$row['topic_id']])) ? append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=logs&amp;mode=topic_logs&amp;t=' . $row['topic_id'], true, $this->user->session_id) : false;
			}
		}

		if (count($reportee_id_list))
		{
			$reportee_data_list = $this->get_reportee_data($reportee_id_list);

			foreach ($log as $key => $row)
			{
				if (!isset($reportee_data_list[$row['reportee_id']]))
				{
					continue;
				}

				$log[$key]['reportee_username'] = $reportee_data_list[$row['reportee_id']]['username'];
				$log[$key]['reportee_username_full'] = get_username_string('full', $row['reportee_id'], $reportee_data_list[$row['reportee_id']]['username'], $reportee_data_list[$row['reportee_id']]['user_colour'], false, $profile_url);
			}
		}

		/**
		* Allow modifying or execute extra final filter on log entries
		*
		* @event core.get_logs_after
		* @var	array	log			Array with all our log entries
		* @var	array	topic_id_list		Array of topic ids, for which we
		*									get the permission data
		* @var	array	reportee_id_list	Array of additional user IDs we
		*									get the username strings for
		* @var	string	mode		Mode of the entries we display
		* @var	bool	count_logs	Do we count all matching entries?
		* @var	int		limit		Limit the number of entries
		* @var	int		offset		Offset when fetching the entries
		* @var	mixed	forum_id	Limit entries to the forum_id,
		*							can also be an array of forum_ids
		* @var	int		topic_id	Limit entries to the topic_id
		* @var	int		user_id		Limit entries to the user_id
		* @var	int		log_time	Limit maximum age of log entries
		* @var	string	sort_by		SQL order option
		* @var	string	keywords	Will only return entries that have the
		*							keywords in log_operation or log_data
		* @var	string	profile_url	URL to the users profile
		* @var	int		log_type	The type of logs it was filtered
		* @since 3.1.3-RC1
		*/
		$vars = array(
			'log',
			'topic_id_list',
			'reportee_id_list',
			'mode',
			'count_logs',
			'limit',
			'offset',
			'forum_id',
			'topic_id',
			'user_id',
			'log_time',
			'sort_by',
			'keywords',
			'profile_url',
			'log_type',
		);
		extract($this->dispatcher->trigger_event('core.get_logs_after', compact($vars)));

		return $log;
	}

	/**
	* Generates a sql condition for the specified keywords
	*
	* @param	string	$keywords			The keywords the user specified to search for
	* @param	string	$table_alias		The alias of the logs' table ('l.' by default)
	* @param	string	$statement_operator	The operator used to prefix the statement ('AND' by default)
	*
	* @return	string		Returns the SQL condition searching for the keywords
	*/
	protected function generate_sql_keyword($keywords, $table_alias = 'l.', $statement_operator = 'AND')
	{
		// Use no preg_quote for $keywords because this would lead to sole
		// backslashes being added. We also use an OR connection here for
		// spaces and the | string. Currently, regex is not supported for
		// searching (but may come later).
		$keywords = preg_split('#[\s|]+#u', utf8_strtolower($keywords), 0, PREG_SPLIT_NO_EMPTY);
		$sql_keywords = '';

		if (!empty($keywords))
		{
			$keywords_pattern = array();

			// Build pattern and keywords...
			for ($i = 0, $num_keywords = count($keywords); $i < $num_keywords; $i++)
			{
				$keywords_pattern[] = preg_quote($keywords[$i], '#');
				$keywords[$i] = $this->db->sql_like_expression($this->db->get_any_char() . $keywords[$i] . $this->db->get_any_char());
			}

			$keywords_pattern = '#' . implode('|', $keywords_pattern) . '#ui';

			$operations = array();
			foreach ($this->user->lang as $key => $value)
			{
				if (substr($key, 0, 4) == 'LOG_')
				{
					if (is_array($value))
					{
						foreach ($value as $plural_value)
						{
							if (preg_match($keywords_pattern, $plural_value))
							{
								$operations[] = $key;
								break;
							}
						}
					}
					else if (preg_match($keywords_pattern, $value))
					{
						$operations[] = $key;
					}
				}
			}

			$sql_keywords = ' ' . $statement_operator . ' (';
			if (!empty($operations))
			{
				$sql_keywords .= $this->db->sql_in_set($table_alias . 'log_operation', $operations) . ' OR ';
			}
			$sql_lower = $this->db->sql_lower_text($table_alias . 'log_data');
			$sql_keywords .= " $sql_lower " . implode(" OR $sql_lower ", $keywords) . ')';
		}

		return $sql_keywords;
	}

	/**
	* Determine whether the user is allowed to read and/or moderate the forum of the topic
	*
	* @param	array	$topic_ids	Array with the topic ids
	*
	* @return	array		Returns an array with two keys 'm_' and 'read_f' which are also an array of topic_id => forum_id sets when the permissions are given. Sample:
	*						array(
	*							'permission' => array(
	*								topic_id => forum_id
	*							),
	*						),
	*/
	protected function get_topic_auth(array $topic_ids)
	{
		$forum_auth = array('f_read' => array(), 'm_' => array());
		$topic_ids = array_unique($topic_ids);

		$sql_ary = array(
			'SELECT'	=> 'topic_id, forum_id',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't',
			),
			'WHERE'		=> $this->db->sql_in_set('topic_id', array_map('intval', $topic_ids)),
		);

		/**
		* Allow modifying SQL query before topic data is retrieved.
		*
		* @event core.phpbb_log_get_topic_auth_sql_before
		* @var	array	topic_ids	Array with unique topic IDs
		* @var	array	sql_ary		SQL array
		* @since 3.1.11-RC1
		*/
		$vars = array(
			'topic_ids',
			'sql_ary',
		);
		extract($this->dispatcher->trigger_event('core.phpbb_log_get_topic_auth_sql_before', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['topic_id'] = (int) $row['topic_id'];
			$row['forum_id'] = (int) $row['forum_id'];

			if ($this->auth->acl_get('f_read', $row['forum_id']))
			{
				$forum_auth['f_read'][$row['topic_id']] = $row['forum_id'];
			}

			/**
			 * Allow modifying SQL query after topic data is retrieved (inside loop).
			 *
			 * @event core.phpbb_log_get_topic_auth_sql_after
			 * @var	array	forum_auth	Forum permissions
			 * @var	array	row			One row of data from SQL query
			 * @since 3.2.2-RC1
			 */
			$vars = array(
				'forum_auth',
				'row',
			);
			extract($this->dispatcher->trigger_event('core.phpbb_log_get_topic_auth_sql_after', compact($vars)));

			if ($this->auth->acl_gets('a_', 'm_', $row['forum_id']))
			{
				$forum_auth['m_'][$row['topic_id']] = $row['forum_id'];
			}
		}
		$this->db->sql_freeresult($result);

		return $forum_auth;
	}

	/**
	* Get the data for all reportee from the database
	*
	* @param	array	$reportee_ids	Array with the user ids of the reportees
	*
	* @return	array		Returns an array with the reportee data
	*/
	protected function get_reportee_data(array $reportee_ids)
	{
		$reportee_ids = array_unique($reportee_ids);
		$reportee_data_list = array();

		$sql = 'SELECT user_id, username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_id', $reportee_ids);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$reportee_data_list[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $reportee_data_list;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_log_count()
	{
		return ($this->entry_count) ? $this->entry_count : 0;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_valid_offset()
	{
		return ($this->last_page_offset) ? $this->last_page_offset : 0;
	}
}
