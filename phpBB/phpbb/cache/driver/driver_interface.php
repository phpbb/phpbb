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
* An interface that all cache drivers must implement
*/
interface driver_interface
{
	/**
	* Load global cache
	*
	* @return mixed False if an error was encountered, otherwise the data type of the cached data
	*/
	public function load();

	/**
	* Unload cache object
	*
	* @return null
	*/
	public function unload();

	/**
	* Save modified objects
	*
	* @return null
	*/
	public function save();

	/**
	* Tidy cache
	*
	* @return null
	*/
	public function tidy();

	/**
	* Get saved cache object
	*
	* @param string $var_name 		Cache key
	* @return mixed 				False if an error was encountered, otherwise the saved cached object
	*/
	public function get($var_name);

	/**
	* Put data into cache
	*
	* @param string $var_name 		Cache key
	* @param mixed $var 			Cached data to store
	* @param int $ttl 				Time-to-live of cached data
	* @return null
	*/
	public function put($var_name, $var, $ttl = 0);

	/**
	* Purge cache data
	*
	* @return null
	*/
	public function purge();

	/**
	* Destroy cache data
	*
	* @param string $var_name 		Cache key
	* @param string $table 			Table name
	* @return null
	*/
	public function destroy($var_name, $table = '');

	/**
	* Check if a given cache entry exists
	*
	* @param string $var_name 		Cache key
	*
	* @return bool 					True if cache file exists and has not expired.
	*								False otherwise.
	*/
	public function _exists($var_name);

	/**
	* Load result of an SQL query from cache.
	*
	* @param string $query			SQL query
	*
	* @return array|false			The cached data the if cache contains a rowset
	*								for the specified query.
	*								False otherwise.
	*/
	public function sql_load($query);

	/**
	 * Save a SQL query result set to cache.
	 *
	 * @param string	$query	The SQL query string.
	 * @param array		$data	The result of the query.
	 * @param int		$ttl	The time to live attribute.
	 *
	 * @return bool True, if the data has been written to the cache, False otherwise.
	 */
	public function sql_save($query, $data, $ttl);

	/**
	 * Returns the cache key for the SQL query.
	 *
	 * @param string $query The SQL query.
	 *
	 * @return string The cache key for the SQL query.
	 */
	public function get_cache_id_from_sql_query($query);
}
