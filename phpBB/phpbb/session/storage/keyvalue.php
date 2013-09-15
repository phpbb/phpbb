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

/** Shared Session Storage functions that are common to
*   Key/Value stores such as Redis or Memcache.
*/
abstract class phpbb_session_storage_keyvalue
{
	protected $db_user;
	protected $db;
	protected $time_now;
	const all_sessions = "ALL_SESSIONS";

	function __construct($db, $time, phpbb_session_storage_interface_user $db_user)
	{
		$this->db = $db;
		$this->db_user = $db_user;
		$this->time_now = $time;
	}

	function set_time_now($time_now)
	{
		$this->time_now = $time_now;
		$this->db_user->set_time_now($time_now);
	}

	function set_db(phpbb_db_driver $db)
	{
		$this->db = $db;
		$this->db_user->set_db($db);
	}

	// Abstract methods that must be implemented by each implementer
	abstract protected function add_to($key, $expire, $value);

	abstract protected function remove_from($key, $value);

	abstract protected function atomic_operation($key, Closure $operation, $retry_usleep_time = 10000, $retries = 0);

	abstract protected function set_session_data($session_id, $data);

	abstract protected function delete_session($session_id);

	abstract function get_session_data($session_id);
	
	abstract protected function get_all_ids($min = '-inf', $max = '+inf');
	
	abstract protected function get_user_sessions($user_id, $min = '-inf', $max = '+inf');

	abstract protected function get_user_data_and_newest_session_id($user_id);
	
	// The rest of these functions use the functions above
	// (or each other) to perform different types of queries
	// on the session data.
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
		$this->add_to_all_users($expires, $session_id);
		$this->add_to_user($session_user, $expires, $session_id);
	}

	function create($session_data)
	{
		$id = $session_data['session_id'];
		$expire = $session_data['session_time'];
		$user = $session_data['session_user_id'];
		
		// Key/Value stores contain the session data and 2 'indicies' (of sorts)
		// on the data.
		
		// Session_id -> array session_data.
		// This is the one that contains all the actual session data
		$this->set_session_data($id, $session_data);
		// ALL_SESSIONS -> array [session_id] => [session_time]
		// this one contains all of the sessions that exist
		// in the system (that haven't been cleaned up yet essentially)
		// Useful for finding all users logged in, removing all, etc.
		$this->add_to_all_users($expire, $id);
		// USER_{$user_id} -> array [session_id] => [session_time]
		// this one contains all the sessions that exist
		// for a user id. Useful for finding the newest
		// session or finding visibility.
		$this->add_to_user($user, $expire, $id);
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

	function get_session_and_user_data($session_id)
	{
		$session_data = $this->get_session_data($session_id);
		$session_user = $this->db_user->get_user_info((int) $session_data['session_user_id']);
		if(!is_array($session_data) || !is_array($session_user))
		{
			return false;
		}
		return array_merge($session_data, $session_user);
	}
	
	function delete($session_id = false, $user_id = false)
	{
		if ($user_id === false)
		{
			$session = $this->get_session_data($session_id);
			$user_id = $session['session_user_id'];
		}
		$this->delete_session($session_id);
		$this->remove_from_all_users($session_id);
		$this->remove_from_user($user_id, $session_id);
	}

	protected function get_all($min = '-inf', $max = '+inf')
	{
		return array_map(array($this, 'get_session_data'), $this->get_all_ids($min, $max));
	}

	function map_all(Closure $function)
	{
		return array_map($function, $this->get_all());
	}

	protected function get_all_non_guests($min = '-inf', $max = '+inf')
	{
		return array_filter($this->get_all($min, $max),
			function ($session)
			{
				return $session['session_user_id'] != ANONYMOUS;
			}
		);
	}

	protected function get_all_guests($min = '-inf', $max = '+inf')
	{
		return array_filter($this->get_all($min, $max),
			function ($session)
			{
				return $session['session_user_id'] == ANONYMOUS;
			}
		);
	}

	function get_user_data_and_newest_session($user_id)
	{
		return $this->get_session_and_user_data($this->get_newest_session_id($user_id));
	}

	// Basically just an alias
	function get_session_and_user_data_with_id($user_id)
	{
		return $this->get_user_data_and_newest_session($user_id);
	}

	function delete_all_sessions()
	{
		foreach($this->get_all_ids() as $session_id)
		{
			$this->delete($session_id);
		}
	}

	function get_user_ip_from_session($session_id)
	{
		$session = $this->get_session_data($session_id);
		return $session['session_ip'];
	}

	function get_all_users_time_visibility($user_id)
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
				function ($session)
				{
					return $session['session_user_id'] != ANONYMOUS;
				}
			);
		}

		// Sort sessions by order_by
		if ($order_by != 'session_time')
		{
			// Remove DEC or ASC at the end of order_by
			$order_by = trim(substr($order_by, 0, strlen($order_by)-3));
			uasort($sessions,
				function($a, $b) use ($order_by)
				{
					return $a[$order_by] < $b[$order_by];
				}
			);
		}
		return array_map(array($this, 'get'), $sessions);
	}

	function map_users_online($user_list, $session_length, Closure $function)
	{
		$time_allowed = $this->time_now - $session_length;
		$get_visable_older_than =
			function($session) use ($time_allowed, $function)
			{
				if ($session['onlinetime'] < $time_allowed &&
				    $session['viewonline'])
				{
					return $function(array(
							'session_user_id' => $session['session_user_id'],
							'session_time' => $session['onlinetime'],
						)
					);
				}
				return null;
			};
		$sessions = $this->map_certain_users_with_time($user_list, $get_visable_older_than);
		// Don't return values that are null
		return array_filter($sessions, function($v) {return !is_null($v);});
	}

	function map_certain_users_with_time($user_list, Closure $function)
	{
		return array_map($function, array_map(array($this, 'get_all_users_time_visibility'), $user_list));
	}

	function unset_admin($session_id)
	{
		$this->update($session_id, array('session_admin'=>0));

	}

	function get_newest($user_id)
	{
		return $this->get_user_sessions($user_id);
	}

	function delete_by_user_id($user_id)
	{
		foreach($this->get_user_sessions($user_id) as $session)
		{
			$this->delete($session['session_id']);
		}
	}

	function num_active_sessions_for_user($user_id, $max_time)
	{
		return count($this->get_user_sessions($user_id, $this->time_now - $max_time));
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

	function cleanup_certain_expired_sessions(array $user_ids, $session_length)
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

  // batch_size is here because it matches the interface
  // but it isn't used here (it's only used in the native sql driver).
	function map_recently_expired($session_length, Closure $session_function, $batch_size = 99, $ids_only = false)
	{
		$time = $this->time_now - $session_length;
		$sessions = $ids_only ? $this->get_all_ids('-inf', $time) : $this->get_all('-inf', $time);
		return array_map($session_function, $sessions);
	}

	function map_friends_online($user_id, Closure $function)
	{
	  $friends = $this->db_user->get_friends($user_id);
		$processed_friends = array_map(
      function($friend)
      {
        $visibility = array();
        $online_time = array();
        foreach($this->get_user_sessions($friend['user_id']) as $friend_session)
        {
          $visibility = $friend_session['session_viewonline'];
          $online_time = $friend_session['session_time'];
        }
        $friend += array
        (
          'viewonline' => min($visibility),
          'online_time' => max($online_time),
        );
        return $friend;
      },
      $friends
    );
    
		return array_map($function, $processed_friends);
	}
}
