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
if(!defined('SQL_LAYER'))
{

	define('SQL_LAYER', 'oracle');
	include($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* @package dbal
* Oracle Database Abstraction Layer
*/
class dbal_oracle extends dbal
{
	var $last_query_text = '';

	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;
		
		$this->db_connect_id = ($this->persistency) ? @ociplogon($this->user, $sqlpassword, $this->server) : @ocinlogon($this->user, $sqlpassword, $this->server);

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
			@ocicommit($this->db_connect_id);
		}

		if (sizeof($this->open_queries))
		{
			foreach ($this->open_queries as $i_query_id => $query_id)
			{
				@ocifreestatement($query_id);
			}
		}

		return @ocilogoff($this->db_connect_id);
	}

	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				$result = true;
				$this->transaction = true;
				break;

			case 'commit':
				$result = @ocicommit($this->db_connect_id);
				$this->transaction = false;

				if (!$result)
				{
					@ocirollback($this->db_connect_id);
				}
				break;

			case 'rollback':
				$result = @ocirollback($this->db_connect_id);
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
		
			$this->last_query_text = $query;
			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
			
			if (!$this->query_result)
			{
				$this->num_queries++;

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

	function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0) 
	{ 
		if ($query != '') 
		{
			$this->query_result = false; 

			$query = 'SELECT * FROM (SELECT /*+ FIRST_ROWS */ rownum AS xrownum, a.* FROM (' . $query . ') a WHERE rownum <= ' . ($offset + $total) . ') WHERE xrownum >= ' . $offset;

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

		$result = @ocifetchstatement($query_id, $this->rowset);
		// OCIFetchStatment kills our query result so we have to execute the statment again
		// if we ever want to use the query_id again.
		@ociexecute($query_id, OCI_DEFAULT);

		return $result;
	}

	function sql_affectedrows()
	{
		$query_id = $this->query_result;

		return ($query_id) ? @ocirowcount($query_id) : false;
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
				$value = ($value->size()) ? $value->read($value->size()) : '';
			}
			
			$result_row[strtolower($key)] = $value;
		}
		$this->row[$query_id] = $result_row;

		return $this->row[$query_id];
	}

	function sql_fetchrowset($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);

			$result = array();
			while ($this->rowset[$query_id] = $this->sql_fetchrow($query_id))
			{
				$result[] = $this->rowset[$query_id];
			}
			return $result;
		}
		
		return false;
	}

	function sql_fetchfield($field, $rownum = -1, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($rownum > -1)
			{
				// Reset the internal rownum pointer.
				@ociexecute($query_id, OCI_DEFAULT);
				for ($i = 0; $i < $rownum; $i++)
				{
					// Move the interal pointer to the row we want
					@ocifetch($query_id);
				}
				
				// Get the field data.
				$result = @ociresult($query_id, strtoupper($field));
			}
			else
			{
				// The internal pointer should be where we want it
				// so we just grab the field out of the current row.
				$result = @ociresult($query_id, strtoupper($field));
			}

			return $result;
		}
		
		return false;
	}

	function sql_rowseek($rownum, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			@ociexecute($query_id, OCI_DEFAULT);

			for ($i = 0; $i < $rownum; $i++)
			{
				@ocifetch($query_id);
			}
			$result = @ocifetch($query_id);

			return $result;
		}
		
		return false;
	}

	function sql_nextid()
	{
		$query_id = $this->query_result;

		if ($query_id && $this->last_query_text != '')
		{
			if (preg_match('#^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)#is', $this->last_query_text, $tablename))
			{
				$query = 'SELECT ' . $tablename[1] . '_id_seq.currval FROM DUAL';
				$stmt = @ociparse($this->db_connect_id, $query);
				@ociexecute($stmt,OCI_DEFAULT );

				$temp_result = @ocifetchinto($stmt, $temp_result, OCI_ASSOC + OCI_RETURN_NULLS);

				if ($temp_result)
				{
					return $temp_result['CURRVAL'];
				}
				else
				{
					return false;
				}
			}
		}

		return false;
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
			return @ocifreestatement($query_id);
		}

		return false;
	}

	function sql_escape($msg)
	{
		return str_replace("'", "''", str_replace('\\', '\\\\', $msg));
	}

	function db_sql_error()
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

				$result = @ociparse($this->db_connect_id, $query);
				$success = @ociexecute($result, OCI_DEFAULT);
				$row = array();

				while ($void = @ocifetchinto($query_id, $row, OCI_ASSOC + OCI_RETURN_NULLS))
				{
					// Take the time spent on parsing rows into account
				}
				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$time_cache = $endtime - $curtime;
				$time_db = $splittime - $endtime;
				$color = ($time_db > $time_cache) ? 'green' : 'red';

				$sql_report .= '<hr width="100%"/><br /><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0"><tr><th>Query results obtained from the cache</th></tr><tr><td class="row1"><textarea style="font-family:\'Courier New\',monospace;width:100%" rows="5">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td></tr></table><p align="center">';

				$sql_report .= 'Before: ' . sprintf('%.5f', $curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed [cache]: <b style="color: ' . $color . '">' . sprintf('%.5f', ($time_cache)) . 's</b> | Elapsed [db]: <b>' . sprintf('%.5f', $time_db) . 's</b></p>';

				// Pad the start time to not interfere with page timing
				$starttime += $time_db;

				@ocifreestatement($result);
				$cache_num_queries++;
				break;
		}
	}


}

} // if ... define

?>