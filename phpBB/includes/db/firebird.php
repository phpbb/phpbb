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
* Firebird/Interbase Database Abstraction Layer
* Minimum Requirement is Firebird 2.1
* @package dbal
*/
class dbal_firebird extends dbal
{
	var $last_query_text = '';
	var $service_handle = false;
	var $affected_rows = 0;

	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = str_replace('\\', '/', $database);

		// There are three possibilities to connect to an interbase db
		if (!$this->server)
		{
			$use_database = $this->dbname;
		}
		else if (strpos($this->server, '//') === 0)
		{
			$use_database = $this->server . $this->dbname;
		}
		else
		{
			$use_database = $this->server . ':' . $this->dbname;
		}

		$this->db_connect_id = ($this->persistency) ? @ibase_pconnect($use_database, $this->user, $sqlpassword, false, false, 3) : @ibase_connect($use_database, $this->user, $sqlpassword, false, false, 3);

		$this->service_handle = (function_exists('ibase_service_attach') && $this->server) ? @ibase_service_attach($this->server, $this->user, $sqlpassword) : false;

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database
	* @param bool $raw if true, only return the fetched sql_server_version
	* @param bool $use_cache forced to false for Interbase
	* @return string sql server version
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		/**
		* force $use_cache false.  I didn't research why the caching code there is no caching code
		* but I assume its because the IB extension provides a direct method to access it
		* without a query.
		*/

		$use_cache = false;

		if ($this->service_handle !== false && function_exists('ibase_server_info'))
		{
			return @ibase_server_info($this->service_handle, IBASE_SVC_SERVER_VERSION);
		}

		return ($raw) ? '2.1' : 'Firebird/Interbase';
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
				return true;
			break;

			case 'commit':
				return @ibase_commit();
			break;

			case 'rollback':
				return @ibase_rollback();
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
				// We overcome Firebird's 32767 char limit by binding vars
				if (strlen($query) > 32767)
				{
					if (preg_match('/^(INSERT INTO[^(]++)\\(([^()]+)\\) VALUES[^(]++\\((.*?)\\)$/s', $query, $regs))
					{
						if (strlen($regs[3]) > 32767)
						{
							preg_match_all('/\'(?:[^\']++|\'\')*+\'|[\d-.]+/', $regs[3], $vals, PREG_PATTERN_ORDER);

							$inserts = $vals[0];
							unset($vals);

							foreach ($inserts as $key => $value)
							{
								if (!empty($value) && $value[0] === "'" && strlen($value) > 32769) // check to see if this thing is greater than the max + 'x2
								{
									$inserts[$key] = '?';
									$array[] = str_replace("''", "'", substr($value, 1, -1));
								}
							}

							$query = $regs[1] . '(' . $regs[2] . ') VALUES (' . implode(', ', $inserts) . ')';
						}
					}
					else if (preg_match('/^(UPDATE ([\\w_]++)\\s+SET )([\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|\\d+)(?:,\\s*[\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+)\\s+(WHERE.*)$/s', $query, $data))
					{
						if (strlen($data[3]) > 32767)
						{
							$update = $data[1];
							$where = $data[4];
							preg_match_all('/(\\w++)\\s*=\\s*(\'(?:[^\']++|\'\')*+\'|[\d-.]++)/', $data[3], $temp, PREG_SET_ORDER);
							unset($data);

							$cols = array();
							foreach ($temp as $value)
							{
								if (!empty($value[2]) && $value[2][0] === "'" && strlen($value[2]) > 32769) // check to see if this thing is greater than the max + 'x2
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

				if (!function_exists('ibase_affected_rows') && (preg_match('/^UPDATE ([\w_]++)\s+SET [\w_]++\s*=\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+)(?:,\s*[\w_]++\s*=\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+\s+(WHERE.*)?$/s', $query, $regs) || preg_match('/^DELETE FROM ([\w_]++)\s*(WHERE\s*.*)?$/s', $query, $regs)))
				{
					$affected_sql = 'SELECT COUNT(*) as num_rows_affected FROM ' . $regs[1];
					if (!empty($regs[2]))
					{
						$affected_sql .= ' ' . $regs[2];
					}

					if (!($temp_q_id = @ibase_query($this->db_connect_id, $affected_sql)))
					{
						return false;
					}

					$temp_result = @ibase_fetch_assoc($temp_q_id);
					@ibase_free_result($temp_q_id);

					$this->affected_rows = ($temp_result) ? $temp_result['NUM_ROWS_AFFECTED'] : false;
				}

				if (sizeof($array))
				{
					$p_query = @ibase_prepare($this->db_connect_id, $query);
					array_unshift($array, $p_query);
					$this->query_result = call_user_func_array('ibase_execute', $array);
					unset($array);

					if ($this->query_result === false)
					{
						$this->sql_error($query);
					}
				}
				else if (($this->query_result = @ibase_query($this->db_connect_id, $query)) === false)
				{
					$this->sql_error($query);
				}

				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('stop', $query);
				}

				if (!$this->transaction)
				{
					if (function_exists('ibase_commit_ret'))
					{
						@ibase_commit_ret();
					}
					else
					{
						// way cooler than ibase_commit_ret :D
						@ibase_query('COMMIT RETAIN;');
					}
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

		return $this->query_result;
	}

	/**
	* Build LIMIT query
	*/
	function _sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$this->query_result = false;

		$query = 'SELECT FIRST ' . $total . ((!empty($offset)) ? ' SKIP ' . $offset : '') . substr($query, 6);

		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		// PHP 5+ function
		if (function_exists('ibase_affected_rows'))
		{
			return ($this->db_connect_id) ? @ibase_affected_rows($this->db_connect_id) : false;
		}
		else
		{
			return $this->affected_rows;
		}
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

		$row = array();
		$cur_row = @ibase_fetch_object($query_id, IBASE_TEXT);

		if (!$cur_row)
		{
			return false;
		}

		foreach (get_object_vars($cur_row) as $key => $value)
		{
			$row[strtolower($key)] = (is_string($value)) ? trim(str_replace(array("\\0", "\\n"), array("\0", "\n"), $value)) : $value;
		}

		return (sizeof($row)) ? $row : false;
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
		$query_id = $this->query_result;

		if ($query_id !== false && $this->last_query_text != '')
		{
			if ($this->query_result && preg_match('#^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)#i', $this->last_query_text, $tablename))
			{
				$sql = 'SELECT GEN_ID(' . $tablename[1] . '_gen, 0) AS new_id FROM RDB$DATABASE';

				if (!($temp_q_id = @ibase_query($this->db_connect_id, $sql)))
				{
					return false;
				}

				$temp_result = @ibase_fetch_assoc($temp_q_id);
				@ibase_free_result($temp_q_id);

				return ($temp_result) ? $temp_result['NEW_ID'] : false;
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

		if ($query_id === false)
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
			return @ibase_free_result($query_id);
		}

		return false;
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		return str_replace(array("'", "\0"), array("''", ''), $msg);
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
	* Build db-specific query data
	* @access private
	*/
	function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	function _sql_bit_and($column_name, $bit, $compare = '')
	{
		return 'BIN_AND(' . $column_name . ', ' . (1 << $bit) . ')' . (($compare) ? ' ' . $compare : '');
	}

	function _sql_bit_or($column_name, $bit, $compare = '')
	{
		return 'BIN_OR(' . $column_name . ', ' . (1 << $bit) . ')' . (($compare) ? ' ' . $compare : '');
	}

	/**
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		return array(
			'message'	=> @ibase_errmsg(),
			'code'		=> (@function_exists('ibase_errcode') ? @ibase_errcode() : '')
		);
	}

	/**
	* Close sql connection
	* @access private
	*/
	function _sql_close()
	{
		if ($this->service_handle !== false)
		{
			@ibase_service_detach($this->service_handle);
		}

		return @ibase_close($this->db_connect_id);
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
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @ibase_query($this->db_connect_id, $query);
				while ($void = @ibase_fetch_object($result, IBASE_TEXT))
				{
					// Take the time spent on parsing rows into account
				}
				@ibase_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>