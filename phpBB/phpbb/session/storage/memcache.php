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

if (!defined('PHPBB_ACM_MEMCACHE_PORT'))
{
	define('PHPBB_ACM_MEMCACHE_PORT', 11211);
}

if (!defined('PHPBB_ACM_MEMCACHE_COMPRESS'))
{
	define('PHPBB_ACM_MEMCACHE_COMPRESS', false);
}

if (!defined('PHPBB_ACM_MEMCACHE_HOST'))
{
	define('PHPBB_ACM_MEMCACHE_HOST', 'localhost');
}

if (!defined('PHPBB_ACM_MEMCACHE'))
{
	//can define multiple servers with host1/port1,host2/port2 format
	define('PHPBB_ACM_MEMCACHE', PHPBB_ACM_MEMCACHE_HOST . '/' . PHPBB_ACM_MEMCACHE_PORT);
}

class phpbb_session_storage_memcache
	extends phpbb_session_storage_keyvalue
	implements phpbb_session_storage_interface_session
{
	protected $memcache;
	const all_sessions = "ALL_SESSIONS";

	function __construct($db, $time, phpbb_session_storage_interface_user $db_user)
	{
		parent::__construct($db, $time, $db_user);
		$this->memcache = new Memcache;
		foreach(explode(',', PHPBB_ACM_MEMCACHE) as $u)
		{
			$parts = explode('/', $u);
			$this->memcache->addServer(trim($parts[0]), trim($parts[1]));
		}
		$this->flags = (PHPBB_ACM_MEMCACHE_COMPRESS) ? MEMCACHE_COMPRESSED : 0;
	}

	protected function add_to($key, $expire, $value)
	{
		$this->atomic_operation($key,
			function($data) use ($expire, $value) {
				$data[$value] = $expire;
				return $data;
			}
		);
	}

	protected function remove_from($key, $value)
	{
		$this->atomic_operation($key,
			function($data) use ($value) {
				unset($data[$value]);
				return $data;
			}
		);
	}

	protected function atomic_operation($key, Closure $operation, $retry_usleep_time = 10000, $retries=0)
	{
		if (!$key)
		{
			return;
		}
		if ($retries > 9)
		{
			trigger_error('An error occurred processing session data.');
		}
		$flags = 0;
		$cas = null;
		$data = memcache_get($this->memcache, $this->key_prefix . $key, $flags, $cas);
		if ($data === false)
		{
			$this->memcache->set($key, $operation(array()));
		}
		$ret = memcache_cas($this->memcache, $this->key_prefix . $key, $operation($data), null, 0, $cas);
		if ($ret === false)
		{
			usleep($retry_usleep_time);
			$this->atomic_operation($key, $operation, $retry_usleep_time, $retries+1);
		}
	}

	protected function set_session_data($session_id, $data)
	{
		$this->memcache->set($session_id, $data);
	}

	protected function delete_session($session_id)
	{
		if ($session_id)
		{
			$this->memcache->delete($session_id);
		}
	}

	function get_session_data($session_id)
	{
		if ($session_id)
		{
			return $this->memcache->get($session_id);
		}
	}

	function num_active_sessions($minutes_considered_active)
	{
		return count($this->get_key_limited_by_time(
			self::all_sessions,
			$this->time_now - $minutes_considered_active,
			+INF)
		);
	}

	protected function get_all_ids($min='-inf', $max='+inf')
	{
		return $this->get_key_limited_by_time(self::all_sessions, $min, $max);
	}

	protected function get_user_sessions($user_id, $min='-inf', $max='+inf')
	{
		return array_map(
			array($this, 'get_session_data'),
			$this->get_key_limited_by_time("USER_{$user_id}", $min, $max)
		);
	}

	protected function get_newest_session_id($user_id)
	{
		$sessions = $this->memcache->get("USER_{$user_id}");
		$sessions = $sessions ? $sessions : array();
		$session_keys = array_keys($sessions);
		return $session_keys[0];
	}

	protected function sort_by_time($values)
	{
		asort($values);
		return $values;
	}

	protected function get_key_limited_by_time($key, $min='-inf', $max='+inf', $limit=false)
	{
		$sessions = $this->memcache->get($key);
		if ($sessions === false)
		{
			$sessions = array();
		}

		if ($min != '-inf' || $max != '+inf')
		{
			$min = $min == '-inf' ? -INF : $min;
			$max = $max == '+inf' ? +INF : $max;
			foreach($sessions as $session_id => $session_time)
			{
				if (!($session_time > $min && $session_time < $max))
				{
					unset($sessions[$session_id]);
				};
			}
		}
		if ($limit !== false)
		{
			return array_keys(array_splice($sessions, 0, $limit));
		}
		else
		{
			return array_keys($sessions);
		}

	}
}
