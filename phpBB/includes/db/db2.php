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
* Minimum Requirement is DB2 8.2.2
* @package dbal
*/
class dbal_db2 extends dbal
{
	var $multi_insert = true;
	var $last_query_text = '';

	// can't truncate a table
	var $truncate = false;

	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->db_connect_id = ($this->persistency) ? @db2_pconnect($this->dbname, $this->user, $sqlpassword, array('autocommit' => DB2_AUTOCOMMIT_ON, 'DB2_ATTR_CASE' => DB2_CASE_LOWER)) : @db2_connect($this->dbname, $this->user, $sqlpassword, array('autocommit' => DB2_AUTOCOMMIT_ON, 'DB2_ATTR_CASE' => DB2_CASE_LOWER));

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database
	*/
	function sql_server_info()
	{
		$info = db2_server_info($this->db_connect_id);
		return $info->DBMS_VER;
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
				return @db2_autocommit($this->db_connect_id, DB2_AUTOCOMMIT_OFF);
			break;

			case 'commit':
				$result = @db2_commit($this->db_connect_id);
				@db2_autocommit($this->db_connect_id, DB2_AUTOCOMMIT_ON);
				return $result;
			break;

			case 'rollback':
				$result = @db2_rollback($this->db_connect_id);
				@db2_autocommit($this->db_connect_id, DB2_AUTOCOMMIT_ON);
				return $result;
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

			if ($this->query_result === false)
			{
				$array = array();
				if (strlen($query) > 32740)
				{
					if (preg_match('/^(INSERT INTO[^(]++)\\(([^()]+)\\) VALUES[^(]++\\((.*?)\\)$/s', $query, $regs))
					{
						if (strlen($regs[3]) > 32740)
						{
							preg_match_all('/\'(?:[^\']++|\'\')*+\'|[\d-.]+/', $regs[3], $vals, PREG_PATTERN_ORDER);

							$inserts = $vals[0];
							unset($vals);

							foreach ($inserts as $key => $value)
							{
								if (!empty($value) && $value[0] === "'" && strlen($value) > 32742) // check to see if this thing is greater than the max + 'x2
								{
									$inserts[$key] = '?';
									$array[] = str_replace("''", "'", substr($value, 1, -1));
								}
							}

							$query = $regs[1] . '(' . $regs[2] . ') VALUES (' . implode(', ', $inserts) . ')';
						}
					}
					else if (preg_match_all('/^(UPDATE ([\\w_]++)\\s+SET )([\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+)(?:,\\s*[\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+)\\s+(WHERE.*)$/s', $query, $data, PREG_SET_ORDER))
					{
						if (strlen($data[0][3]) > 32740)
						{
							$update = $data[0][1];
							$where = $data[0][4];
							preg_match_all('/(\\w++) = (\'(?:[^\']++|\'\')*+\'|\\d++)/', $data[0][3], $temp, PREG_SET_ORDER);
							unset($data);

							$cols = array();
							foreach ($temp as $value)
							{
								if (!empty($value[2]) && $value[2][0] === "'" && strlen($value[2]) > 32742) // check to see if this thing is greater than the max + 'x2
								{
									$array[] = str_replace("''", "'", substr($value[2], 1, -1));
									$cols[] = $value[1] . '=?';
								}
								else
								{
									$cols[] = $value[1] . '=' . $value[2];
								}
							}

							$query = $update . implode(', ', $cols) . ' ' . $where;
							unset($cols);
						}
					}
				}

				if (sizeof($array))
				{
					if (($this->query_result = @db2_prepare($this->db_connect_id, $query)) === false)
					{
						$this->sql_error($query);
					}

					if (!@db2_execute($this->query_result, $array))
					{
						$this->sql_error($query);
					}
				}
				else
				{
					if (($this->query_result = @db2_exec($this->db_connect_id, $query)) === false)
					{
						$this->sql_error($query);
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

			if ($total && $offset == 0)
			{
				return $this->sql_query($query . ' fetch first ' . $total . ' rows only', $cache_ttl);
			}


			// Seek by $offset rows
			if ($offset)
			{
				$limit_sql = 'SELECT a2.*
					FROM (
						SELECT ROW_NUMBER() OVER() AS rownum, a1.*
							FROM (
								' . $query . '
							) a1
					) a2
				WHERE a2.rownum BETWEEN ' . ($offset + 1) . ' AND ' . ($offset + $total);
				return $this->sql_query($limit_sql, $cache_ttl);
			}
		}
		else
		{
			return false;
		}
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @db2_num_rows($this->db_connect_id) : false;
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

		$row = @db2_fetch_assoc($query_id);

		return $row;
	}

	/**
	* Seek to given row number
	* rownum is zero-based
	*/
	function sql_rowseek($rownum, $query_id = false)
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
			return;
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
		$result_id = @db2_exec($this->db_connect_id, 'VALUES IDENTITY_VAL_LOCAL()');
		if ($result_id)
		{
			if ($row = @db2_fetch_assoc($result_id))
			{
				@db2_free_result($result_id);
				return (int) $row[1];
			}
			@db2_free_result($result_id);
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
			return @db2_free_result($query_id);
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
				return 'LENGTH(' . $col . ')';
			break;

			case 'length_text':
				return 'LENGTH(' . $col . ')';
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
			'message'	=> @db2_stmt_errormsg(),
			'code'		=> @db2_stmt_error()
		);
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
		return @db2_close($this->db_connect_id);
	}

	/**
	* Build db-specific report
	* @access private
	*/
	function _sql_report($mode, $query = '')
	{
		switch ($mode)
		{
			case 'start':

				$html_table = false;
				@db2_exec($this->db_connect_id, 'DELETE FROM EXPLAIN_INSTANCE');
				@db2_exec($this->db_connect_id, 'EXPLAIN PLAN FOR ' . $query);

				// Get the data from the plan
				$sql = "SELECT O.Operator_ID, S2.Target_ID, O.Operator_Type, S.Object_Name, CAST(O.Total_Cost AS INTEGER) Cost
					FROM EXPLAIN_OPERATOR O
					LEFT OUTER JOIN EXPLAIN_STREAM S2 ON O.Operator_ID = S2.Source_ID
					LEFT OUTER JOIN EXPLAIN_STREAM S ON O.Operator_ID = S.Target_ID AND O.Explain_Time = S.Explain_Time AND S.Object_Name IS NOT NULL
					ORDER BY O.Explain_Time ASC, Operator_ID ASC";
				$query_id = @db2_exec($this->db_connect_id, $sql);

				if ($query_id)
				{
					while ($row = @db2_fetch_assoc($query_id))
					{
						$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
					}

					@db2_free_result($query_id);
				}

				if ($html_table)
				{
					$this->html_hold .= '</table>';
				}
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @db2_exec($this->db_connect_id, $query);
				while ($void = @db2_fetch_assoc($result, IBASE_TEXT))
				{
					// Take the time spent on parsing rows into account
				}
				@db2_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>