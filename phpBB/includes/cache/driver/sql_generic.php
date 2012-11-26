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
		$hash = md5($query);

		// determine which tables this query belongs to
		// Some queries use backticks, namely the get_database_size() query
		// don't check for conformity, the SQL would error and not reach here.
		// @todo handle JOINS
		if (!preg_match('/FROM \\(?(`?\\w+`?(?: \\w+)?(?:, ?`?\\w+`?(?: \\w+)?)*)\\)?/', $query, $regs))
		{
			// Bail out if the match fails.
			return;
		}
		$tables = array_map('trim', explode(',', $regs[1]));

		foreach ($tables as $table_name)
		{
			// Remove backticks
			$table_name = ($table_name[0] == '`') ? substr($table_name, 1, -1) : $table_name;

			if (($pos = strpos($table_name, ' ')) !== false)
			{
				$table_name = substr($table_name, 0, $pos);
			}

			$table_queries = $this->cache_driver->get('_sql_' . $table_name);

			if ($table_queries === false)
			{
				$table_queries = array();
			}

			$table_queries[$hash] = true;

			$this->cache_driver->put('_sql_' . $table_name, $table_queries);
		}

		$rowset = $this->db->sql_fetchrowset($query_result);
		$this->db->sql_freeresult($query_result);

		$this->cache_driver->put('_sql_' . $hash, $rowset, $ttl);

		return $this->store_rowset($rowset);
	}

	/**
	* Tidy cache
	* For generic, there is not any easy way to tidy just sql files, so tidy everything
	*/
	public function tidy()
	{
		$this->cache_driver->tidy();
	}

	/**
	* Purge cache data
	* For generic, there is not any easy way to purge just sql files, so purge everything
	*/
	public function purge()
	{
		$this->cache_driver->purge();
	}

	/**
	* Destroy cache data
	*
	* @param string $table Table name to destroy cached queries for
	*/
	public function destroy($table)
	{
		if (!empty($table))
		{
			if (!is_array($table))
			{
				$table = array($table);
			}

			foreach ($table as $table_name)
			{
				// gives us the md5s that we want
				$table_queries = $this->cache_driver->get('_sql_' . $table_name);

				if ($table_queries === false)
				{
					continue;
				}

				// delete each query ref
				foreach ($table_queries as $md5_id => $void)
				{
					$this->cache_driver->destroy('_sql_' . $md5_id);
				}

				// delete the table ref
				$this->cache_driver->destroy('_sql_' . $table_name);
			}
		}
	}
}
