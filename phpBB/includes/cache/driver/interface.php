<?php
/**
*
* @package acm
* @copyright (c) 2010 phpBB Group
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

/**
* An interface that all cache drivers must implement
*
* @package acm
*/
interface phpbb_cache_driver_interface
{
	/**
	* Load global cache
	*/
	public function load();

	/**
	* Unload cache object
	*/
	public function unload();

	/**
	* Save modified objects
	*/
	public function save();

	/**
	* Tidy cache
	*/
	public function tidy();

	/**
	* Get saved cache object
	*/
	public function get($var_name);

	/**
	* Put data into cache
	*/
	public function put($var_name, $var, $ttl = 0);

	/**
	* Purge cache data
	*/
	public function purge();

	/**
	* Destroy cache data
	*/
	public function destroy($var_name);

	/**
	* Check if a given cache entry exist
	*/
	public function _exists($var_name);
}
