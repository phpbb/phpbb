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
* A generic cache driver service that takes a phpbb_cache_driver and
* turn it into an SQL cache driver
*
* @package acm
*/
class phpbb_cache_driver_sql_generic implements phpbb_cache_driver_sql_interface
{
	/** @var phpbb_cache_driver_interface **/
	protected $cache_driver = null;

	public function __construct(phpbb_cache_driver_interface $cache_driver)
	{
		$this->cache_driver = $cache_driver;
	}

	/**
	* Load cached sql query
	*
	* @param string $query SQL Query
	* @return int|bool Integer query_id on success, bool false on failure
	*/
	public function sql_load($query)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		if (($rowset = $this->cache_driver->get('_sql_' . md5($query))) === false)
		{
			return false;
		}

		$query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$query_id] = $rowset;
		$this->sql_row_pointer[$query_id] = 0;

		return $query_id;
	}

	/**
	* Save sql query
	*
	* @param string $query SQL Query
	* @param object $query_result Query result (sql result object)
	* @param int $ttl Time in seconds from now to store the query result
	* @return int query_id (to load the results from)
	*/
	public function sql_save($query, $query_result, $ttl)
	{
		global $db;

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		$query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$query_id] = array();
		$this->sql_row_pointer[$query_id] = 0;

		$this->sql_rowset[$query_id] = $db->sql_fetchrowset($query_result);
		$db->sql_freeresult($query_result);

		$this->cache_driver->put('_sql_' . md5($query), $this->sql_rowset[$query_id], $ttl, $query);

		return $query_id;
	}

	/**
	* Check if a given sql query id exist in cache
	*
	* @param int $query_id (to load the results from)
	* @return bool True if the query_id exists in the rowset, False if not
	*/
	public function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	/**
	* Fetch row from cache (database)
	*
	* @param int $query_id (to load the results from)
	* @return array|bool Array of row data, False if query_id isn't set or past the last row
	*/
	public function sql_fetchrow($query_id)
	{
		if ($this->sql_exists($query_id) && $this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			return $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++];
		}

		return false;
	}

	/**
	* Fetch a field from the current row of a cached database result (database)
	*
	* @param int $query_id (to load the results from)
	* @param string $field Column to return
	* @return mixed Field on success, Bool False if the query does not exist or past the last row
	*/
	public function sql_fetchfield($query_id, $field)
	{
		if ($this->sql_exists($query_id) && $this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			return (isset($this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]][$field])) ? $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++][$field] : false;
		}

		return false;
	}

	/**
	* Seek a specific row in an a cached database result (database)
	*
	* @param int $rownum Row to seek to
	* @param int $query_id (to load the results from)
	* @return bool False if the query does not exist or past the last row, True on success
	*/
	public function sql_rowseek($rownum, $query_id)
	{
		if ($this->sql_exists($query_id) && $rownum >= sizeof($this->sql_rowset[$query_id]))
		{
			return false;
		}

		$this->sql_row_pointer[$query_id] = $rownum;

		return true;
	}

	/**
	* Free memory used for a cached database result (database)
	*
	* @param int $query_id (to clear the results from)
	* @return bool False if the query does not exist, True on success
	*/
	public function sql_freeresult($query_id)
	{
		if (!$this->sql_exists($query_id))
		{
			return false;
		}

		unset($this->sql_rowset[$query_id]);
		unset($this->sql_row_pointer[$query_id]);

		return true;
	}
}
