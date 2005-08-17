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

	define('SQL_LAYER', 'postgresql');
	include($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* @package dbal
* PostgreSQL Database Abstraction Layer
* Minimum Requirement is Version 7.3+
*/
class dbal_postgres extends dbal
{

	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->connect_string = '';

		if ($sqluser)
		{
			$this->connect_string .= "user=$sqluser ";
		}

		if ($sqlpassword)
		{
			$this->connect_string .= "password=$sqlpassword ";
		}

		if ($sqlserver)
		{
			if (ereg(":", $sqlserver))
			{
				list($sqlserver, $sqlport) = split(":", $sqlserver);
				$this->connect_string .= "host=$sqlserver port=$sqlport ";
			}
			else
			{
				if ($sqlserver != "localhost")
				{
					$this->connect_string .= "host=$sqlserver ";
				}
			
				if ($port)
				{
					$this->connect_string .= "port=$port ";
				}
			}
		}

		if ($database)
		{
			$this->dbname = $database;
			$this->connect_string .= "dbname=$database";
		}

		$this->persistency = $persistency;

		$this->db_connect_id = ($this->persistency) ? @pg_pconnect($this->connect_string) : @pg_connect($this->connect_string);

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
			@pg_exec($this->db_connect_id, 'COMMIT');
		}

		return @pg_close($this->db_connect_id);
	}

	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				$result = @pg_exec($this->db_connect_id, 'BEGIN');
				$this->transaction = true;
				break;

			case 'commit':
				$result = @pg_exec($this->db_connect_id, 'COMMIT');
				$this->transaction = false;

				if (!$result)
				{
					@pg_exec($this->db_connect_id, 'ROLLBACK');
				}
				break;

			case 'rollback':
				$result = @pg_exec($this->db_connect_id, 'ROLLBACK');
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
				$this->last_query_text = $query;

				if (($this->query_result = @pg_exec($this->db_connect_id, $query)) === false)
				{
					$this->sql_error($query);
				}

				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('stop', $query);
				}

				if ($cache_ttl && method_exists($cache, 'sql_save'))
				{
					$cache->sql_save($query, $this->query_result, $cache_ttl);
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

			// if $total is set to 0 we do not want to limit the number of rows
			if ($total == 0)
			{
				$total = -1;
			}

			$query .= "\n LIMIT $total OFFSET $offset";

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

		return ($query_id) ? @pg_numrows($query_id) : false;
	}

	function sql_affectedrows($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @pg_cmdtuples($query_id) : false;
	}

	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (!isset($this->rownum[$query_id]))
		{
			$this->rownum[$query_id] = 0;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

		$result = @pg_fetch_array($query_id, NULL, PGSQL_ASSOC);
		
		if ($result)
		{
			$this->rownum[$query_id]++;
		}

		return $result;
	}

	function sql_fetchrowset($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		$result = array();

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
				if (@function_exists('pg_result_seek'))
				{
					@pg_result_seek($query_id, $rownum);
					$row = @pg_fetch_assoc($query_id);
					$result = isset($row[$field]) ? $row[$field] : false;
				}
				else
				{
					$this->sql_rowseek($offset, $query_id);
					$row = $this->sql_fetchrow($query_id);
					$result = isset($row[$field]) ? $row[$field] : false;
				}
			}
			else
			{
				if (empty($this->row[$query_id]) && empty($this->rowset[$query_id]))
				{
					if ($this->row[$query_id] = $this->sql_fetchrow($query_id))
					{
						$result = $this->row[$query_id][$field];
					}
				}
				else
				{
					if ($this->rowset[$query_id])
					{
						$result = $this->rowset[$query_id][$field];
					}
					elseif ($this->row[$query_id])
					{
						$result = $this->row[$query_id][$field];
					}
				}
			}
			return $result;
		}
		return false;
	}

	function sql_rowseek($offset, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($offset > -1)
			{
				if (@function_exists('pg_result_seek'))
				{
					@pg_result_seek($query_id, $rownum);
				}
				else
				{
					for ($i = $this->rownum[$query_id]; $i < $offset; $i++)
					{
						$this->sql_fetchrow($query_id);
					}
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	function sql_nextid()
	{
		$query_id = $this->query_result;

		if ($query_id && $this->last_query_text != '')
		{
			if (preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $this->last_query_text, $tablename))
			{
				$query = "SELECT currval('" . $tablename[1] . "_id_seq') AS last_value";
				$temp_q_id =  @pg_exec($this->db_connect_id, $query);
				if (!$temp_q_id)
				{
					return false;
				}

				$temp_result = @pg_fetch_array($temp_q_id, NULL, PGSQL_ASSOC);

				return ($temp_result) ? $temp_result['last_value'] : false;
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

		return (is_resource($query_id)) ? @pg_freeresult($query_id) : false;
	}

	function sql_escape($msg)
	{
		return str_replace("'", "''", str_replace('\\', '\\\\', $msg));
	}

	function db_sql_error()
	{
		return array(
			'message'	=> @pg_errormessage(),
			'code'		=> ''
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

				$result = @pg_exec($this->db_connect_id, $query);
				while ($void = @pg_fetch_array($result, NULL, PGSQL_ASSOC))
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

				@pg_freeresult($result);
				$cache_num_queries++;
				break;
		}
	}

}

} // if ... defined

?>