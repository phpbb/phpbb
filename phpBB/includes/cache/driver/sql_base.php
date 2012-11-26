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
* A base sql driver
*
* @package acm
*/
abstract class phpbb_cache_driver_sql_base implements phpbb_cache_driver_sql_interface
{
	/** @var DBAL **/
	protected $db = null;

	/** @var phpbb_cache_driver_interface **/
	protected $cache_driver = null;

	/** @var array Array of queries loaded at the time, but not (yet) freed **/
	protected $sql_rowset;

	/** @var array Array of pointer locations for each rowset ([$query_id] = 0) **/
	protected $sql_row_pointer;

	public function __construct(dbal $db = null, phpbb_cache_driver_interface $cache_driver)
	{
		$this->db = $db;

		// Cache driver is always sent because of limitations to setting up services with YML
		// If you create your own sql cache system implementing phpbb_cache_driver_sql_interface,
		// you will be sent phpbb_cache_driver_null as a cache driver (just ignore it)
		$this->cache_driver = $cache_driver;
	}

	/**
	* Check if a given sql query id exist in cache
	*
	* @param int $query_id (to load the results from)
	* @return bool True if the query_id exists in the rowset, False if not
	*/
	public function exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	/**
	* Fetch row from cache (database)
	*
	* @param int $query_id (to load the results from)
	* @return array|bool Array of row data, False if query_id isn't set or past the last row
	*/
	public function fetchrow($query_id)
	{
		if ($this->exists($query_id) && $this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			$row = $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]];

			$this->sql_row_pointer[$query_id]++;

			return $row;
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
	public function fetchfield($query_id, $field)
	{
		if ($this->exists($query_id) && $this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			$row = $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]];

			if (isset($row[$field]))
			{
				$this->sql_row_pointer[$query_id]++;

				return $row[$field];
			}
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
	public function rowseek($rownum, $query_id)
	{
		if ($this->exists($query_id) && $rownum >= sizeof($this->sql_rowset[$query_id]))
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
	public function freeresult($query_id)
	{
		if (!$this->exists($query_id))
		{
			return false;
		}

		unset($this->sql_rowset[$query_id]);
		unset($this->sql_row_pointer[$query_id]);

		return true;
	}

	/**
	* Store a rowset in $this->sql_rowset
	*
	* @param array $rowset Rowset from the database
	* @return int Query ID
	*/
	protected function store_rowset($rowset)
	{
		$query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$query_id] = $rowset;
		$this->sql_row_pointer[$query_id] = 0;

		return $query_id;
	}

	/**
	* Normalise query
	*
	* @param string $query Query to remove extra spaces and tabs from
	* @return string
	*/
	protected function normalise_query($query)
	{
		return preg_replace('/[\n\r\s\t]+/', ' ', $query);
	}
}
