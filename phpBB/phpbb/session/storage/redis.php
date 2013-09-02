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

if (!defined('PHPBB_ACM_REDIS_PORT'))
{
	define('PHPBB_ACM_REDIS_PORT', 6379);
}

if (!defined('PHPBB_ACM_REDIS_HOST'))
{
	define('PHPBB_ACM_REDIS_HOST', 'localhost');
}

class phpbb_session_storage_redis implements phpbb_session_storage_interface_session
{
	protected $native;
	protected $db;
	protected $time_now;
	protected $redis;
	const all_sessions = "ALL_SESSIONS";

	function __construct($db, $time)
	{
		$this->db = $db;
		$this->native = new phpbb_session_storage_native($db, time());
		$this->time_now = $time;
		$this->redis = new Redis();
		$ok = $this->redis->connect(PHPBB_ACM_REDIS_HOST, PHPBB_ACM_REDIS_PORT);
		if (!$ok)
		{
			trigger_error('Could not connect to redis server');
		}
		$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
	}

	function set_time_now($time_now)
	{
		$this->time_now = $time_now;
	}

	function set_db($db)
	{
		$this->db = $db;
		$this->native = new phpbb_session_storage_native($db, time());
	}

	function create($session_data)
	{
		$this->redis->set($session_data['session_id'], $session_data);
	}

	function update($session_id, $session_data)
	{
		$this->redis->set($session_id, $session_data);
	}

	function get($session_id)
	{
		$session_data = $this->redis->get($session_id);
		$session_user = $this->native->get_user_info((int) $session_data['session_user_id']);
		return array_merge($session_data, $session_user);
	}

	function delete($session_id = false, $user_id = false)
	{
		$this->redis->delete($session_id);
	}

	protected function get_all_users()
	{
		return $this->redis->zrange(self::all_sessions, 0, -1);
	}

	protected function add_to_all_users($expire, $value)
	{
		$this->redis->zadd(self::all_sessions, $expire, $value);
	}

	protected function remove_from_all_users($value)
	{
		$this->redis->zrem(self::all_sessions, $value);
	}

	function delete_all_sessions()
	{
		foreach($this->get_all_users() as $user)
		{
			$this->redis->del($user);
		}
		$this->redis->del(self::all_sessions);
	}

	function get_user_ip_from_session($session_id)
	{
		$session = $this->redis->get($session_id);
		return $session['session_ip'];
	}

	function get_newest_session($user_id)
	{
		$session = $this->redis->zRevRangeByScore(
			"USER_{$user_id}",
			'+inf',
			'-inf',
			array('limit' => array(0, 1))
		);
		return $session[0];
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

	/** Count number of active sessions on board
	 *
	 * @param int $minutes_considered_active
	 * @return int Count of number of active sessions that
	 *         where active within the last $minutes_considered_active
	 *           (default: 60)
	 */
	function num_active_sessions($minutes_considered_active)
	{
		// TODO: Implement num_active_sessions() method.
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
		// TODO: Implement get_users_online_totals() method.
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
	function get_user_list($show_guests, $online_time, $order_by, $phpbb_dispatcher)
	{
		// TODO: Implement get_user_list() method.
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

	/** Remove admin status on session associated with session_id
	 *
	 * @param String $session_id session_id to remove
	 */
	function unset_admin($session_id)
	{
		// TODO: Implement unset_admin() method.
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
	}
}
