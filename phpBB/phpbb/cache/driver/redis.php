<?php
/**
*
* @package acm
* @copyright (c) 2011 phpBB Group
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

/**
* ACM for Redis
*
* Compatible with the php extension phpredis available
* at https://github.com/nicolasff/phpredis
*
* @package acm
*/
class phpbb_cache_driver_redis extends phpbb_cache_driver_memory implements phpbb_cache_driver_atomic_interface
{
	var $extension = 'redis';

	var $redis;

	/**
	* Creates a redis cache driver.
	*
	* The following global constants affect operation:
	*
	* PHPBB_ACM_REDIS_HOST
	* PHPBB_ACM_REDIS_PORT
	* PHPBB_ACM_REDIS_PASSWORD
	* PHPBB_ACM_REDIS_DB
	*
	* There are no publicly documented constructor parameters.
	*/
	function __construct()
	{
		// Call the parent constructor
		parent::__construct();

		$this->redis = new Redis();

		$args = func_get_args();
		if (!empty($args))
		{
			$ok = call_user_func_array(array($this->redis, 'connect'), $args);
		}
		else
		{
			$ok = $this->redis->connect(PHPBB_ACM_REDIS_HOST, PHPBB_ACM_REDIS_PORT);
		}

		if (!$ok)
		{
			trigger_error('Could not connect to redis server');
		}

		if (defined('PHPBB_ACM_REDIS_PASSWORD'))
		{
			if (!$this->redis->auth(PHPBB_ACM_REDIS_PASSWORD))
			{
				global $acm_type;

				trigger_error("Incorrect password for the ACM module $acm_type.", E_USER_ERROR);
			}
		}

		$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
		$this->redis->setOption(Redis::OPT_PREFIX, $this->key_prefix);

		if (defined('PHPBB_ACM_REDIS_DB'))
		{
			if (!$this->redis->select(PHPBB_ACM_REDIS_DB))
			{
				global $acm_type;

				trigger_error("Incorrect database for the ACM module $acm_type.", E_USER_ERROR);
			}
		}
	}

	/**
	* Unload the cache resources
	*
	* @return null
	*/
	function unload()
	{
		parent::unload();

		$this->redis->close();
	}

	/**
	* Purge cache data
	*
	* @return null
	*/
	function purge()
	{
		$this->redis->flushDB();

		parent::purge();
	}

	/**
	* Fetch an item from the cache
	*
	* @access protected
	* @param string $var Cache key
	* @return mixed Cached data
	*/
	function _read($var)
	{
		return $this->redis->get($var);
	}

	/**
	* Store data in the cache
	*
	* @access protected
	* @param string $var Cache key
	* @param mixed $data Data to store
	* @param int $ttl Time-to-live of cached data
	* @return bool True if the operation succeeded
	*/
	function _write($var, $data, $ttl = 2592000)
	{
		return $this->redis->setex($var, $ttl, $data);
	}

	/**
	* Remove an item from the cache
	*
	* @access protected
	* @param string $var Cache key
	* @return bool True if the operation succeeded
	*/
	function _delete($var)
	{
		if ($this->redis->delete($var) > 0)
		{
			return true;
		}
		return false;
	}

	function atomic_operation($key, Closure $operation, $retry_usleep_time = 10000, $retries=0)
	{
		if ($retries > 9)
		{
			trigger_error('An error occurred processing session data.');
		}
		$this->redis->watch($key);
		$value = $operation($this->redis->get($key));
		$ret = $this->redis->multi()
						->set($key, $value)
						->exec();
		if ($ret === false)
		{
			usleep($retry_usleep_time);
			self::atomic_operation($key, $operation, $retry_usleep_time = 10000, $retries+1);
		}
	}
}

