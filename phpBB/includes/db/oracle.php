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
* Oracle Database Abstraction Layer
* @package dbal
*/
class dbal_oracle extends dbal
{
	var $last_query_text = '';

	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;
		
		$this->db_connect_id = ($new_link) ? @ocinlogon($this->user, $sqlpassword, $this->dbname, 'UTF8') : (($this->persistency) ? @ociplogon($this->user, $sqlpassword, $this->dbname, 'UTF8') : @ocinlogon($this->user, $sqlpassword, $this->dbname, 'UTF8'));

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database
	*/
	function sql_server_info()
	{
		return @ociserverversion($this->db_connect_id);
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

			if ($this->query_result === false)
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

				// We overcome Oracle's 4000 char limit by binding vars
				if (strlen($query) > 4000)
				{
					$array = array();

					if (preg_match('/^(INSERT INTO[^(]+)\\(([^()]+)\\) VALUES[^(]+\\((.*?)\\)$/s', $query, $regs))
					{
						if (strlen($regs[3]) > 4000)
						{
							$cols = explode(', ', $regs[2]);
							preg_match_all('/\'(?:[^\']++|\'\')*+\'|\\d+/', $regs[3], $vals, PREG_PATTERN_ORDER);

							$inserts = $vals[0];
							unset($vals);

							foreach ($inserts as $key => $value)
							{
								if (!empty($value) && $value[0] === "'" && strlen($value) > 4002) // check to see if this thing is greater than the max + 'x2
								{
									$inserts[$key] = ':' . strtoupper($cols[$key]);
									$array[$inserts[$key]] = str_replace("''", "'", substr($value, 1, -1));
								}
							}

							$query = $regs[1] . '(' . $regs[2] . ') VALUES (' . implode(', ', $inserts) . ')';
							unset($art);
						}
					}
					else if (preg_match_all('/^(UPDATE [\\w_]++\\s+SET )(\\w+ = (?:\'(?:[^\']++|\'\')*+\'|\\d+)(?:, \\w+ = (?:\'(?:[^\']++|\'\')*+\'|\\d+))*+)\\s+(WHERE.*)$/s', $query, $data, PREG_SET_ORDER))
					{
						if (strlen($data[0][2]) > 4000)
						{
							$update = $data[0][1];
							$where = $data[0][3];
							preg_match_all('/(\\w++) = (\'(?:[^\']++|\'\')*+\'|\\d++)/', $data[0][2], $temp, PREG_SET_ORDER);
							unset($data);

							$art = array();
							foreach ($temp as $value)
							{
								if (!empty($value[2]) && $value[2][0] === "'" && strlen($value[2]) > 4002) // check to see if this thing is greater than the max + 'x2
								{
									$art[] = $value[1] . '=:' . strtoupper($value[1]);
									$array[$value[1]] = str_replace("''", "'", substr($value[2], 1, -1));
								}
								else
								{
									$art[] = $value[1] . '=' . $value[2];
								}
							}

							$query = $update . implode(', ', $art) . ' ' . $where;
							unset($art);
						}
					}
				}

				$this->query_result = @ociparse($this->db_connect_id, $query);

				foreach ($array as $key => $value)
				{
					@ocibindbyname($this->query_result, $key, $array[$key], -1);
				}

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
	function _sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0) 
	{
		$this->query_result = false; 

		$query = 'SELECT * FROM (SELECT /*+ FIRST_ROWS */ rownum AS xrownum, a.* FROM (' . $query . ') a WHERE rownum <= ' . ($offset + $total) . ') WHERE xrownum >= ' . $offset;

		return $this->sql_query($query, $cache_ttl); 
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

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

		if ($query_id !== false)
		{
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

			return $result_row;
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

		if ($query_id !== false && $this->last_query_text != '')
		{
			if (preg_match('#^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)#is', $this->last_query_text, $tablename))
			{
				$query = 'SELECT ' . $tablename[1] . '_seq.currval FROM DUAL';
				$stmt = @ociparse($this->db_connect_id, $query);
				@ociexecute($stmt, OCI_DEFAULT);

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
	* @access private
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
	* @access private
	*/
	function _sql_close()
	{
		return @ocilogoff($this->db_connect_id);
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

?>