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
	* @return int|bool				Query ID (integer) if cache contains a rowset
	*								for the specified query.
	*								False otherwise.
	*/
	public function sql_load($query);

	/**
	* Save result of an SQL query in cache.
	*
	* In persistent cache stores, this function stores the query
	* result to persistent storage. In other words, there is no need
	* to call save() afterwards.
	*
	* @param \phpbb\db\driver\driver_interface $db	Database connection
	* @param string $query			SQL query, should be used for generating storage key
	* @param mixed $query_result	The result from \dbal::sql_query, to be passed to
	* 								\dbal::sql_fetchrow to get all rows and store them
	* 								in cache.
	* @param int $ttl				Time to live, after this timeout the query should
	*								expire from the cache.
	* @return int|mixed				If storing in cache succeeded, an integer $query_id
	* 								representing the query should be returned. Otherwise
	* 								the original $query_result should be returned.
	*/
	public function sql_save(\phpbb\db\driver\driver_interface $db, $query, $query_result, $ttl);

	/**
	* Check if result for a given SQL query exists in cache.
	*
	* @param int $query_id
	* @return bool
	*/
	public function sql_exists($query_id);

	/**
	* Fetch row from cache (database)
	*
	* @param int $query_id
	* @return array|bool 			The query result if found in the cache, otherwise
	* 								false.
	*/
	public function sql_fetchrow($query_id);

	/**
	* Fetch a field from the current row of a cached database result (database)
	*
	* @param int $query_id
	* @param string $field 			The name of the column.
	* @return string|bool 			The field of the query result if found in the cache,
	* 								otherwise false.
	*/
	public function sql_fetchfield($query_id, $field);

	/**
	* Seek a specific row in an a cached database result (database)
	*
	* @param int $rownum 			Row to seek to.
	* @param int $query_id
	* @return bool
	*/
	public function sql_rowseek($rownum, $query_id);

	/**
	* Free memory used for a cached database result (database)
	*
	* @param int $query_id
	* @return bool
	*/
	public function sql_freeresult($query_id);
}
