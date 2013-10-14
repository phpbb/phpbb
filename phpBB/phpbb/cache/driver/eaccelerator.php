<?php
/**
*
* @package acm
* @copyright (c) 2005, 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cache\driver;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* ACM for eAccelerator
* @package acm
* @todo Missing locks from destroy() talk with David
*/
class eaccelerator extends \phpbb\cache\driver\memory
{
	var $extension = 'eaccelerator';
	var $function = 'eaccelerator_get';

	var $serialize_header = '#phpbb-serialized#';

	/**
	* Purge cache data
	*
	* @return null
	*/
	function purge()
	{
		foreach (eaccelerator_list_keys() as $var)
		{
			// @todo Check why the substr()
			// @todo Only unset vars matching $this->key_prefix
			eaccelerator_rm(substr($var['name'], 1));
		}

		parent::purge();
	}

	/**
	 * Perform cache garbage collection
	 *
	 * @return null
	 */
	function tidy()
	{
		eaccelerator_gc();

		set_config('cache_last_gc', time(), true);
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
		$result = eaccelerator_get($this->key_prefix . $var);

		if ($result === null)
		{
			return false;
		}

		// Handle serialized objects
		if (is_string($result) && strpos($result, $this->serialize_header . 'O:') === 0)
		{
			$result = unserialize(substr($result, strlen($this->serialize_header)));
		}

		return $result;
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
		// Serialize objects and make them easy to detect
		$data = (is_object($data)) ? $this->serialize_header . serialize($data) : $data;

		return eaccelerator_put($this->key_prefix . $var, $data, $ttl);
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
		return eaccelerator_rm($this->key_prefix . $var);
	}
}
