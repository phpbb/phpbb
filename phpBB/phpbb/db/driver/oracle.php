<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\db\driver;

/**
* Oracle Database Abstraction Layer
*/
class oracle extends \phpbb\db\driver\driver
{
	var $last_query_text = '';
	var $connect_error = '';

	/**
	* {@inheritDoc}
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$connect = $database;

		// support for "easy connect naming"
		if ($sqlserver !== '' && $sqlserver !== '/')
		{
			if (substr($sqlserver, -1, 1) == '/')
			{
				$sqlserver == substr($sqlserver, 0, -1);
			}
			$connect = $sqlserver . (($port) ? ':' . $port : '') . '/' . $database;
		}

		if ($new_link)
		{
			if (!function_exists('ocinlogon'))
			{
				$this->connect_error = 'ocinlogon function does not exist, is oci extension installed?';
				return $this->sql_error('');
			}
			$this->db_connect_id = @ocinlogon($this->user, $sqlpassword, $connect, 'UTF8');
		}
		else if ($this->persistency)
		{
			if (!function_exists('ociplogon'))
			{
				$this->connect_error = 'ociplogon function does not exist, is oci extension installed?';
				return $this->sql_error('');
			}
			$this->db_connect_id = @ociplogon($this->user, $sqlpassword, $connect, 'UTF8');
		}
		else
		{
			if (!function_exists('ocilogon'))
			{
				$this->connect_error = 'ocilogon function does not exist, is oci extension installed?';
				return $this->sql_error('');
			}
			$this->db_connect_id = @ocilogon($this->user, $sqlpassword, $connect, 'UTF8');
		}

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* {@inheritDoc}
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		/**
		* force $use_cache false.  I didn't research why the caching code below is commented out
		* but I assume its because the Oracle extension provides a direct method to access it
		* without a query.
		*/
/*
		global $cache;

		if (empty($cache) || ($this->sql_server_version = $cache->get('oracle_version')) === false)
		{
			$result = @ociparse($this->db_connect_id, 'SELECT * FROM v$version WHERE banner LIKE \'Oracle%\'');
			@ociexecute($result, OCI_DEFAULT);
			@ocicommit($this->db_connect_id);

			$row = array();
			@ocifetchinto($result, $row, OCI_ASSOC + OCI_RETURN_NULLS);
			@ocifreestatement($result);
			$this->sql_server_version = trim($row['BANNER']);

			$cache->put('oracle_version', $this->sql_server_version);
		}
*/
		$this->sql_server_version = @ociserverversion($this->db_connect_id);

		return $this->sql_server_version;
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
	* Oracle specific code to handle the fact that it does not compare columns properly
	* @access private
	*/
	function _rewrite_col_compare($args)
	{
		if (count($args) == 4)
		{
			if ($args[2] == '=')
			{
				return '(' . $args[0] . ' OR (' . $args[1] . ' is NULL AND ' . $args[3] . ' is NULL))';
			}
			else if ($args[2] == '<>')
			{
				// really just a fancy way of saying foo <> bar or (foo is NULL XOR bar is NULL) but SQL has no XOR :P
				return '(' . $args[0] . ' OR ((' . $args[1] . ' is NULL AND ' . $args[3] . ' is NOT NULL) OR (' . $args[1] . ' is NOT NULL AND ' . $args[3] . ' is NULL)))';
			}
		}
		else
		{
			return $this->_rewrite_where($args[0]);
		}
	}

	/**
	* Oracle specific code to handle it's lack of sanity
	* @access private
	*/
	function _rewrite_where($where_clause)
	{
		preg_match_all('/\s*(AND|OR)?\s*([\w_.()]++)\s*(?:(=|<[=>]?|>=?|LIKE)\s*((?>\'(?>[^\']++|\'\')*+\'|[\d-.()]+))|((NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]+,? ?)*+\)))/', $where_clause, $result, PREG_SET_ORDER);
		$out = '';
		foreach ($result as $val)
		{
			if (!isset($val[5]))
			{
				if ($val[4] !== "''")
				{
					$out .= $val[0];
				}
				else
				{
					$out .= ' ' . $val[1] . ' ' . $val[2];
					if ($val[3] == '=')
					{
						$out .= ' is NULL';
					}
					else if ($val[3] == '<>')
					{
						$out .= ' is NOT NULL';
					}
				}
			}
			else
			{
				$in_clause = array();
				$sub_exp = substr($val[5], strpos($val[5], '(') + 1, -1);
				$extra = false;
				preg_match_all('/\'(?>[^\']++|\'\')*+\'|[\d-.]++/', $sub_exp, $sub_vals, PREG_PATTERN_ORDER);
				$i = 0;
				foreach ($sub_vals[0] as $sub_val)
				{
					// two things:
					// 1) This determines if an empty string was in the IN clausing, making us turn it into a NULL comparison
					// 2) This fixes the 1000 list limit that Oracle has (ORA-01795)
					if ($sub_val !== "''")
					{
						$in_clause[(int) $i++/1000][] = $sub_val;
					}
					else
					{
						$extra = true;
					}
				}
				if (!$extra && $i < 1000)
				{
					$out .= $val[0];
				}
				else
				{
					$out .= ' ' . $val[1] . '(';
					$in_array = array();

					// constuct each IN() clause
					foreach ($in_clause as $in_values)
					{
						$in_array[] = $val[2] . ' ' . (isset($val[6]) ? $val[6] : '') . 'IN(' . implode(', ', $in_values) . ')';
					}

					// Join the IN() clauses against a few ORs (IN is just a nicer OR anyway)
					$out .= implode(' OR ', $in_array);

					// handle the empty string case
					if ($extra)
					{
						$out .= ' OR ' . $val[2] . ' is ' . (isset($val[6]) ? $val[6] : '') . 'NULL';
					}
					$out .= ')';

					unset($in_array, $in_clause);
				}
			}
		}

		return $out;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_query($query = '', $cache_ttl = 0)
	{
		if ($query != '')
		{
			global $cache;

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG'))
			{
				$this->sql_report('start', $query);
			}
			else if (defined('PHPBB_DISPLAY_LOAD_TIME'))
			{
				$this->curtime = microtime(true);
			}

			$this->last_query_text = $query;
			$this->query_result = ($cache && $cache_ttl) ? $cache->sql_load($query) : false;
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

				$array = array();

				// We overcome Oracle's 4000 char limit by binding vars
				if (strlen($query) > 4000)
				{
					if (preg_match('/^(INSERT INTO[^(]++)\\(([^()]+)\\) VALUES[^(]++\\((.*?)\\)$/sU', $query, $regs))
					{
						if (strlen($regs[3]) > 4000)
						{
							$cols = explode(', ', $regs[2]);

							preg_match_all('/\'(?:[^\']++|\'\')*+\'|[\d-.]+/', $regs[3], $vals, PREG_PATTERN_ORDER);

/*						The code inside this comment block breaks clob handling, but does allow the
						database restore script to work.  If you want to allow no posts longer than 4KB
						and/or need the db restore script, uncomment this.


							if (count($cols) !== count($vals))
							{
								// Try to replace some common data we know is from our restore script or from other sources
								$regs[3] = str_replace("'||chr(47)||'", '/', $regs[3]);
								$_vals = explode(', ', $regs[3]);

								$vals = array();
								$is_in_val = false;
								$i = 0;
								$string = '';

								foreach ($_vals as $value)
								{
									if (strpos($value, "'") === false && !$is_in_val)
									{
										$vals[$i++] = $value;
										continue;
									}

									if (substr($value, -1) === "'")
									{
										$vals[$i] = $string . (($is_in_val) ? ', ' : '') . $value;
										$string = '';
										$is_in_val = false;

										if ($vals[$i][0] !== "'")
										{
											$vals[$i] = "''" . $vals[$i];
										}
										$i++;
										continue;
									}
									else
									{
										$string .= (($is_in_val) ? ', ' : '') . $value;
										$is_in_val = true;
									}
								}

								if ($string)
								{
									// New value if cols != value
									$vals[(count($cols) !== count($vals)) ? $i : $i - 1] .= $string;
								}

								$vals = array(0 => $vals);
							}
*/

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
						}
					}
					else if (preg_match_all('/^(UPDATE [\\w_]++\\s+SET )([\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+)(?:,\\s*[\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+)\\s+(WHERE.*)$/s', $query, $data, PREG_SET_ORDER))
					{
						if (strlen($data[0][2]) > 4000)
						{
							$update = $data[0][1];
							$where = $data[0][3];
							preg_match_all('/([\\w_]++)\\s*=\\s*(\'(?:[^\']++|\'\')*+\'|[\d-.]++)/', $data[0][2], $temp, PREG_SET_ORDER);
							unset($data);

							$cols = array();
							foreach ($temp as $value)
							{
								if (!empty($value[2]) && $value[2][0] === "'" && strlen($value[2]) > 4002) // check to see if this thing is greater than the max + 'x2
								{
									$cols[] = $value[1] . '=:' . strtoupper($value[1]);
									$array[$value[1]] = str_replace("''", "'", substr($value[2], 1, -1));
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

				switch (substr($query, 0, 6))
				{
					case 'DELETE':
						if (preg_match('/^(DELETE FROM [\w_]++ WHERE)((?:\s*(?:AND|OR)?\s*[\w_]+\s*(?:(?:=|<>)\s*(?>\'(?>[^\']++|\'\')*+\'|[\d-.]+)|(?:NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]+,? ?)*+\)))*+)$/', $query, $regs))
						{
							$query = $regs[1] . $this->_rewrite_where($regs[2]);
							unset($regs);
						}
					break;

					case 'UPDATE':
						if (preg_match('/^(UPDATE [\\w_]++\\s+SET [\\w_]+\s*=\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]++|:\w++)(?:, [\\w_]+\s*=\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]++|:\w++))*+\\s+WHERE)(.*)$/s',  $query, $regs))
						{
							$query = $regs[1] . $this->_rewrite_where($regs[2]);
							unset($regs);
						}
					break;

					case 'SELECT':
						$query = preg_replace_callback('/([\w_.]++)\s*(?:(=|<>)\s*(?>\'(?>[^\']++|\'\')*+\'|[\d-.]++|([\w_.]++))|(?:NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]++,? ?)*+\))/', array($this, '_rewrite_col_compare'), $query);
					break;
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

				if (defined('DEBUG'))
				{
					$this->sql_report('stop', $query);
				}
				else if (defined('PHPBB_DISPLAY_LOAD_TIME'))
				{
					$this->sql_time += microtime(true) - $this->curtime;
				}

				if (!$this->query_result)
				{
					return false;
				}

				if ($cache && $cache_ttl)
				{
					$this->open_queries[(int) $this->query_result] = $this->query_result;
					$this->query_result = $cache->sql_save($this, $query, $this->query_result, $cache_ttl);
				}
				else if (strpos($query, 'SELECT') === 0)
				{
					$this->open_queries[(int) $this->query_result] = $this->query_result;
				}
			}
			else if (defined('DEBUG'))
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

		$query = 'SELECT * FROM (SELECT /*+ FIRST_ROWS */ rownum AS xrownum, a.* FROM (' . $query . ') a WHERE rownum <= ' . ($offset + $total) . ') WHERE xrownum >= ' . $offset;

		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* {@inheritDoc}
	*/
	function sql_affectedrows()
	{
		return ($this->query_result) ? @ocirowcount($this->query_result) : false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($cache && $cache->sql_exists($query_id))
		{
			return $cache->sql_fetchrow($query_id);
		}

		if ($query_id)
		{
			$row = array();
			$result = ocifetchinto($query_id, $row, OCI_ASSOC + OCI_RETURN_NULLS);

			if (!$result || !$row)
			{
				return false;
			}

			$result_row = array();
			foreach ($row as $key => $value)
			{
				// Oracle treats empty strings as null
				if (is_null($value))
				{
					$value = '';
				}

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
	* {@inheritDoc}
	*/
	function sql_rowseek($rownum, &$query_id)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($cache && $cache->sql_exists($query_id))
		{
			return $cache->sql_rowseek($rownum, $query_id);
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
	* {@inheritDoc}
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
				if ($stmt)
				{
					$success = @ociexecute($stmt, OCI_DEFAULT);

					if ($success)
					{
						$temp_result = ocifetchinto($stmt, $temp_array, OCI_ASSOC + OCI_RETURN_NULLS);
						ocifreestatement($stmt);

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
			}
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_freeresult($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($cache && !is_object($query_id) && $cache->sql_exists($query_id))
		{
			return $cache->sql_freeresult($query_id);
		}

		if (isset($this->open_queries[(int) $query_id]))
		{
			unset($this->open_queries[(int) $query_id]);
			return ocifreestatement($query_id);
		}

		return false;
	}

	/**
	* {@inheritDoc}
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
	* Build NOT LIKE expression
	* @access private
	*/
	function _sql_not_like_expression($expression)
	{
		return $expression . " ESCAPE '\\'";
	}

	function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	function _sql_bit_and($column_name, $bit, $compare = '')
	{
		return 'BITAND(' . $column_name . ', ' . (1 << $bit) . ')' . (($compare) ? ' ' . $compare : '');
	}

	function _sql_bit_or($column_name, $bit, $compare = '')
	{
		return 'BITOR(' . $column_name . ', ' . (1 << $bit) . ')' . (($compare) ? ' ' . $compare : '');
	}

	/**
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		if (function_exists('ocierror'))
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
		}
		else
		{
			$error = array(
				'message'	=> $this->connect_error,
				'code'		=> '',
			);
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

				$html_table = false;

				// Grab a plan table, any will do
				$sql = "SELECT table_name
					FROM USER_TABLES
					WHERE table_name LIKE '%PLAN_TABLE%'";
				$stmt = ociparse($this->db_connect_id, $sql);
				ociexecute($stmt);
				$result = array();

				if (ocifetchinto($stmt, $result, OCI_ASSOC + OCI_RETURN_NULLS))
				{
					$table = $result['TABLE_NAME'];

					// This is the statement_id that will allow us to track the plan
					$statement_id = substr(md5($query), 0, 30);

					// Remove any stale plans
					$stmt2 = ociparse($this->db_connect_id, "DELETE FROM $table WHERE statement_id='$statement_id'");
					ociexecute($stmt2);
					ocifreestatement($stmt2);

					// Explain the plan
					$sql = "EXPLAIN PLAN
						SET STATEMENT_ID = '$statement_id'
						FOR $query";
					$stmt2 = ociparse($this->db_connect_id, $sql);
					ociexecute($stmt2);
					ocifreestatement($stmt2);

					// Get the data from the plan
					$sql = "SELECT operation, options, object_name, object_type, cardinality, cost
						FROM plan_table
						START WITH id = 0 AND statement_id = '$statement_id'
						CONNECT BY PRIOR id = parent_id
							AND statement_id = '$statement_id'";
					$stmt2 = ociparse($this->db_connect_id, $sql);
					ociexecute($stmt2);

					$row = array();
					while (ocifetchinto($stmt2, $row, OCI_ASSOC + OCI_RETURN_NULLS))
					{
						$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
					}

					ocifreestatement($stmt2);

					// Remove the plan we just made, we delete them on request anyway
					$stmt2 = ociparse($this->db_connect_id, "DELETE FROM $table WHERE statement_id='$statement_id'");
					ociexecute($stmt2);
					ocifreestatement($stmt2);
				}

				ocifreestatement($stmt);

				if ($html_table)
				{
					$this->html_hold .= '</table>';
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @ociparse($this->db_connect_id, $query);
				if ($result)
				{
					$success = @ociexecute($result, OCI_DEFAULT);
					if ($success)
					{
						$row = array();

						while (ocifetchinto($result, $row, OCI_ASSOC + OCI_RETURN_NULLS))
						{
							// Take the time spent on parsing rows into account
						}
						@ocifreestatement($result);
					}
				}

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}
