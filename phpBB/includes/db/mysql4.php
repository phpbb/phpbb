<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : mysql4.php 
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!defined('SQL_LAYER'))
{

define('SQL_LAYER', 'mysql4');

class sql_db
{
	var $db_connect_id;
	var $query_result;
	var $return_on_error = false;
	var $transaction = false;
	var $sql_time = 0;
	var $num_queries = 0;
	var $open_queries = array();

	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->password = $sqlpassword;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->db_connect_id = ($this->persistency) ? @mysqli_pconnect($this->server, $this->user, $this->password) : @mysqli_connect($this->server, $this->user, $this->password);

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

	function sql_return_on_error($fail = false)
	{
		$this->return_on_error = $fail;
	}

	function sql_num_queries()
	{
		return $this->num_queries;
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

				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('stop', $query);
				}

				if ($cache_ttl && method_exists($cache, 'sql_save'))
				{
					$cache->sql_save($query, $this->query_result, $cache_ttl);
					// mysql_free_result called within sql_save()
				}
/*				else if (strpos($query, 'SELECT') !== false && $this->query_result)
				{
				}*/
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

	// Idea for this from Ikonboard
	function sql_build_array($query, $assoc_ary = false)
	{
		if (!is_array($assoc_ary))
		{
			return false;
		}

		$fields = array();
		$values = array();
		if ($query == 'INSERT')
		{
			foreach ($assoc_ary as $key => $var)
			{
				$fields[] = $key;

				if (is_null($var))
				{
					$values[] = 'NULL';
				}
				elseif (is_string($var))
				{
					$values[] = "'" . $this->sql_escape($var) . "'";
				}
				else
				{
					$values[] = (is_bool($var)) ? intval($var) : $var;
				}
			}

			$query = ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
		}
		else if ($query == 'UPDATE' || $query == 'SELECT')
		{
			$values = array();
			foreach ($assoc_ary as $key => $var)
			{
				if (is_null($var))
				{
					$values[] = "$key = NULL";
				}
				elseif (is_string($var))
				{
					$values[] = "$key = '" . $this->sql_escape($var) . "'";
				}
				else
				{
					$values[] = (is_bool($var)) ? "$key = " . intval($var) : "$key = $var";
				}
			}
			$query = implode(($query == 'UPDATE') ? ', ' : ' AND ', $values);
		}

		return $query;
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
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
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
				@mysqli_data_seek($query_id, $rownum);
				$row = @mysqli_fetch_row($query_id);
				$result = isset($row[$field]) ? $row[$field] : false;
			}
			else
			{
				if (empty($this->row[$query_id]) && empty($this->rowset[$query_id]))
				{
					if ($this->sql_fetchrow())
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

		return (is_object($query_id)) ? @mysqli_free_result($query_id) : false;
	}

	function sql_escape($msg)
	{
		return @mysqli_real_escape_string($this->db_connect_id, $msg);
	}
	
	function sql_error($sql = '')
	{
		if (!$this->return_on_error)
		{
			$this_page = (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
			$this_page .= '&' . ((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : (isset($_ENV['QUERY_STRING']) ? $_ENV['QUERY_STRING'] : ''));

			$message = '<u>SQL ERROR</u> [ ' . SQL_LAYER . ' ]<br /><br />' . @mysqli_error($this->db_connect_id) . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . htmlspecialchars($this_page) . (($sql != '') ? '<br /><br /><u>SQL</u><br /><br />' . $sql : '') . '<br />';

			if ($this->transaction)
			{
				$this->sql_transaction('rollback');
			}
			
			trigger_error($message, E_USER_ERROR);
		}

		$result = array(
			'message'	=> @mysqli_error($this->db_connect_id),
			'code'		=> @mysqli_errno($this->db_connect_id)
		);

		return $result;
	}

	function sql_report($mode, $query = '')
	{
		if (empty($_GET['explain']))
		{
			return;
		}

		global $db, $cache, $starttime, $phpbb_root_path;
		static $curtime, $query_hold, $html_hold;
		static $sql_report = '';
		static $cache_num_queries = 0;

		if (!$query && !empty($query_hold))
		{
			$query = $query_hold;
		}

		switch ($mode)
		{
			case 'display':
				if (!empty($cache))
				{
					$cache->unload();
				}
				$db->sql_close();

				$mtime = explode(' ', microtime());
				$totaltime = $mtime[0] + $mtime[1] - $starttime;

				echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8869-1"><meta http-equiv="Content-Style-Type" content="text/css"><link rel="stylesheet" href="' . $phpbb_root_path . 'adm/subSilver.css" type="text/css"><style type="text/css">' . "\n";
				echo 'th { background-image: url(\'' . $phpbb_root_path . 'adm/images/cellpic3.gif\') }' . "\n";
				echo 'td.cat	{ background-image: url(\'' . $phpbb_root_path . 'adm/images/cellpic1.gif\') }' . "\n";
				echo '</style><title>' . $msg_title . '</title></head><body>';
				echo '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td><a href="' . htmlspecialchars(preg_replace('/&explain=([^&]*)/', '', $_SERVER['REQUEST_URI'])) . '"><img src="' . $phpbb_root_path . 'adm/images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></a></td><td width="100%" background="' . $phpbb_root_path . 'adm/images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle">SQL Report</span> &nbsp; &nbsp; &nbsp;</td></tr></table><br clear="all"/><table width="95%" cellspacing="1" cellpadding="4" border="0" align="center"><tr><td height="40" align="center" valign="middle"><b>Page generated in ' . round($totaltime, 4) . " seconds with {$this->num_queries} queries" . (($cache_num_queries) ? " + $cache_num_queries " . (($cache_num_queries == 1) ? 'query' : 'queries') . ' returning data from cache' : '') . '</b></td></tr><tr><td align="center" nowrap="nowrap">Time spent on MySQL queries: <b>' . round($this->sql_time, 5) . 's</b> | Time spent on PHP: <b>' . round($totaltime - $this->sql_time, 5) . 's</b></td></tr></table><table width="95%" cellspacing="1" cellpadding="4" border="0" align="center"><tr><td>';
				echo $sql_report;
				echo '</td></tr></table><br /></body></html>';
				exit;
				break;

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

			case 'stop':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$sql_report .= '<hr width="100%"/><br /><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0"><tr><th>Query #' . $this->num_queries . '</th></tr><tr><td class="row1"><textarea style="font-family:\'Courier New\',monospace;width:100%" rows="5">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td></tr></table> ' . $html_hold . '<p align="center">';

				if ($this->query_result)
				{
					if (preg_match('/^(UPDATE|DELETE|REPLACE)/', $query))
					{
						$sql_report .= "Affected rows: <b>" . $this->sql_affectedrows($this->query_result) . '</b> | ';
					}
					$sql_report .= 'Before: ' . sprintf('%.5f', $curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed: <b>' . sprintf('%.5f', $endtime - $curtime) . 's</b>';
				}
				else
				{
					$error = $this->sql_error();
					$sql_report .= '<b style="color: red">FAILED</b> - MySQL Error ' . $error['code'] . ': ' . htmlspecialchars($error['message']);
				}

				$sql_report .= '</p>';

				$this->sql_time += $endtime - $curtime;
				break;
		}
	}
} // class sql_db

} // if ... define

?>