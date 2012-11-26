<?php
/**
*
* @package acm
* @copyright (c) 2012 phpBB Group
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
* An interface that all sql cache drivers must implement
*
* @package acm
*/
interface phpbb_cache_driver_sql_interface
{

	/**
	* Load cached sql query
	*
	* @param string $query SQL Query
	* @return int|bool Integer query_id on success, bool false on failure
	*/
	public function load($query);

	/**
	* Save sql query
	*
	* @param string $query SQL Query
	* @param object $query_result Query result (sql result object)
	* @param int $ttl Time in seconds from now to store the query result
	* @return int query_id (to load the results from)
	*/
	public function save($query, $query_result, $ttl);

	/**
	* Check if a given sql query id exist in cache
	*
	* @param int $query_id (to load the results from)
	* @return bool True if the query_id exists in the rowset, False if not
	*/
	public function exists($query_id);

	/**
	* Fetch row from cache (database)
	*
	* @param int $query_id (to load the results from)
	* @return array|bool Array of row data, False if query_id isn't set or past the last row
	*/
	public function fetchrow($query_id);

	/**
	* Fetch a field from the current row of a cached database result (database)
	*
	* @param int $query_id (to load the results from)
	* @param string $field Column to return
	* @return mixed Field on success, Bool False if the query does not exist or past the last row
	*/
	public function fetchfield($query_id, $field);

	/**
	* Seek a specific row in an a cached database result (database)
	*
	* @param int $rownum Row to seek to
	* @param int $query_id (to load the results from)
	* @return bool False if the query does not exist or past the last row, True on success
	*/
	public function rowseek($rownum, $query_id);

	/**
	* Free memory used for a cached database result (database)
	*
	* @param int $query_id (to clear the results from)
	* @return bool False if the query does not exist, True on success
	*/
	public function freeresult($query_id);
}
