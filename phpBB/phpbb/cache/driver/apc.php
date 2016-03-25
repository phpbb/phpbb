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

/**
* ACM for APC
*/
class apc extends \phpbb\cache\driver\memory
{
	var $extension = 'apc';

	/**
	* {@inheritDoc}
	*/
	function purge()
	{
		apc_clear_cache('user');

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
		return apc_fetch($this->key_prefix . $var);
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
		return apc_store($this->key_prefix . $var, $data, $ttl);
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
		return apc_delete($this->key_prefix . $var);
	}
}
