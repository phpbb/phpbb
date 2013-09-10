<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
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

class phpbb_session_storage_native implements
										phpbb_session_storage_interface_session,
										phpbb_session_storage_interface_banlist,
										phpbb_session_storage_interface_keys,
										phpbb_session_storage_interface_cleanup,
										phpbb_session_storage_interface_user
{
	protected $db;
	protected $time_now;

	function __construct($db, $time)
	{
		$this->db = $db;
		$this->time_now = $time;
	}

	protected function query($sql)
	{
		$result = $this->db->sql_query_limit($sql, 1);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $data;
	}

	protected function update_query($sql)
	{
		return $this->db->sql_query($sql);
	}

	protected function query_return_all($sql)
	{
		$result = $this->db->sql_query($sql);
		$data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$data[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $data;
	}

	protected function map_query($sql, $function, $batch_size=null)
	{
		if (is_null($batch_size))
		{
			$result = $this->db->sql_query($sql);
		}
		else
		{
			$result = $this->db->sql_query_limit($sql, $batch_size);
		}

		$results = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$results[] = $function($row);
		}
		$this->db->sql_freeresult($result);
		return $results;
	}

	function set_time_now($time_now)
	{
		$this->time_now = $time_now;
	}

	/** Update the database used in session storage
	 *
	 * @param phpbb_db_driver $db Driver to use in queries
	 * @return null
	 */
	function set_db(phpbb_db_driver $db)
	{
		$this->db = $db;
	}

	function create($session_data)
	{
		$sql = 'INSERT INTO ' . SESSIONS_TABLE . ' ' .
				$this->db->sql_build_array('INSERT', $session_data);
		$this->update_query($sql);
	}

	function update($session_id, $session_data)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
		 		SET ' . $this->db->sql_build_array('UPDATE', $session_data) . "
				WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		return $this->update_query($sql);
	}

	function get($session_id)
	{
		$sql = 'SELECT u.*, s.*
				FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
				WHERE s.session_id = '" . $this->db->sql_escape($session_id) . "'
					AND u.user_id = s.session_user_id";
		return $this->query($sql);
	}

	function get_with_user_id($user_id)
	{
		$sql = 'SELECT u.*, s.*
				FROM ' . USERS_TABLE . ' u
					LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
				WHERE u.user_id = ' . (int) $user_id;
		return $this->query($sql);
	}

	function get_user_info($user_id, $normal_founder_only=false)
	{
		$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
		if ($normal_founder_only)
		{
			$sql .= ' AND user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		}
		return $this->query($sql);
	}

	function get_user_info_with_key($user_id, $session_key)
	{
		$sql = 'SELECT u.*
				FROM ' . USERS_TABLE . ' u, ' . SESSIONS_KEYS_TABLE . ' k
				WHERE u.user_id = ' . (int) $user_id . '
					AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ")
					AND k.user_id = u.user_id
					AND k.key_id = '" . $this->db->sql_escape(md5($session_key)) . "'";
		return $this->query($sql);
	}

	function get_newest($user_id)
	{
		$sql = 'SELECT *
				FROM ' . SESSIONS_TABLE . '
				WHERE session_user_id = ' . (int) $user_id . '
				ORDER BY session_time DESC';
		return $this->query($sql);
	}

	function delete_by_user_id($user_id)
	{
		$this->update_query('
			DELETE FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . (int) $user_id
		);
	}

	function delete($session_id = false, $user_id = false)
	{
		$sql = 'DELETE FROM ' . SESSIONS_TABLE;

		if ($session_id !== false)
		{
			$sql .= " WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		}

		if (is_numeric($user_id) || is_array($user_id))
		{
			$sql .= ($session_id !== false) ? ' AND ' : ' WHERE ';
			if (is_numeric($user_id))
			{
				$sql .= ' session_user_id = ' . (int) $user_id;
			}
			else
			{
				$sql .= $this->db->sql_in_set('session_user_id', $user_id);
			}
		}

		if ($session_id === false && $user_id === false)
		{
			throw new InvalidArgumentException("Need either session or user_id");
		}

		$result = $this->db->sql_query($sql);

		if (!$result || !$this->db->sql_affectedrows()) {
			return false;
		}

		return true;
	}

	function num_active_sessions($minutes_considered_active)
	{
		$results = $this->query('
			SELECT COUNT(session_id) AS sessions
			FROM ' . SESSIONS_TABLE . '
			WHERE session_time >= ' . ($this->time_now - $minutes_considered_active)
		);
		return $results['sessions'];
	}

	function num_sessions($user_id, $max_time)
	{
		$sql = 'SELECT COUNT(session_id) AS sessions
				FROM ' . SESSIONS_TABLE . '
				WHERE session_user_id = ' . (int) $user_id . '
					AND session_time >= ' . (int) ($this->time_now - $max_time);
		$results =  $this->query($sql);
		return $results['sessions'];
	}

	function unset_admin($session_id)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . "
				SET session_admin = 0
				WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		$this->update_query($sql);
	}

	function set_viewonline($user_id, $viewonline)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
				SET session_viewonline = ' . (int) $viewonline . '
				WHERE session_user_id = ' . (int) $user_id;
		$this->update_query($sql);
	}

	function update_last_visit($time, $user_id, $page='')
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_lastvisit = ' . (int) $time;
		if(!empty($page))
		{
			$sql .= ", user_lastpage = '" . $this->db->sql_escape($page) . "'";
		}
		$sql .=	' WHERE user_id = ' . (int) $user_id;
		$this->update_query($sql);
	}

	function update_form_salt($salt, $user_id)
	{
		$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_form_salt = '" . $this->db->sql_escape($salt) . "'
				WHERE user_id = " . (int) $user_id;
		$this->update_query($sql);
	}

	function remove_session_key($user_id, $key=false)
	{
		$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
		if ($key !== false)
		{
			$sql .= " AND key_id = '" . $this->db->sql_escape(md5($key)) . "'";
		}
		$this->update_query($sql);
	}

	function update_session_key($user_id, $key_id, array $data)
	{
		$this->update_query('
			UPDATE ' . SESSIONS_KEYS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE user_id = ' . (int) $user_id . "
				AND key_id = '" . $this->db->sql_escape(md5($key_id)) . "'"
		);
	}

	function insert_session_key($data)
	{
		$this->update_query(
			'INSERT INTO ' . SESSIONS_KEYS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $data)
		);
	}

	function cleanup_guest_sessions($session_length)
	{
		$this->update_query('
			DELETE FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . ANONYMOUS . '
				AND session_time < ' . (int) ($this->time_now - $session_length)
		);
	}

	function cleanup_expired_sessions(array $user_ids, $session_length)
	{
		if (sizeof($user_ids))
		{
			$this->update_query('
				DELETE FROM ' . SESSIONS_TABLE . '
				WHERE ' . $this->db->sql_in_set('session_user_id', $user_ids) . '
					AND session_time < ' . ($this->time_now - $session_length)
			);
		}
	}

	/** For sessions older than length, run a function and collect results.
	* @param $session_length Int - How old to search
	* @param $session_function Callable - Function to run takes $row, outputs array
	* @param $batch_size Int - Sql Paging size
	* @return Array - An array containing the results of $session_function
	*/
	function map_recently_expired($session_length, Closure $session_function, $batch_size)
	{
		$sql = '
			SELECT session_user_id, session_page, MAX(session_time) AS recent_time
			FROM ' . SESSIONS_TABLE . '
			WHERE session_time < ' . ($this->time_now - $session_length) . '
			GROUP BY session_user_id, session_page';

		return $this->map_query($sql, $session_function, $batch_size);
	}

	function map_all(Closure $function, $batch_size=25)
	{
		$sessions_table = SESSIONS_TABLE;
		return $this->map_query("SELECT * FROM $sessions_table", $function, $batch_size);
	}

	function cleanup_long_session_keys($max_autologin_time)
	{
		$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
				WHERE last_login < ' . (time() - (86400 * (int) $max_autologin_time));
		$this->update_query($sql);
	}

	function cleanup_attempt_table($ip_login_limit_time)
	{
		$sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE attempt_time < ' . (time() - (int) $ip_login_limit_time);
		$this->update_query($sql);
	}

	function banlist($user_email, $user_ips, $user_id, $cache_ttl)
	{
		$where_sql = array();
		$sql = '
			SELECT ban_ip, ban_userid, ban_email, ban_exclude, ban_give_reason, ban_end
			FROM ' . BANLIST_TABLE . '
			WHERE ';

		// Determine which entries to check, only return those
		if ($user_email === false)
		{
			$where_sql[] = "ban_email = ''";
		}

		if ($user_ips === false)
		{
			$where_sql[] = "(ban_ip = '' OR ban_exclude = 1)";
		}

		if ($user_id === false)
		{
			$where_sql[] = '(ban_userid = 0 OR ban_exclude = 1)';
		}
		else
		{
			$cache_ttl = ($user_id == ANONYMOUS) ? 3600 : 0;
			$_sql = '(ban_userid = ' . (int) $user_id;

			if ($user_email !== false)
			{
				$_sql .= " OR ban_email <> ''";
			}

			if ($user_ips !== false)
			{
				$_sql .= " OR ban_ip <> ''";
			}

			$_sql .= ')';

			$where_sql[] = $_sql;
		}
		$sql .= (sizeof($where_sql)) ? implode(' AND ', $where_sql) : '';
		return $this->db->sql_query($sql, $cache_ttl);
	}


	/**
	 * Completely delete all session data being used for phpbb
	 */
	function delete_all_sessions()
	{
		switch ($this->db->sql_layer)
		{
			case 'sqlite':
			case 'firebird':
				$this->db->sql_query("DELETE FROM " . SESSIONS_TABLE);
				break;

			default:
				$this->db->sql_query("TRUNCATE TABLE " . SESSIONS_TABLE);
				break;
		}
	}

	/**
	 * Get ip address from session_id
	 *
	 * @param $session_id
	 *
	 * @return null|string -- Either the ip address or null if none found
	 */
	public function get_user_ip_from_session($session_id)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_type, s.session_ip
		FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . " s
		WHERE s.session_id = '" . $this->db->sql_escape($session_id) . "'
			AND	u.user_id = s.session_user_id";
		$result = $this->db->sql_query($sql);

		$output = null;
		if ($current_user = $this->db->sql_fetchrow($result))
		{
			$output = $current_user['session_ip'];
		}
		$this->db->sql_freeresult($result);
		return $output;
	}

	/**
	 * Get newest user and session data for this $user_id
	 *
	 * @param $user_id -- user id to get user and session data for
	 *
	 * @return user and session data as an array
	 */
	public function get_newest_session($user_id)
	{
		$sql = 'SELECT u.*, s.*
				FROM ' . USERS_TABLE . ' u
					LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
				WHERE u.user_id = ' . (int) $user_id . '
				ORDER BY s.session_time DESC';
		return $this->query($sql);
	}

	/**
	 * Queries the session table to get information about online users
	 *
	 * @param int    $item_id Limits the search to the item with this id
	 * @param string $item    The name of the item which is stored in the session table as session_{$item}_id
	 *
	 * @return array An array containing the ids of online, hidden and visible users, as well as statistical info
	 */
	function get_users_online_totals($item_id = 0, $item = 'forum')
	{
		global $config;

		$reading_sql = '';
		if ($item_id !== 0)
		{
			$reading_sql = ' AND s.session_' . $item . '_id = ' . (int) $item_id;
		}

		$online_users = array(
			'online_users'			=> array(),
			'hidden_users'			=> array(),
			'total_online'			=> 0,
			'visible_online'		=> 0,
			'hidden_online'			=> 0,
			'guests_online'			=> 0,
		);

		if ($config['load_online_guests'])
		{
			$online_users['guests_online'] = obtain_guest_count($item_id, $item);
		}

		// a little discrete magic to cache this for 30 seconds
		$time = (time() - (intval($config['load_online_time']) * 60));

		$sql = 'SELECT s.session_user_id, s.session_ip, s.session_viewonline
		FROM ' . SESSIONS_TABLE . ' s
		WHERE s.session_time >= ' . ($time - ((int) ($time % 30))) .
			$reading_sql .
			' AND s.session_user_id <> ' . ANONYMOUS;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Skip multiple sessions for one user
			if (!isset($online_users['online_users'][$row['session_user_id']]))
			{
				$online_users['online_users'][$row['session_user_id']] = (int) $row['session_user_id'];
				if ($row['session_viewonline'])
				{
					$online_users['visible_online']++;
				}
				else
				{
					$online_users['hidden_users'][$row['session_user_id']] = (int) $row['session_user_id'];
					$online_users['hidden_online']++;
				}
			}
		}
		$online_users['total_online'] = $online_users['guests_online'] + $online_users['visible_online'] + $online_users['hidden_online'];
		$this->db->sql_freeresult($result);

		return $online_users;
	}

	/**
	 * Queries the session table to get information about online guests
	 *
	 * @param int    $item_id Limits the search to the item with this id
	 * @param string $item    The name of the item which is stored in the session table as session_{$item}_id
	 *
	 * @return int The number of active distinct guest sessions
	 */
	function obtain_guest_count($item_id = 0, $item = 'forum')
	{
		global $config;
		if ($item_id)
		{
			$reading_sql = ' AND s.session_' . $item . '_id = ' . (int) $item_id;
		}
		else
		{
			$reading_sql = '';
		}
		$time = (time() - (intval($config['load_online_time']) * 60));

		// Get number of online guests

		if ($this->db->sql_layer === 'sqlite')
		{
			$sql = 'SELECT COUNT(session_ip) as num_guests
			FROM (
				SELECT DISTINCT s.session_ip
				FROM ' . SESSIONS_TABLE . ' s
				WHERE s.session_user_id = ' . ANONYMOUS . '
					AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
				$reading_sql .
				')';
		}
		else
		{
			$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests
			FROM ' . SESSIONS_TABLE . ' s
			WHERE s.session_user_id = ' . ANONYMOUS . '
				AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
				$reading_sql;
		}
		$result = $this->db->sql_query($sql);
		$guests_online = (int) $this->db->sql_fetchfield('num_guests');
		$this->db->sql_freeresult($result);

		return $guests_online;
	}

	/**
	 * Get a list of all users active after online_time.
	 *
	 * @param $show_guests             Include anonymous users
	 * @param $online_time             Include sessions active in a time greater than this
	 * @param $order_by                order_by sql
	 * @param $phpbb_dispatcher
	 *
	 * @return array                List of all rows containing users that matched
	 */
	function get_user_list($show_guests, $online_time, $order_by, $phpbb_dispatcher)
	{
		$sql_ary = array(
			'SELECT'	=> '
				u.user_id,
				u.username,
				u.username_clean,
				u.user_type,
				u.user_colour,
				s.session_id,
				s.session_time,
				s.session_page,
				s.session_ip,
				s.session_browser,
				s.session_viewonline,
				s.session_forum_id',
			'FROM'		=> array(
				USERS_TABLE		=> 'u',
				SESSIONS_TABLE	=> 's',
			),
			'WHERE'		=> 'u.user_id = s.session_user_id
			AND s.session_time >= ' . $online_time .
			((!$show_guests) ? ' AND s.session_user_id <> ' . ANONYMOUS : ''),
			'ORDER_BY'	=> $order_by,
		);
		$vars = array('sql_ary', 'show_guests');
		extract($phpbb_dispatcher->trigger_event('core.viewonline_modify_sql', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		return $this->query_return_all($sql);
	}

	/**
	 * Map over users in list within the last $session_length using $function
	 *
	 * @param          $user_list              -- List of users to map over
	 * @param          $session_length         -- get users within the last number of seconds
	 * @param callable $function               -- function used in mapping over users.
	 *                                         should take a ($row) param containing user_id & $session_time
	 *
	 * @return array -- Array of function results
	 */
	function map_users_online($user_list, $session_length, Closure $function)
	{
		$sql = 'SELECT session_user_id, MAX(session_time) AS session_time
				FROM ' . SESSIONS_TABLE . '
				WHERE session_time >= ' . (time() - $session_length) . '
					AND ' . $this->db->sql_in_set('session_user_id', $user_list) . '
				GROUP BY session_user_id';
		return $this->map_query($sql, $function);
	}

	/**
	 * Map over users in list using $function
	 * @param          $user_list      -- List of users to map over
	 * @param callable $function       -- Function used in mapping over users
	 *                                 should take a ($row) param containing user_id & $session_time
	 *
	 * @return array
	 */
	function map_certain_users_with_time($user_list, Closure $function)
	{
		$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
		FROM ' . SESSIONS_TABLE . '
		WHERE ' . $this->db->sql_in_set('session_user_id', $user_list) . '
		GROUP BY session_user_id';
		return $this->map_query($sql, $function);
	}

	/**
	 * Map over all friends of user with user_id
	 *
	 * @param          $user_id        user_id of who we should find friends to map over
	 * @param callable $function       function to map with
	 *                                    (function should take $user param containing friend of user)
	 *
	 * @return array    Array containing results of the function
	 */
	function map_friends_online($user_id, Closure $function)
	{
		$sql_ary = array(
			'SELECT'	=> 'u.user_id, u.username, u.username_clean, u.user_colour, MAX(s.session_time) as online_time, MIN(s.session_viewonline) AS viewonline',

			'FROM'		=> array(
				USERS_TABLE		=> 'u',
				ZEBRA_TABLE		=> 'z',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(SESSIONS_TABLE => 's'),
					'ON'	=> 's.session_user_id = z.zebra_id',
				),
			),

			'WHERE'		=> 'z.user_id = ' . $user_id . '
			AND z.friend = 1
			AND u.user_id = z.zebra_id',

			'GROUP_BY'	=> 'z.zebra_id, u.user_id, u.username_clean, u.user_colour, u.username',

			'ORDER_BY'	=> 'u.username_clean ASC',
		);

		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_ary);
		return $this->map_query($sql, $function);
	}

	/**
	 * Get the longest session, and visibility for $user_id
	 *
	 * @param int $user_id    User id
	 *
	 * @return array Array containing user_id, online_time, viewonline
	 */
	function get_user_online_time($user_id)
	{
		$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
			FROM ' . SESSIONS_TABLE . "
			WHERE session_user_id = $user_id
			GROUP BY session_user_id";
		return $this->query($sql);
	}

	/** Gets friends from zebra table with user_id
	 *
	 * @param $user_id
	 * @return mixed
	 */
	function get_friends($user_id)
	{
		$sql_ary = array(
			'SELECT'	=> 'u.user_id, u.username, u.username_clean, u.user_colour',
			'FROM'		=> array(
				USERS_TABLE		=> 'u',
				ZEBRA_TABLE		=> 'z',
			),
			'WHERE'		=> 'z.user_id = ' . $user_id . '
			AND z.friend = 1
			AND u.user_id = z.zebra_id',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		return $this->query_return_all($sql);
	}
}
