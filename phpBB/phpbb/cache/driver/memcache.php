<?php
/**
*
* @package acm
* @copyright (c) 2005, 2009 phpBB Group
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

/**
* ACM for Memcached
* @package acm
*/
class phpbb_cache_driver_memcache extends phpbb_cache_driver_memory implements phpbb_cache_driver_atomic_interface
{
	var $extension = 'memcache';

	var $memcache;
	var $flags = 0;

	function __construct()
	{
		// Call the parent constructor
		parent::__construct();

		$this->memcache = new Memcache;
		foreach(explode(',', PHPBB_ACM_MEMCACHE) as $u)
		{
			$parts = explode('/', $u);
			$this->memcache->addServer(trim($parts[0]), trim($parts[1]));
		}
		$this->flags = (PHPBB_ACM_MEMCACHE_COMPRESS) ? MEMCACHE_COMPRESSED : 0;
	}

	/**
	* Unload the cache resources
	*
	* @return null
	*/
	function unload()
	{
		parent::unload();

		$this->memcache->close();
	}

	/**
	* Purge cache data
	*
	* @return null
	*/
	function purge()
	{
		$this->memcache->flush();

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
		return $this->memcache->get($this->key_prefix . $var);
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
		if (!$this->memcache->replace($this->key_prefix . $var, $data, $this->flags, $ttl))
		{
			return $this->memcache->set($this->key_prefix . $var, $data, $this->flags, $ttl);
		}
		return true;
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
		return $this->memcache->delete($this->key_prefix . $var);
	}
	
	function atomic_operation($key, Closure $operation, $retry_usleep_time = 10000, $retries=0)
	{
		if ($retries > 9)
		{
			trigger_error('An error occurred processing session data.');
		}
		$flags = 0;
		$cas = null;
		$data = memcache_get($this->memcache, $this->key_prefix . $key, $flags, $cas);
		if ($data === false)
		{
			return;
		}
		$ret = memcache_cas($this->memcache, $this->key_prefix . $key, $operation($data), $flags, 0, $cas);
		if ($ret === false)
		{
			usleep($retry_usleep_time);
			$this->atomic_operation($key, $operation, $retry_usleep_time, $retries+1);
		}
	}
}
