<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\cache\driver;

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
*/
class redis extends \phpbb\cache\driver\memory
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

		$this->redis = new \Redis();

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

		$this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
		$this->redis->setOption(\Redis::OPT_PREFIX, $this->key_prefix);

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
	* {@inheritDoc}
	*/
	function unload()
	{
		parent::unload();

		$this->redis->close();
	}

	/**
	* {@inheritDoc}
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
