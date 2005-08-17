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
if (!defined('SQL_LAYER'))
{

	define('SQL_LAYER', 'mssql_odbc');
	include($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* @package dbal
* MSSQL ODBC Database Abstraction Layer for MSSQL
* Minimum Requirement is Version 2000+
*/
class dbal_mssql_odbc extends dbal
{
	var $result_rowset = array();
	var $field_names = array();
	var $field_types = array();
	var $num_rows = array();
	var $current_row = array();

	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->db_connect_id = ($this->persistency) ? @odbc_pconnect($this->server, $this->user, $sqlpassword) : @odbc_connect($this->server, $this->user, $sqlpassword);

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	//
	// Other base methods
	//
	function sql_close()
	{
		if (!$this->db_connect_id)
		{
			return false;
		}

		if ($this->transaction)
		{
			@odbc_commit($this->db_connect_id);
		}

		if (sizeof($this->result_rowset))
		{
			unset($this->result_rowset);
			unset($this->field_names);
			unset($this->field_types);
			unset($this->num_rows);
			unset($this->current_row);
		}

		if (sizeof($this->open_queries))
		{
			foreach ($this->open_queries as $i_query_id => $query_id)
			{
				@odbc_free_result($query_id);
			}
		}

		return @odbc_close($this->db_connect_id);
	}

	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				$result = @odbc_autocommit($this->db_connect_id, false);
				$this->transaction = true;
				break;

			case 'commit':
				$result = @odbc_commit($this->db_connect_id);
				@odbc_autocommit($this->db_connect_id, true);
				$this->transaction = false;

				if (!$result)
				{
					@odbc_rollback($this->db_connect_id);
					@odbc_autocommit($this->db_connect_id, true);
				}
				break;

			case 'rollback':
				$result = @odbc_rollback($this->db_connect_id);
				@odbc_autocommit($this->db_connect_id, true);
				$this->transaction = false;
				break;

			default:
				$result = true;
		}

		return $result;
	}

	// Base query method
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

			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;

			if (!$this->query_result)
			{
				$this->num_queries++;

				if (($this->query_result = $this->_odbc_execute_query($query)) === false)
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
					// odbc_free_result called within sql_save()
				}
				else if (strpos($query, 'SELECT') !== false && $this->query_result)
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

	function _odbc_execute_query($query)
	{
		$result = false;
		
		if (eregi("^SELECT ", $query))
		{
			$result = @odbc_exec($this->db_connect_id, $query); 

			if ($result)
			{
				if (empty($this->field_names[$result]))
				{
					for ($i = 1, $j = @odbc_num_fields($result) + 1; $i < $j; $i++)
					{
						$this->field_names[$result][] = @odbc_field_name($result, $i);
						$this->field_types[$result][] = @odbc_field_type($result, $i);
					}
				}

				$this->current_row[$result] = 0;
				$this->result_rowset[$result] = array();

				$row_outer = (isset($row_offset)) ? $row_offset + 1 : 1;
				$row_outer_max = (isset($num_rows)) ? $row_offset + $num_rows + 1 : 1E9;
				$row_inner = 0;

				while (@odbc_fetch_row($result, $row_outer) && $row_outer < $row_outer_max)
				{
					for ($i = 0, $j = sizeof($this->field_names[$result]); $i < $j; $i++)
					{
						$this->result_rowset[$result][$row_inner][$this->field_names[$result][$i]] = stripslashes(@odbc_result($result, $i + 1));
					}

					$row_outer++;
					$row_inner++;
				}

				$this->num_rows[$result] = sizeof($this->result_rowset[$result]);	
			}
		}
		else if (eregi("^INSERT ", $query))
		{
			$result = @odbc_exec($this->db_connect_id, $query);

			if ($result)
			{
				$result_id = @odbc_exec($this->db_connect_id, 'SELECT @@IDENTITY');
				if ($result_id)
				{
					if (@odbc_fetch_row($result_id))
					{
						$this->next_id[$this->db_connect_id] = @odbc_result($result_id, 1);	
						$this->affected_rows[$this->db_connect_id] = @odbc_num_rows($result);
					}
				}
			}
		}
		else
		{
			$result = @odbc_exec($this->db_connect_id, $query);

			if ($result)
			{
				$this->affected_rows[$this->db_connect_id] = @odbc_num_rows($result);
			}
		}

		return $result;
	}

	function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0) 
	{ 
		if ($query != '') 
		{
			$this->query_result = false; 

			// if $total is set to 0 we do not want to limit the number of rows
			if ($total == 0)
			{
				$total = -1;
			}

			$row_offset = ($total) ? $offset : '';
			$num_rows = ($total) ? $total : $offset;

			$query = 'SELECT TOP ' . ($row_offset + $num_rows) . ' ' . substr($query, 6);

			return $this->sql_query($query, $cache_ttl); 
		} 
		else 
		{ 
			return false; 
		} 
	}

	// Other query methods
	//
	// NOTE :: Want to remove _ALL_ reliance on sql_numrows from core code ...
	//         don't want this here by a middle Milestone
	function sql_numrows($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @$this->num_rows($query_id) : false;
	}

	function sql_affectedrows()
	{
		return ($this->affected_rows[$this->db_connect_id]) ? $this->affected_rows[$this->db_connect_id] : false;
	}

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

		return ($this->num_rows[$query_id] && $this->current_row[$query_id] < $this->num_rows[$query_id]) ? $this->result_rowset[$query_id][$this->current_row[$query_id]++] : false;
	}

	function sql_fetchrowset($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($this->num_rows[$query_id]) ? $this->result_rowset[$query_id] : false;
	}

	function sql_fetchfield($field, $rownum = -1, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($rownum < $this->num_rows[$query_id])
			{
				$getrow = ($rownum == -1) ? $this->current_row[$query_id] - 1 : $rownum;

				return $this->result_rowset[$query_id][$getrow][$this->field_names[$query_id][$field]];
			}
		}

		return false;
	}

	function sql_rowseek($rownum, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($this->current_row[$query_id]))
		{
			$this->current_row[$query_id] = $rownum;
			return true;
		}

		return false;
	}

	function sql_nextid()
	{
		return ($this->next_id[$this->db_connect_id]) ? $this->next_id[$this->db_connect_id] : false;
	}

	function sql_freeresult($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($this->open_queries[(int) $query_id]))
		{
			unset($this->open_queries[(int) $query_id]);
			unset($this->num_rows[$query_id]);
			unset($this->current_row[$query_id]);
			unset($this->result_rowset[$query_id]);
			unset($this->field_names[$query_id]);
			unset($this->field_types[$query_id]);

			return @odbc_free_result($query_id);
		}

		return false;
	}

	function sql_escape($msg)
	{
		return str_replace("'", "''", str_replace('\\', '\\\\', $msg));
	}

	function db_sql_error()
	{
		return array(
			'message'	=> @odbc_errormsg(),
			'code'		=> @odbc_error()
		);
	}

	function _sql_report($mode, $query = '')
	{
		global $cache, $starttime, $phpbb_root_path;
		static $curtime, $query_hold, $html_hold;
		static $sql_report = '';
		static $cache_num_queries = 0;

		switch ($mode)
		{
			case 'start':
				$query_hold = $query;
				$html_hold = '';

				$curtime = explode(' ', microtime());
				$curtime = $curtime[0] + $curtime[1];
				break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = $this->_odbc_execute_query($query);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$time_cache = $endtime - $curtime;
				$time_db = $splittime - $endtime;
				$color = ($time_db > $time_cache) ? 'green' : 'red';

				$sql_report .= '<hr width="100%"/><br /><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0"><tr><th>Query results obtained from the cache</th></tr><tr><td class="row1"><textarea style="font-family:\'Courier New\',monospace;width:100%" rows="5">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td></tr></table><p align="center">';

				$sql_report .= 'Before: ' . sprintf('%.5f', $curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed [cache]: <b style="color: ' . $color . '">' . sprintf('%.5f', ($time_cache)) . 's</b> | Elapsed [db]: <b>' . sprintf('%.5f', $time_db) . 's</b></p>';

				// Pad the start time to not interfere with page timing
				$starttime += $time_db;

				@odbc_free_result($result);
				$cache_num_queries++;
				break;
		}
	}

}

} // if ... define

?>