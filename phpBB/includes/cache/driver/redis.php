<?php
/**
*
* @package acm
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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

if (!defined('PHPBB_ACM_REDIS'))
{
	//can define multiple servers with host1/port1,host2/port2 format
	define('PHPBB_ACM_REDIS', PHPBB_ACM_REDIS_HOST . '/' . PHPBB_ACM_REDIS_PORT);
}

/**
* ACM for Redis
*
* Compatible with the php extension phpredis available
* at https://github.com/nicolasff/phpredis
*
* @package acm
*/
class phpbb_cache_driver_redis extends phpbb_cache_driver_memory
{
	var $extension = 'redis';

	var $redis;

	function __construct()
	{
		// Call the parent constructor
		parent::__construct();

		$this->redis = new Redis();
		foreach (explode(',', PHPBB_ACM_REDIS) as $server)
		{
			$parts = explode('/', $server);
			$this->redis->connect(trim($parts[0]), trim($parts[1]));
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
	* @return void
	*/
	function unload()
	{
		parent::unload();

		$this->redis->close();
	}

	/**
	* Purge cache data
	*
	* @return void
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
}

