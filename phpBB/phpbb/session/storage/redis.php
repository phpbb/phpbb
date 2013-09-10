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

	protected function add_to($key, $expire, $value)
	{
		$this->redis->zadd($key, $expire, $value);
	}

	protected function remove_from($key, $value)
	{
		$this->redis->zrem($key, $value);
	}

	protected function add_to_all_users($expire, $value)
	{
		$this->add_to(self::all_sessions, $expire, $value);
	}

	protected function remove_from_all_users($value)
	{
		$this->remove_from(self::all_sessions, $value);
	}

	protected function user_key($user)
	{
		return "USER_$user";
	}

	protected function add_to_user($user, $expire, $value)
	{
		$this->add_to($this->user_key($user), $expire, $value);
	}

	protected function remove_from_user($user, $value)
	{
		$this->remove_from($this->user_key($user), $value);
	}

	protected function update_expires($session_id, $session_user, $expires)
	{
		// Works because Redis zadd command updates score if already exists
		$this->add_to_all_users($expires, $session_id);
		$this->add_to_user($session_user, $expires, $session_id);
	}

	function create($session_data)
	{
		$id = $session_data['session_id'];
		$expire = $session_data['session_time'];
		$user = $session_data['session_user_id'];
		$this->redis->set($id, $session_data);
		$this->add_to_all_users($expire, $id);
		$this->add_to_user($user, $expire, $id);
	}

	protected function atomic_operation($key, Closure $operation, $retry_usleep_time = 10000, $retries=0)
	{
		if ($retries > 9)
		{
			trigger_error('An error occurred processing session data.');
		}
		$this->redis->watch($key);
		$data = $this->redis->get($key);
		if ($data === false)
		{
			return;
		}
		if (is_null($data))
		{
			$data = array();
		}
		$ret = $this->redis->multi()
			->set($key, $operation($data))
			->exec();
		if ($ret === false)
		{
			usleep($retry_usleep_time);
			$this->atomic_operation($key, $operation, $retry_usleep_time, $retries+1);
		}
		$this->redis->unwatch();
	}

	function update($session_id, $session_data_to_change)
	{
		$update_session_time = false;
		$update_session_time_user = false;
		$this->atomic_operation($session_id,
			function($session_data) use ($session_data_to_change, &$update_session_time, &$update_session_time_user)
			{
				// If session time is updated, other procedures may need to be run as well
				if (array_key_exists('session_time', $session_data_to_change) &&
					is_int($session_data_to_change['session_time']))
				{
					$update_session_time = $session_data_to_change['session_time'];
					$update_session_time_user = $session_data['session_user_id'];
				}
				return $session_data_to_change + $session_data;
			}
		);
		// Procedure cannot be combined inside the atomic_operation
		// because storage engines often cannot execute commands while they are
		// performing atomic operations.
		if ($update_session_time !== false && $update_session_time_user !== false)
		{
			$this->update_expires($session_id, $update_session_time_user, $update_session_time);
		}
	}

	function get($session_id)
	{
		$session_data = $this->redis->get($session_id);
		$session_user = $this->native->get_user_info((int) $session_data['session_user_id']);
		if(!is_array($session_data) || !is_array($session_user))
		{
			return false;
		}
		return array_merge($session_data, $session_user);
	}

	function get_session_data($session_id)
	{
		return $this->redis->get($session_id);
	}

	function delete($session_id = false, $user_id = false)
	{
		if ($user_id === false)
		{
			$session = $this->get_session_data($session_id);
			$user_id = $session['session_user_id'];
		}
		$this->redis->delete($session_id);
		$this->remove_from_all_users($session_id);
		$this->remove_from_user($user_id, $session_id);
	}

	protected function get_all_ids($min='-inf', $max='+inf')
	{
		if ($min === '-inf' && $max === '+inf')
		{
			return $this->redis->zrange(self::all_sessions, 0, -1);
		}
		else
		{
			return $this->redis->zRevRangeByScore(
				self::all_sessions,
				$max,
				$min
			);
		}
	}

	protected function get_all($min='-inf', $max='+inf')
	{
		return array_map(array($this, 'get_session_data'), $this->get_all_ids($min, $max));
	}

	function map_all(Closure $function)
	{
		return array_map($function, $this->get_all());
	}

	protected function get_all_non_guests($min='-inf', $max='+inf')
	{
		return array_filter($this->get_all($min, $max),
			function ($session) {
				return $session['session_user_id'] != ANONYMOUS;
			});
	}

	protected function get_all_guests($min='-inf', $max='+inf')
	{
		return array_filter($this->get_all($min, $max),
			function ($session) {
				return $session['session_user_id'] == ANONYMOUS;
			});
	}

	// Though it would be faster to let Redis handle the expire
	// detail as the sorted set key, and simply replace the key as it comes
	// out of redis with a the withscores=>true option, this isn't possible
	// because of the way the redis driver handles serialized arrays.
	// (Basically withscores flips the array's keys/values and would make
	//  the session data the keys of the array itself, which isn't possible in php)
	protected function get_user_sessions($user_id, $min='-inf', $max='+inf')
	{
		return array_map(array($this, 'get_session_data'), $this->redis->zRevRangeByScore(
			"USER_{$user_id}",
			$max,
			$min,
			array('limit' => array(0, 1))
		));
	}

	protected function get_newest_session_id($user_id)
	{
		$session = $this->redis->zRevRangeByScore(
			"USER_{$user_id}",
			'+inf',
			'-inf',
			array('limit' => array(0, 1))
		);
		return $session[0];
	}

	function get_newest_session($user_id)
	{
		return $this->get_session_data($this->get_newest_session_id($user_id));
	}

	function delete_all_sessions()
	{
		foreach($this->get_all() as $session)
		{
			$this->delete($session['session_id']);
		}
	}

	function get_user_ip_from_session($session_id)
	{
		$session = $this->redis->get($session_id);
		return $session['session_ip'];
	}

	function get_user_online_time($user_id)
	{
		$session_time = array();
		$viewonline = array(true);
		foreach($this->get_user_sessions($user_id) as $session)
		{
			$session_time[] = $session['session_time'];
			$viewonline[] = $session['session_viewonline'];
		}
		return array(
			'session_user_id' => $user_id,
			'online_time' 	=> max($session_time),
			'viewonline' 	=> min($viewonline),
		);
	}

	function num_active_sessions($minutes_considered_active)
	{
		return $this->redis->zCount(self::all_sessions, $this->time_now - $minutes_considered_active, '+inf');
	}

	function get_users_online_totals($item_id = 0, $item = 'forum')
	{
		global $config;
		$time = (time() - (intval($config['load_online_time']) * 60));
		$time = ($time - ((int) ($time % 30)));
		$sessions = $this->get_all_non_guests($time);

		// Filter sessions if criteria is set
		if ($item_id !== 0)
		{
			$sessions = array_filter($sessions,
				function($session) use ($item_id, $item)
				{
					return $session["session_${item}_id"] == $item_id;
				}
			);
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
			$online_users['guests_online'] = $this->obtain_guest_count($item_id, $item);
		}

		foreach($sessions as $session)
		{
			// Skip multiple sessions for one user
			if (!isset($online_users['online_users'][$session['session_user_id']]))
			{
				$online_users['online_users'][$session['session_user_id']] = (int) $session['session_user_id'];
				if ($session['session_viewonline'])
				{
					$online_users['visible_online']++;
				}
				else
				{
					$online_users['hidden_users'][$session['session_user_id']] = (int) $session['session_user_id'];
					$online_users['hidden_online']++;
				}
			}
		}
		$online_users['total_online'] = $online_users['guests_online'] + $online_users['visible_online'] + $online_users['hidden_online'];
		return $online_users;
	}

	function obtain_guest_count($item_id = 0, $item = 'forum')
	{
		global $config;
		// Undocumented logic taken from native driver
		$load_online_time_minutes = (intval($config['load_online_time']) * 60);
		$time_to_search = (time() - $load_online_time_minutes);
		$rounded_time_to_search = (int) ($time_to_search - ((int) ($time_to_search % 60)));

		$guest_sessions = array_map(
			function ($session) use ($item_id, $item)
			{
				if ($session['session_viewonline'] &&
					(!$item_id || $session["session_${item}_id"] == $item_id))
				{
					return $session['session_ip'];
				}
				return null;
			},
			$this->get_all_guests($rounded_time_to_search)
		);

		$guest_sessions = array_filter($guest_sessions, 'is_string');

		// Return a count of the guest sessions with unique ip addresses
		return count(array_unique($guest_sessions));
	}

	function get_user_list($show_guests, $online_time, $order_by, $phpbb_dispatcher)
	{
		$time = $this->time_now - $online_time;
		$sessions = $this->get_all_ids($time);

		if (!$show_guests)
		{
			$sessions = array_filter($sessions,
				function ($session) {
					return $session['session_user_id'] != ANONYMOUS;
				});
		}

		return array_map(array($this, 'get'), $sessions);
		// Sort sessions by order_by
		/*$order_by = trim(substr($order_by, 0, strlen($order_by)-3)); // Remove DEC or ASC
		uasort($sessions,
			function($a, $b) use ($order_by)
			{
				return $a[$order_by] < $b[$order_by];
			}
		);*/
	}

	function map_users_online($user_list, $session_length, Closure $function)
	{
		return $this->map_recently_expired($session_length,
			function($session) use ($user_list, $function)
			{
				if (in_array($session['session_user_id'], $user_list) )
				{
					$function($session);
				}
			},
			99
		);
	}

	function map_certain_users_with_time($user_list, Closure $function)
	{
		$this->map_users_online($user_list, 60, $function);
	}

	function unset_admin($session_id)
	{
		$this->update($session_id, array('session_admin'=>0));

	}

	function get_newest($user_id)
	{
		// They guaranteed sorted by newest by zRevRangeByScore
		return $this->get_user_sessions($user_id);
	}

	function delete_by_user_id($user_id)
	{
		foreach($this->get_user_sessions($user_id) as $session)
		{
			$this->delete($session['session_id']);
		}
	}

	function num_sessions($user_id, $max_time)
	{
		return count($this->get_user_sessions($user_id, $this->time_now - $max_time));
	}

	/** Get session & user data associated with user_id
	 *
	 * @param int $user_id
	 * @return array
	 */
	function get_with_user_id($user_id)
	{
		return $this->get($this->get_newest_session_id($user_id));
	}

	function set_viewonline($user_id, $viewonline)
	{
		$this->update($this->get_newest_session_id($user_id), array('session_viewonline'=>$viewonline));
	}

	function cleanup_guest_sessions($session_length)
	{
		$host = $this;
		$this->map_recently_expired($session_length,
			function($session) use ($host)
			{
				if ($session['session_user_id'] == ANONYMOUS)
				{
					$host->delete($session['session_id']);
				}
			}
		);
	}

	function cleanup_expired_sessions(array $user_ids, $session_length)
	{
		$host = $this;
		$this->map_recently_expired($session_length,
			function($session) use ($user_ids, $host)
			{
				if (in_array($session['session_user_id'], $user_ids) )
				{
					$host->delete($session['session_id']);
				}
			}
		);
	}

	function map_recently_expired($session_length, Closure $session_function, $batch_size=99, $ids_only=false)
	{
		$time = $this->time_now - $session_length;
		$sessions = $ids_only ? $this->get_all_ids('-inf', $time) : $this->get_all('-inf', $time);
		return array_map($session_function, $sessions);
	}
}
