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
	public function destroy($var_name, $table = '');

	/**
	* Check if a given cache entry exist
	*/
	public function _exists($var_name);

	/**
	* Load cached sql query
	*/
	public function sql_load($query);

	/**
	* Save sql query
	*/
	public function sql_save($query, $query_result, $ttl);

	/**
	* Ceck if a given sql query exist in cache
	*/
	public function sql_exists($query_id);

	/**
	* Fetch row from cache (database)
	*/
	public function sql_fetchrow($query_id);

	/**
	* Fetch a field from the current row of a cached database result (database)
	*/
	public function sql_fetchfield($query_id, $field);

	/**
	* Seek a specific row in an a cached database result (database)
	*/
	public function sql_rowseek($rownum, $query_id);

	/**
	* Free memory used for a cached database result (database)
	*/
	public function sql_freeresult($query_id);
}
