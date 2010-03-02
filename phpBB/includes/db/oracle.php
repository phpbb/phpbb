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
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @ignore
*/
if(!defined('SQL_LAYER'))
{

	define('SQL_LAYER', 'oracle');
	include_once($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* Oracle Database Abstraction Layer
* @package dbal
*/
class dbal_oracle extends dbal
{
	var $last_query_text = '';

	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;
		
		$this->db_connect_id = ($this->persistency) ? @ociplogon($this->user, $sqlpassword, $this->server) : @ocinlogon($this->user, $sqlpassword, $this->server);

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database
	*/
	function sql_server_info()
	{
		return 'Oracle ' . @ociserverversion($this->db_connect_id);
	}

	/**
	* SQL Transaction
	* @access: private
	*/
	function _sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				return true;
			break;

			case 'commit':
				return @ocicommit($this->db_connect_id);
			break;

			case 'rollback':
				return @ocirollback($this->db_connect_id);
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

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('start', $query);
			}

			$this->last_query_text = $query;
			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if (!$this->query_result)
			{
				$in_transaction = false;
				if (!$this->transaction)
				{
					$this->sql_transaction('begin');
				}
				else
				{
					$in_transaction = true;
				}

				$this->query_result = @ociparse($this->db_connect_id, $query);
				$success = @ociexecute($this->query_result, OCI_DEFAULT);

				if (!$success)
				{
					$this->sql_error($query);
					$this->query_result = false;
				}
				else
				{
					if (!$in_transaction)
					{
						$this->sql_transaction('commit');
					}
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
	function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0) 
	{
		if ($query != '')
		{
			$this->query_result = false; 

			// Any implicit columns exist?
			if (strpos($query, '.*') !== false)
			{
				// This sucker does a few things for us. It grabs all the explicitly named columns and what tables are being used
				preg_match('/SELECT (?:DISTINCT )?(.*?)FROM(.*?)(?:WHERE|(ORDER|GROUP) BY|$)/s', $query, $tables);

				// The prefixes of the explicit columns don't matter, they simply get in the way
				preg_match_all('/\.(\w+)/', trim($tables[1]), $columns);

				// Flip lets us do an easy isset() call
				$columns = array_flip($columns[1]);

				$table_data = trim($tables[2]);

				// Grab the implicitly named columns, they need expanding...
				preg_match_all('/(\w)\.\*/', $query, $info);

				$cols = array();

				foreach ($info[1] as $table_alias)
				{
					// We need to get the name of the aliased table
					preg_match('/(\w+) ' . $table_alias . '/', $table_data, $table_name);
					$table_name = $table_name[1];

					$sql  = "SELECT column_name
						FROM all_tab_cols
						WHERE table_name = '" . strtoupper($table_name) . "'";

					$result = $this->sql_query($sql);
					while ($row = $this->sql_fetchrow($result))
					{
						if (!isset($columns[strtolower($row['column_name'])]))
						{
							$cols[] = $table_alias . '.' . strtolower($row['column_name']);
						}
					}
					$this->sql_freeresult($result);

					// Remove the implicity .* with it's full expansion
					$query = str_replace($table_alias . '.*', implode(', ', $cols), $query);

					unset($cols);
				}
			}

			$query = 'SELECT * FROM (SELECT /*+ FIRST_ROWS */ rownum AS xrownum, a.* FROM (' . $query . ') a WHERE rownum <= ' . ($offset + $total) . ') WHERE xrownum >= ' . $offset;

			return $this->sql_query($query, $cache_ttl); 
		}
		else
		{
			return false; 
		}
	}

	/**
	* Return number of rows
	* Not used within core code
	*/
	function sql_numrows($query_id = false)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_numrows($query_id);
		}

		$result = @ocifetchstatement($query_id, $this->rowset);

		// OCIFetchStatment kills our query result so we have to execute the statment again
		// if we ever want to use the query_id again.
		@ociexecute($query_id, OCI_DEFAULT);

		return $result;
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->query_result) ? @ocirowcount($this->query_result) : false;
	}

	/**
	* Fetch current row
	*/
	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

		$row = array();
		$result = @ocifetchinto($query_id, $row, OCI_ASSOC + OCI_RETURN_NULLS);

		if (!$result || !$row)
		{
			return false;
		}

		$result_row = array();
		foreach ($row as $key => $value)
		{
			// OCI->CLOB?
			if (is_object($value))
			{
				$value = $value->load();
			}
			
			$result_row[strtolower($key)] = $value;
		}

		return ($query_id) ? $result_row : false;
	}

	/**
	* Fetch field
	* if rownum is false, the current row is used, else it is pointing to the row (zero-based)
	*/
	function sql_fetchfield($field, $rownum = false, $query_id = false)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($rownum !== false)
			{
				$this->sql_rowseek($rownum, $query_id);
			}

			if (isset($cache->sql_rowset[$query_id]))
			{
				return $cache->sql_fetchfield($query_id, $field);
			}

			$row = $this->sql_fetchrow($query_id);
			return isset($row[$field]) ? $row[$field] : false;
		}

		return false;
	}

	/**
	* Seek to given row number
	* rownum is zero-based
	*/
	function sql_rowseek($rownum, $query_id = false)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_rowseek($query_id, $rownum);
		}

		if (!$query_id)
		{
			return false;
		}

		// Reset internal pointer
		@ociexecute($query_id, OCI_DEFAULT);

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
		$query_id = $this->query_result;

		if ($query_id && $this->last_query_text != '')
		{
			if (preg_match('#^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)#is', $this->last_query_text, $tablename))
			{
				$query = 'SELECT ' . $tablename[1] . '_seq.currval FROM DUAL';
				$stmt = @ociparse($this->db_connect_id, $query);
				@ociexecute($stmt, OCI_DEFAULT );

				$temp_result = @ocifetchinto($stmt, $temp_array, OCI_ASSOC + OCI_RETURN_NULLS);
				@ocifreestatement($stmt);

				if ($temp_result)
				{
					return $temp_array['CURRVAL'];
				}
				else
				{
					return false;
				}
			}
		}

		return false;
	}

	/**
	* Free sql result
	*/
	function sql_freeresult($query_id = false)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_freeresult($query_id);
		}

		if (isset($this->open_queries[(int) $query_id]))
		{
			unset($this->open_queries[(int) $query_id]);
			return @ocifreestatement($query_id);
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

	function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	/**
	* return sql error array
	* @access: private
	*/
	function _sql_error()
	{
		$error = @ocierror();
		$error = (!$error) ? @ocierror($this->query_result) : $error;
		$error = (!$error) ? @ocierror($this->db_connect_id) : $error;

		if ($error)
		{
			$this->last_error_result = $error;
		}
		else
		{
			$error = (isset($this->last_error_result) && $this->last_error_result) ? $this->last_error_result : array();
		}

		return $error;
	}

	/**
	* Close sql connection
	* @access: private
	*/
	function _sql_close()
	{
		return @ocilogoff($this->db_connect_id);
	}

	/**
	* Build db-specific report
	* @access: private
	*/
	function _sql_report($mode, $query = '')
	{
		switch ($mode)
		{
			case 'start':
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @ociparse($this->db_connect_id, $query);
				$success = @ociexecute($result, OCI_DEFAULT);
				$row = array();

				while (@ocifetchinto($result, $row, OCI_ASSOC + OCI_RETURN_NULLS))
				{
					// Take the time spent on parsing rows into account
				}
				@ocifreestatement($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}


}

} // if ... define

?>