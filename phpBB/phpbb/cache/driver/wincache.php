<?php
/**
*
* @package acm
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cache\driver;

/**
* ACM for WinCache
* @package acm
*/
class wincache extends \phpbb\cache\driver\memory
{
	var $extension = 'wincache';

	/**
	* Purge cache data
	*
	* @return null
	*/
	function purge()
	{
		wincache_ucache_clear();

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
		$success = false;
		$result = wincache_ucache_get($this->key_prefix . $var, $success);

		return ($success) ? $result : false;
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
		return wincache_ucache_set($this->key_prefix . $var, $data, $ttl);
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
		return wincache_ucache_delete($this->key_prefix . $var);
	}
}
