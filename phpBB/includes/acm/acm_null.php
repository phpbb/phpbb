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

/**
* ACM Null Caching
* @package acm
*/
class acm
{
	/**
	* Set cache path
	*/
	function acm()
	{
	}

	/**
	* Load global cache
	*/
	function load()
	{
		return true;
	}

	/**
	* Unload cache object
	*/
	function unload()
	{
	}

	/**
	* Save modified objects
	*/
	function save()
	{
	}

	/**
	* Tidy cache
	*/
	function tidy()
	{
		// This cache always has a tidy room.
		set_config('cache_last_gc', time(), true);
	}

	/**
	* Get saved cache object
	*/
	function get($var_name)
	{
		return false;
	}

	/**
	* Put data into cache
	*/
	function put($var_name, $var, $ttl = 0)
	{
	}

	/**
	* Purge cache data
	*/
	function purge()
	{
	}

	/**
	* Destroy cache data
	*/
	function destroy($var_name, $table = '')
	{
	}

	/**
	* Check if a given cache entry exist
	*/
	function _exists($var_name)
	{
		return false;
	}

	/**
	* Load cached sql query
	*/
	function sql_load($query)
	{
		return false;
	}

	/**
	* Save sql query
	*/
	function sql_save($query, &$query_result, $ttl)
	{
	}

	/**
	* Ceck if a given sql query exist in cache
	*/
	function sql_exists($query_id)
	{
		return false;
	}

	/**
	* Fetch row from cache (database)
	*/
	function sql_fetchrow($query_id)
	{
		return false;
	}

	/**
	* Fetch a field from the current row of a cached database result (database)
	*/
	function sql_fetchfield($query_id, $field)
	{
		return false;
	}

	/**
	* Seek a specific row in an a cached database result (database)
	*/
	function sql_rowseek($rownum, $query_id)
	{
		return false;
	}

	/**
	* Free memory used for a cached database result (database)
	*/
	function sql_freeresult($query_id)
	{
		return false;
	}
}

?>