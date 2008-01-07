<?php
/**
*
* @package dbal
* @version $Id$
* @copyright (c) 2005 phpBB Group
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

include_once($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* MSSQL Database Abstraction Layer
* Minimum Requirement is MSSQL 2005+
* @package dbal
*/
class dbal_mssql_2005 extends dbal
{
	var $last_query_text = '';
	var $dbms_type = 'mssql';

	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->db_connect_id = sqlsrv_connect($this->server, array('UID' => $this->user, 'PWD' => $sqlpassword));

		if ($this->db_connect_id && $this->dbname != '')
		{
			if (!sqlsrv_conn_execute($this->db_connect_id, 'USE ' . $this->dbname))
			{
				@sqlsrv_conn_close($this->db_connect_id);
				return false;
			}
		}

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database
	*/
	function sql_server_info()
	{
		$server_info = sqlsrv_conn_server_info($this->db_connect_id);

		return 'MSSQL (2005)<br />' . $server_info['SQL Server Version'];
	}

	/**
	* SQL Transaction
	* @access private
	*/
	function _sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				return sqlsrv_conn_execute($this->db_connect_id, 'BEGIN TRANSACTION');
			break;

			case 'commit':
				return sqlsrv_conn_execute($this->db_connect_id, 'COMMIT TRANSACTION');
			break;

			case 'rollback':
				return sqlsrv_conn_execute($this->db_connect_id, 'ROLLBACK TRANSACTION');
			break;
		}

		return true;
	}

	/**
	* Base query method
	*
	* @param	string	$query		Contains the SQL query which shall be executed
	* @param	int		$cache_ttl	Either 0 to avoid caching or the time in seconds which the result shall be kept in cache
	* @return	mixed				When casted to bool the returned value returns true on success and false on failure
	*
	* @access	public
	*/
	function sql_query($query = '', $cache_ttl = 0)
	{
		if ($query != '')
		{
			global $cache;

			if (strpos($query, 'BEGIN') === 0 || strpos($query, 'COMMIT') === 0)
			{
				return true;
			}

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('start', $query);
			}

			$this->last_query_text = $query;
			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if ($this->query_result === false)
			{
				if (($this->query_result = @sqlsrv_conn_execute($this->db_connect_id, $query)) === false)
				{
					$this->sql_error($query);
				}

				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('stop', $query);
				}

				if ($cache_ttl && method_exists($cache, 'sql_save'))
				{
					$this->open_queries[(int) $this->query_result] = $this->query_result;
					$cache->sql_save($query, $this->query_result, $cache_ttl);
				}
				else if (strpos($query, 'SELECT') === 0 && $this->query_result)
				{
					$this->open_queries[(int) $this->query_result] = $this->query_result;
				}
			}
			else if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('fromcache', $query);
			}
		}
		else
		{
			return false;
		}

		return ($this->query_result) ? $this->query_result : false;
	}

	/**
	* Build LIMIT query
	*/
	function _sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$this->query_result = false;

		// Since TOP is only returning a set number of rows we won't need it if total is set to 0 (return all rows)
		if ($total)
		{
			// We need to grab the total number of rows + the offset number of rows to get the correct result
			if (strpos($query, 'SELECT DISTINCT') === 0)
			{
				$query = 'SELECT DISTINCT TOP ' . ($total + $offset) . ' ' . substr($query, 15);
			}
			else
			{
				$query = 'SELECT TOP ' . ($total + $offset) . ' ' . substr($query, 6);
			}
		}

		$result = $this->sql_query($query, $cache_ttl);

		// Seek by $offset rows
		if ($offset)
		{
			$this->sql_rowseek($offset, $result);
		}

		return $result;
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? sqlsrv_stmt_rows_affected($this->db_connect_id) : false;
	}

	/**
	* Fetch current row
	*/
	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

		if ($query_id === false)
		{
			return false;
		}

		$row = @sqlsrv_stmt_fetch_array($query_id, SQLSRV_FETCH_TYPE_ARRAY);

		// I hope i am able to remove this later... hopefully only a PHP or MSSQL bug
		if ($row)
		{
			foreach ($row as $key => $value)
			{
				$row[$key] = ($value === ' ' || $value === NULL) ? '' : $value;
			}
		}

		return $row;
	}

	/**
	* Seek to given row number
	* rownum is zero-based
	*/
	function sql_rowseek($rownum, &$query_id)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_rowseek($rownum, $query_id);
		}

		if ($query_id === false)
		{
			return false;
		}

		$this->sql_freeresult($query_id);
		$query_id = $this->sql_query($this->last_query_text);

		if ($query_id === false)
		{
			return false;
		}

		// We do not fetch the row for rownum == 0 because then the next resultset would be the second row
		for ($i = 0; $i < $rownum; $i++)
		{
			if (!$this->sql_fetchrow($query_id))
			{
				return false;
			}
		}

		return true;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		$result_id = @sqlsrv_conn_execute($this->db_connect_id, 'SELECT SCOPE_IDENTITY()');
		if ($result_id)
		{
			if ($row = @sqlsrv_stmt_fetch_array($result_id, SQLSRV_FETCH_TYPE_ARRAY))
			{
				@sqlsrv_stmt_close($result_id);
				return $row['computed'];
			}
			@sqlsrv_stmt_close($result_id);
		}

		return false;
	}

	/**
	* Free sql result
	*/
	function sql_freeresult($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_freeresult($query_id);
		}

		if (isset($this->open_queries[$query_id]))
		{
			unset($this->open_queries[$query_id]);
			return @sqlsrv_stmt_close($query_id);
		}

		return false;
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		return str_replace("'", "''", $msg);
	}

	/**
	* Expose a DBMS specific function
	*/
	function sql_function($type, $col)
	{
		switch ($type)
		{
			case 'length_varchar':
			case 'length_text':
				return 'DATALENGTH(' . $col . ')';
			break;
		}
	}

	/**
	* Build LIKE expression
	* @access private
	*/
	function _sql_like_expression($expression)
	{
		return $expression . " ESCAPE '\\'";
	}

	/**
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		$error = array(
			'message'	=> '',
			'code'		=> ''
		);

		foreach (sqlsrv_errors() as $error_array)
		{
			$error['message'] .= $error_array['message'] . "<br />";
			$error['code'] .= $error_array['code'] . "<br />";
		}

		return $error;
	}

	/**
	* Build db-specific query data
	* @access private
	*/
	function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	/**
	* Close sql connection
	* @access private
	*/
	function _sql_close()
	{
		return @sqlsrv_conn_close($this->db_connect_id);
	}

	/**
	* Build db-specific report
	* @access private
	*/
	function _sql_report($mode, $query = '')
	{
		switch ($mode)
		{
			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @sqlsrv_conn_execute($this->db_connect_id, $query);
				while ($void = @sqlsrv_stmt_fetch_array($result))
				{
					// Take the time spent on parsing rows into account
				}
				@sqlsrv_stmt_close($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>