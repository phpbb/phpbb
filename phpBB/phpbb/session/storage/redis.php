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

class phpbb_session_storage_redis
	extends phpbb_session_storage_keyvalue
	implements phpbb_session_storage_interface_session
{
	protected $redis;
	const all_sessions = "ALL_SESSIONS";

	function __construct($db, $time, phpbb_session_storage_interface_user $db_user)
	{
		parent::__construct($db, $time, $db_user);
		$this->redis = new Redis();
		$ok = $this->redis->connect(PHPBB_ACM_REDIS_HOST, PHPBB_ACM_REDIS_PORT);
		if (!$ok)
		{
			trigger_error('Could not connect to redis server');
		}
		$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
	}

	protected function add_to($key, $expire, $value)
	{
		$this->redis->zadd($key, $expire, $value);
	}

	protected function remove_from($key, $value)
	{
		$this->redis->zrem($key, $value);
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

	protected function set_session_data($session_id, $data)
	{
		$this->redis->set($session_id, $data);
	}

	protected function delete_session($session_id)
	{
		$this->redis->delete($session_id);
	}

	function get_session_data($session_id)
	{
		return $this->redis->get($session_id);
	}

	function num_active_sessions($minutes_considered_active)
	{
		return $this->redis->zCount(self::all_sessions, $this->time_now - $minutes_considered_active, '+inf');
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
}
