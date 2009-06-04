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
* ACM for eAccelerator
* @package acm
* @todo Missing locks from destroy() talk with David
*/
class acm extends acm_memory
{
	var $extension = 'eaccelerator';

	var $serialize_header = '#phpbb-serialized#';

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
		foreach (eaccelerator_list_keys() as $var)
		{
			// @todo Check why the substr()
			eaccelerator_rm(substr($var['name'], 1));
		}

		parent::purge();
	}

	/**
	 * Perform cache garbage collection
	 *
	 * @return void
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
		$result = eaccelerator_get($var);

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

		return eaccelerator_put($var, $data, $ttl);
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
		return eaccelerator_rm($var);
	}
}

?>