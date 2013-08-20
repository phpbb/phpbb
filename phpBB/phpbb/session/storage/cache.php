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


class phpbb_session_storage_storage_cache implements phpbb_session_storage_interface_session
{
	protected $cache;
	const all_caches_key = 'ALL_SESSIONS';
	const user_id_prefix = 'SESSION_USER_';

	function __construct(phpbb_cache_driver_atomic_interface $cache_driver, $db, $time)
	{
		parent::__construct($db, $time);
		$this->cache = $cache_driver;
	}

	protected function add_to($key, $session_id)
	{
		$this->cache->atomic_operation($key,
			function ($sessions) use ($session_id)
			{
				$sessions[] = $session_id = 1;
				return $sessions;
			}
		);
	}

	protected function remove_from($key, $session_id)
	{
		$this->cache->atomic_operation($key,
			function ($sessions) use ($session_id)
			{
				unset($sessions[$session_id]);
				return $sessions;
			}
		);
	}

	protected function get_count($key)
	{
		return count($this->cache->get($key));
	}

	protected function user_key($user_id)
	{
		return self::user_id_prefix.$user_id;
	}

	function create($session_data)
	{
		$user_key = self::user_key($session_data['session_user_id']);
		$id = $session_data['session_id'];

		$this->add_to($user_key, $id);
		$this->cache->put($id, $session_data);
	}

	function update($session_id, $session_data)
	{
		$this->cache->put($session_id, $session_data);
	}

	function get($session_id)
	{
		return $this->cache->get($session_id);
	}

	function delete($session_id, $user_id = false)
	{
		if ($user_id !== false)
		{
			$session = $this->cache->get($session_id);
			if ($session['session_user_id'] != $user_id)
			{
				// Does not match, do not destroy
				return;
			}
		}
		else
		{
			$session = $this->cache->get($session_id);
			$user_id = $session['session_user_id'];
		}
		$this->remove_from($this->user_key($user_id), $session_id);
		$this->cache->destroy($session_id);
	}

	function num_active_sessions($minutes_considered_active)
	{
		// Doesn't actually use minutes_considered_active, just
		//   counts and hopes garbage collection is working properly
		// Alternate not-implemented-yet solution:
		// - Go through sessions in all_sessions_key
		// - Check expire date
		// - Put in key
		// - Use this key until it expires (every minute or w/e)
		return $this->get_all(self::all_caches_key);
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
		// TODO: Implement set_time_now() method.
	}

	/**
	 * Completely delete all session data being used for phpbb
	 */
	function delete_all_sessions()
	{
		// TODO: Implement delete_all_sessions() method.
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
		// TODO: Implement get_user_ip_from_session() method.
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
		// TODO: Implement get_newest_session() method.
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
		// TODO: Implement get_user_online_time() method.
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
		// TODO: Implement obtain_guest_count() method.
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
		// TODO: Implement map_certain_users_with_time() method.
	}

	/** Get sessions associated with user_id sorted by newest
	 *
	 * @param int $user_id
	 * @return array session data
	 */
	function get_newest($user_id)
	{
		// TODO: Implement get_newest() method.
	}

	/** Delete all session associated with user_id
	 *
	 * @param int $user_id
	 */
	function delete_by_user_id($user_id)
	{
		// TODO: Implement delete_by_user_id() method.
	}

	/** Return count of sessions associated with user_id within max_time
	 *
	 * @param int $user_id
	 * @param int $max_time
	 * @return int number of sessions for user_id within max_time
	 */
	function num_sessions($user_id, $max_time)
	{
		// TODO: Implement num_sessions() method.
	}

	/** Get session & user data associated with user_id
	 *
	 * @param int $user_id
	 * @return array
	 */
	function get_with_user_id($user_id)
	{
		// TODO: Implement get_with_user_id() method.
	}

	/** Set user session visibility
	 *
	 * @param int  $user_id    sessions with user_id to change
	 * @param bool $viewonline true: set visible, false: set invisible
	 */
	function set_viewonline($user_id, $viewonline)
	{
		// TODO: Implement set_viewonline() method.
	}

	/** Remove from storage all guest sessions older than session_length
	 *
	 * @param int $session_length (in seconds) remove sessions older than time - session_length
	 * @return null
	 */
	function cleanup_guest_sessions($session_length)
	{
		// TODO: Implement cleanup_guest_sessions() method.
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
		// TODO: Implement cleanup_expired_sessions() method.
	}

	/** For sessions older than length, run a function and collect results.
	 *
	 * @param int     $session_length   how old to search
	 * @param Closure $session_function function to run takes $row, outputs array
	 * @param int     $batch_size       Sql Paging size
	 * @return array an array containing the results of $session_function
	 */
	function map_recently_expired($session_length, Closure $session_function, $batch_size)
	{
		// TODO: Implement map_recently_expired() method.
}}
