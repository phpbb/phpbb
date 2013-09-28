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

// Include the abstract base
if (!class_exists('acm_memory'))
{
	require("{$phpbb_root_path}includes/acm/acm_memory.$phpEx");
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
class acm extends acm_memory
{
	var $extension = 'redis';

	var $redis;

	function acm()
	{
		// Call the parent constructor
		parent::acm_memory();

		$this->redis = new Redis();
		$this->redis->connect(PHPBB_ACM_REDIS_HOST, PHPBB_ACM_REDIS_PORT);

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
}
