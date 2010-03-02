<?php
/**
*
* @package acm
* @version $Id$
* @copyright (c) 2005, 2009 phpBB Group
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

/**
* ACM for XCache
* @package acm
*
* To use this module you need ini_get() enabled and the following INI settings configured as follows:
* - xcache.var_size > 0
* - xcache.admin.enable_auth = off (or xcache.admin.user and xcache.admin.password set)
*
*/
class acm extends acm_memory
{
	var $extension = 'XCache';

	function acm()
	{
		parent::acm_memory();

		if (!function_exists('ini_get') || (int) ini_get('xcache.var_size') <= 0)
		{
			trigger_error('Increase xcache.var_size setting above 0 or enable ini_get() to use this ACM module.', E_USER_ERROR);
		}
	}

	/**
	* Purge cache data
	*
	* @return void
	*/
	function purge()
	{
		// Run before for XCache, if admin functions are disabled it will terminate execution
		parent::purge();

		// If the admin authentication is enabled but not set up, this will cause a nasty error.
		// Not much we can do about it though.
		$n = xcache_count(XC_TYPE_VAR);

		for ($i = 0; $i < $n; $i++)
		{
			xcache_clear_cache(XC_TYPE_VAR, $i);
		}
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
		$result = xcache_get($this->key_prefix . $var);

		return ($result !== null) ? $result : false;
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
		return xcache_set($this->key_prefix . $var, $data, $ttl);
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
		return xcache_unset($this->key_prefix . $var);
	}

	/**
	* Check if a cache var exists
	*
	* @access protected
	* @param string $var Cache key
	* @return bool True if it exists, otherwise false
	*/	
	function _isset($var)
	{
		return xcache_isset($this->key_prefix . $var);
	}
}

?>