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
	require("${phpbb_root_path}includes/acm/acm_memory.$phpEx");
}

/**
* ACM for XCache
* @package acm
*/
class acm extends acm_memory
{
	var $extension = 'xcache';

	function acm()
	{
		// Call the parent constructor
		parent::acm_memory();
	}

	/**
	* Purge cache data
	*
	* @return void
	*/
	function purge()
	{
		$n = xcache_count(XC_TYPE_VAR);

		for ($i = 0; $i < $n; $i++)
		{
			xcache_clear_cache(XC_TYPE_VAR, $i);
		}

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
		return xcache_get($var);
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
		return xcache_set($var, $data, $ttl);
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
		return xcache_unset($var);
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
		// Most caches don't need to check
		return xcache_isset($var);
	}
}

?>