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

	define('SQL_LAYER', 'mysqli');
	include($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
* @package dbal
* MySQLi Database Abstraction Layer
* Minimum Requirement is MySQL 4.1+ and the mysqli-extension
*/
class dbal_mysqli extends dbal
{
	var $indexed = 0;

	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->db_connect_id = ($this->persistency) ? @mysqli_pconnect($this->server, $this->user, $sqlpassword) : @mysqli_connect($this->server, $this->user, $sqlpassword);

		if ($this->db_connect_id && $this->dbname != '')
		{
			if (@mysqli_select_db($this->db_connect_id, $this->dbname))
			{
				return $this->db_connect_id;
			}
		}

		return $this->sql_error('');
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
			@mysqli_commit($this->db_connect_id);
		}

		return @mysqli_close($this->db_connect_id);
	}

	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				$result = @mysqli_autocommit($this->db_connect_id, false);
				$this->transaction = true;
				break;

			case 'commit':
				$result = @mysqli_commit($this->db_connect_id);
				@mysqli_autocommit($this->db_connect_id, true);
				$this->transaction = false;

				if (!$result)
				{
					@mysqli_rollback($this->db_connect_id);
					@mysqli_autocommit($this->db_connect_id, true);
				}
				break;

			case 'rollback':
				$result = @mysqli_rollback($this->db_connect_id);
				@mysqli_autocommit($this->db_connect_id, true);
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

				if (($this->query_result = @mysqli_query($this->db_connect_id, $query)) === false)
				{
					$this->sql_error($query);
				}

				if (is_object($this->query_result))
				{
					$this->query_result->cur_index = $this->indexed++;
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

			$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);

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

		return ($query_id) ? @mysqli_num_rows($query_id) : false;
	}

	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @mysqli_affected_rows($this->db_connect_id) : false;
	}

	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (!is_object($query_id) && isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

		return ($query_id) ? @mysqli_fetch_assoc($query_id) : false;
	}

	function sql_fetchrowset($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			$cur_index = (is_object($query_id)) ? $query_id->cur_index : $query_id;

			unset($this->rowset[$cur_index]);
			unset($this->row[$cur_index]);
			
			$result = array();
			while ($this->rowset[$cur_index] = $this->sql_fetchrow($query_id))
			{
				$result[] = $this->rowset[$cur_index];
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
				@mysqli_data_seek($query_id, $rownum);
				$row = @mysqli_fetch_assoc($query_id);
				$result = isset($row[$field]) ? $row[$field] : false;
			}
			else
			{
				$cur_index = (is_object($query_id)) ? $query_id->cur_index : $query_id;
	
				if (empty($this->row[$cur_index]) && empty($this->rowset[$cur_index]))
				{
					if ($this->row[$cur_index] = $this->sql_fetchrow($query_id))
					{
						$result = $this->row[$cur_index][$field];
					}
				}
				else
				{
					if ($this->rowset[$cur_index])
					{
						$result = $this->rowset[$cur_index][$field];
					}
					elseif ($this->row[$cur_index])
					{
						$result = $this->row[$cur_index][$field];
					}
				}
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

		return ($query_id) ? @mysqli_data_seek($query_id, $rownum) : false;
	}

	function sql_nextid()
	{
		return ($this->db_connect_id) ? @mysqli_insert_id($this->db_connect_id) : false;
	}

	function sql_freeresult($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		$cur_index = (is_object($query_id)) ? $query_id->cur_index : $query_id;

		unset($this->rowset[$cur_index]);
		unset($this->row[$cur_index]);

		if (is_object($query_id))
		{
			$this->indexed--;
			return @mysqli_free_result($query_id);
		}
		else
		{
			return false;
		}
	}

	function sql_escape($msg)
	{
		return @mysqli_real_escape_string($this->db_connect_id, $msg);
	}
	
	function db_sql_error()
	{
		return array(
			'message'	=> @mysqli_error($this->db_connect_id),
			'code'		=> @mysqli_errno($this->db_connect_id)
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

				$explain_query = $query;
				if (preg_match('/UPDATE ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}
				elseif (preg_match('/DELETE FROM ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}

				if (preg_match('/^SELECT/', $explain_query))
				{
					$html_table = FALSE;

					if ($result = @mysqli_query($this->db_connect_id, "EXPLAIN $explain_query"))
					{
						while ($row = @mysqli_fetch_assoc($result))
						{
							if (!$html_table && sizeof($row))
							{
								$html_table = TRUE;
								$html_hold .= '<table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center"><tr>';
								
								foreach (array_keys($row) as $val)
								{
									$html_hold .= '<th nowrap="nowrap">' . (($val) ? ucwords(str_replace('_', ' ', $val)) : '&nbsp;') . '</th>';
								}
								$html_hold .= '</tr>';
							}
							$html_hold .= '<tr>';

							$class = 'row1';
							foreach (array_values($row) as $val)
							{
								$class = ($class == 'row1') ? 'row2' : 'row1';
								$html_hold .= '<td class="' . $class . '">' . (($val) ? $val : '&nbsp;') . '</td>';
							}
							$html_hold .= '</tr>';
						}
					}

					if ($html_table)
					{
						$html_hold .= '</table>';
					}
				}

				$curtime = explode(' ', microtime());
				$curtime = $curtime[0] + $curtime[1];
				break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @mysqli_query($this->db_connect_id, $query);
				while ($void = @mysqli_fetch_assoc($result))
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

				@mysqli_free_result($result);
				$cache_num_queries++;
				break;
		}
	}
}

} // if ... define

?>