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
* @todo SQL Cache purge/tidy?
*/

/**
* A generic cache driver service that takes a phpbb_cache_driver and
* turn it into an SQL cache driver
*
* @package acm
*/
class phpbb_cache_driver_sql_generic extends phpbb_cache_driver_sql_base
{
	/**
	* Load cached sql query
	*
	* @param string $query SQL Query
	* @return int|bool Integer query_id on success, bool false on failure
	*/
	public function load($query)
	{
		$query = $this->normalise_query($query);

		$rowset = $this->cache_driver->get('_sql_' . md5($query));

		if ($rowset === false)
		{
			return false;
		}

		return $this->store_rowset($rowset);
	}

	/**
	* Save sql query
	*
	* @param string $query SQL Query
	* @param object $query_result Query result (sql result object)
	* @param int $ttl Time in seconds from now to store the query result
	* @return int query_id (to load the results from)
	*/
	public function save($query, $query_result, $ttl)
	{
		$query = $this->normalise_query($query);

		$rowset = $this->db->sql_fetchrowset($query_result);
		$this->db->sql_freeresult($query_result);

		$this->cache_driver->put('_sql_' . md5($query), $rowset, $ttl, $query);

		return $this->store_rowset($rowset);
	}
}
