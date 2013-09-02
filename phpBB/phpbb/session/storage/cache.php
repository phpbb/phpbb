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


class phpbb_session_storage_cache implements phpbb_session_storage_interface_session
{
	public $time_now;
	public $db_user;
	protected $cache;
	const all_sessions_key = '_ALL_SESSIONS';
	const user_id_prefix = '_SESSION_USER_';
	const session_prefix = '_SESSION_';

	function __construct(
		phpbb_cache_driver_atomic_interface $cache_driver,
		phpbb_session_storage_interface_user $db_user,
		$time)
	{
		$this->db_user = $db_user;
		$this->set_time_now($time);
		$this->cache = $cache_driver;
		if (!$this->cache->_isset(self::all_sessions_key))
		{
			$this->cache->_write(self::all_sessions_key, array());
		}
	}

	protected function add_session_to_user($session)
	{
		$session_id   = $session['session_id'];
		$session_time = $session['session_time'];
		$key = $this->user_key($session['session_user_id']);
		if (!$this->cache->_isset($key))
		{
			$this->cache->_write($key, array());
		}
		$this->cache->atomic_operation($key,
			function ($sessions) use ($session_id, $session_time)
			{
				$sessions[$session_id] = $session_time;
				return $sessions;
			});
	}

	protected function remove_session_from_user($user_id, $session_id)
	{
		$key = $this->user_key($user_id);
		$this->cache->atomic_operation($key,
			function ($sessions) use ($session_id)
			{
				unset($sessions[$session_id]);
				return $sessions;
			}
		);
	}

	protected function add_session_to_allsessions($session)
	{
		$session_id   = $session['session_id'];
		$session_time = $session['session_time'];
		$this->cache->atomic_operation(self::all_sessions_key,
			function ($all_sessions) use ($session_id, $session_time)
			{
				$all_sessions[$session_id] = $session_time;
				return $all_sessions;
			}
		);
	}

	protected function remove_session_from_allsessions($session_id)
	{
		$this->cache->atomic_operation(self::all_sessions_key,
			function ($all_sessions) use ($session_id)
			{
				unset($all_sessions[$session_id]);
				return $all_sessions;
			}
		);
	}

	protected function user_key($user_id)
	{
		return self::user_id_prefix . $user_id;
	}

	function create($session_data)
	{
		$id = $session_data['session_id'];

		$this->add_session_to_user($session_data);
		$this->add_session_to_allsessions($session_data);
		$this->cache->put(self::session_prefix . $id, $session_data);
	}

	function update($session_id, $session_data)
	{
		$this->cache->put(self::session_prefix . $session_id, $session_data);
	}

	function get_session($session_id)
	{
		return $this->cache->_read(self::session_prefix . $session_id);
	}

	function get_user_sessions($user_id)
	{
		return $this->cache->_read($this->user_key($user_id));
	}

	function delete($session_id = false, $user_id = false)
	{
		if ($user_id !== false)
		{
			$session = $this->get_session($session_id);
			if ($session['session_user_id'] != $user_id)
			{
				// Does not match, do not destroy
				return;
			}
		}
		else
		{
			$session = $this->get_session($session_id);
			$user_id = $session['session_user_id'];
		}
		$this->remove_session_from_user($this->user_key($user_id), $session_id);
		$this->remove_session_from_allsessions($session_id);
		$this->cache->_delete(self::session_prefix . $session_id);
	}

	function num_active_sessions($minutes_considered_active)
	{
		// Go through all_sessions and add up num_active_sessions
		$sessions = $this->cache->get(self::all_sessions_key);
		$initial = 0;
		return array_reduce(array_values($sessions),
			function ($time, $result) use ($minutes_considered_active)
			{
				// Add 1 to result and return if time is within minutes_considered_active. Otherwise add 0 and return.
				return $result + ($time < $minutes_considered_active);
			},
			$initial);
	}

	function unset_admin($session_id)
	{
		$this->cache->atomic_operation($session_id,
			function($session_data)
			{
				$session_data['session_admin'] = 0;
				return $session_data;
			}
		);
	}

	/** Update the time used in session storage
	 *
	 * @param int $time_now - Time to use in queries
	 *
	 * @return null
	 */
	function set_time_now($time_now)
	{
		$this->time_now = $time_now;
	}

	// Compatibility with the native drivers
	function set_db($db)
	{
	}

	/**
	 * Completely delete all session data being used for phpbb
	 */
	function delete_all_sessions()
	{
		// You can't do operations inside an atomic_operation,
		// So make array and add to it instead
		$sessions_to_delete = $this->cache->_read(self::all_sessions_key);
		foreach(array_keys($sessions_to_delete) as $session_id)
		{
			$this->delete($session_id);
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
		$session = $this->get_session($session_id);
		if (!is_null($session) && array_key_exists('session_ip', $session))
		{
			return $session['session_ip'];
		}
		return null;
	}

	public function get_newest_session_id($user_id)
	{
		$sessions = $this->get_user_sessions($user_id);
		$newest = null;
		$newest_time = 0;
		if (is_array($sessions))
		{
			foreach($sessions as $session)
			{
				if ($session['session_time'] > $newest_time)
				{
					$newest = $session;
					$newest_time = $newest['session_time'];
				}
			}
		}
		return $newest;
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
		$sessions = $this->get_user_sessions($user_id);
		$newest = null;
		$newest_time = 0;
		if (is_array($sessions))
		{
			foreach($sessions as $session)
			{
				if ($session['session_time'] > $newest_time)
				{
					$newest = $session;
					$newest_time = $newest['session_time'];
				}
			}
		}
		if (is_array($newest))
		{
			$user_data = $this->db_user->get_user_info($user_id);
			return array_merge($newest, $user_data);
		}
		return null;
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
		$sessions = $this->get_user_sessions($user_id);
		$longest = null;
		$longest_time = 0;
		$viewonline = true;
		if (is_array($sessions))
		{
			foreach($sessions as $session)
			{
				$session_length = ($session['session_time'] - $session['session_start']);
				// Set visible to false if this session or a previous session is hidden
				$viewonline = ($session['session_viewonline'] && $viewonline);
				if ($session_length > $longest_time)
				{
					$longest = $session;
					$longest_time = $session_length;
				}
			}
		}
		if (is_array($longest))
		{
			return array(
				'user_id' => $user_id,
				'online_time' => $longest_time,
				'viewonline' => $viewonline,
			);
		}
		return null;
	}

	/**
	 * Queries the session table to get information about online users
	 *
	 * @param int    $item_id Limits the search to the item with this id
	 * @param string $item    The name of the item which is stored in the session table as session_{$item}_id
	 *
	 * @return array An array containing the ids of online, hidden and visible users, as well as statistical info
	 */
	function obtain_users_online($item_id = 0, $item = 'forum')
	{
		// TODO: Implement obtain_users_online() method.
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
		// Undocumented logic taken from native driver
		$time = (time() - (intval($config['load_online_time']) * 60));
		$time = (int) ($time - ((int) ($time % 60)));
		$skip_item_id = $item_id === 0;

		$guest_sessions = ($this->map_all(
			function($session) use ($item, $skip_item_id, $item_id, $time)
			{
				if ($session['session_viewonline'] &&
					($skip_item_id || $session["session_${item}_id"] == $item_id) &&
					$session['session_time'] >= $time)
				{
					return $session['session_ip'];
				}
				return null;
			},
			ANONYMOUS)
		);
		// Remove sessions that didn't match
		$guest_sessions = array_filter($guest_sessions, 'is_string');
		// Return a count of the guest sessions with unique ip addresses
		return count(array_unique($guest_sessions));
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
	function get_users_online($show_guests, $online_time, $order_by, $phpbb_dispatcher)
	{
		// TODO: Implement get_users_online() method.
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
		// TODO: Implement map_users_online() method.
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
		foreach($user_list as $user)
		{
			$this->map_all($function, $user);
		}
	}

	/** Get sessions associated with user_id sorted by newest
	 *
	 * @param int $user_id
	 * @return array session data
	 */
	function get_newest($user_id)
	{
		return $this->get_newest_session($user_id);
	}

	/** Delete all session associated with user_id
	 *
	 * @param int $user_id
	 */
	function delete_by_user_id($user_id)
	{
		$this->cleanup_expired_sessions(array($user_id), INF);
	}

	/** Return count of sessions associated with user_id within max_time
	 *
	 * @param int $user_id
	 * @param int $max_time
	 * @return int number of sessions for user_id within max_time
	 */
	function num_sessions($user_id, $max_time)
	{
		return array_sum($this->map_recently_expired($max_time, function($session) {
			return 1;
		}, $user_id));
	}

	/** Get session & user data associated with user_id
	 *
	 * @param int $user_id
	 * @return array
	 */
	function get_with_user_id($user_id)
	{
		return $this->get_newest_session($user_id);
	}

	/** Set user session visibility
	 *
	 * @param int  $user_id    sessions with user_id to change
	 * @param bool $viewonline true: set visible, false: set invisible
	 */
	function set_viewonline($user_id, $viewonline)
	{
		$session_id = $this->get_newest_session_id($user_id);
		$this->cache->atomic_operation($session_id, function ($session) use ($viewonline) {
			$session['viewonline'] = $viewonline;
			return $session;
		});
	}

	/** Remove from storage all guest sessions older than session_length
	 *
	 * @param int $session_length (in seconds) remove sessions older than time - session_length
	 * @return null
	 */
	function cleanup_guest_sessions($session_length)
	{
		$this->map_recently_expired($session_length, function ($session) {
			if($session['user_id'] == ANONYMOUS)
			{
				$this->delete($session['session_id']);
			}
		});
	}

	/** Remove from storage all sessions older than session_length
	 *
	 * If $user_ids is empty, nothing happens.
	 *
	 * @param array $user_ids
	 * @param int   $session_length (in seconds) remove sessions older than time - session_length
	 * @return null
	 */
	function cleanup_expired_sessions(array $user_ids, $session_length)
	{
		$sessions_to_delete = array();
		foreach($user_ids as $user_id)
		{
			$results = $this->map_recently_expired($session_length,
				function ($session)
				{
					return $session['session_id'];
				}, 99, $user_id
			);
			$sessions_to_delete = array_merge($sessions_to_delete, $results);
		}
		foreach($sessions_to_delete as $session_id)
		{
			$this->delete($session_id);
		}
	}

	/** For sessions older than length, run a function and collect results.
	 *
	 * @param int     $current_session_length   how old to search
	 * @param Closure $session_function function to run takes $row, outputs array
	 * @param int     $batch_size       Sql Paging size
	 * @param int	  $user_id			(Optional) If given, only map over this users sessions
	 * @return array an array containing the results of $session_function
	 */
	function map_recently_expired($session_length, Closure $session_function, $batch_size = 99, $user_id = false)
	{
		if ($user_id === false)
		{
			$sessions = $this->cache->_read(self::all_sessions_key);
		}
		else
		{
			$sessions = $this->get_user_sessions($user_id);
		}
		$results = array();
		if (is_array($sessions)) {
			foreach($sessions as $session_id => $session_time)
			{
				$current_session_length = (time() - $session_time);
				if ($current_session_length > $session_length)
				{
					$results[] = $session_function($this->get_session($session_id));
				}
			}
		}
		return $results;
	}

	/**
	* Map all sessions (or only user_id sessions, if given)
	*/
	function map_all($session_function, $user_id = false)
	{
		return $this->map_recently_expired(-INF, $session_function, 99, $user_id);
	}
}
