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
* @package dbal
* Database Abstraction Layer
*/
class dbal
{
	var $db_connect_id;
	var $query_result;
	var $return_on_error = false;
	var $transaction = false;
	var $sql_time = 0;
	var $num_queries = 0;
	var $open_queries = array();

	var $curtime = 0;
	var $query_hold = '';
	var $html_hold = '';
	var $sql_report = '';
	var $cache_num_queries = 0;
	
	var $persistency = false;
	var $user = '';
	var $server = '';
	var $dbname = '';
	
	/**
	* return on error or display error message
	*/
	function sql_return_on_error($fail = false)
	{
		$this->return_on_error = $fail;
	}

	/**
	* Return number of sql queries used (cached and real queries are counted the same)
	*/
	function sql_num_queries()
	{
		return $this->num_queries;
	}

	/**
	* DBAL garbage collection, close sql connection
	*/
	function sql_close()
	{
		if (!$this->db_connect_id)
		{
			return false;
		}

		if ($this->transaction)
		{
			$this->sql_transaction('commit');
		}

		if (sizeof($this->open_queries))
		{
			foreach ($this->open_queries as $i_query_id => $query_id)
			{
				$this->sql_freeresult($query_id);
			}
		}
		
		return $this->_sql_close();
	}

	/**
	* Fetch all rows
	*/
	function sql_fetchrowset($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			$result = array();
			while ($row = $this->sql_fetchrow($query_id))
			{
				$result[] = $row;
			}

			return $result;
		}
		
		return false;
	}

	/**
	* Build sql statement from array for insert/update/select statements
	*
	* Idea for this from Ikonboard
	* Possible query values: INSERT, INSERT_SELECT, MULTI_INSERT, UPDATE, SELECT
	*/
	function sql_build_array($query, $assoc_ary = false)
	{
		if (!is_array($assoc_ary))
		{
			return false;
		}

		$fields = array();
		$values = array();
		if ($query == 'INSERT' || $query == 'INSERT_SELECT')
		{
			foreach ($assoc_ary as $key => $var)
			{
				$fields[] = $key;

				if (is_null($var))
				{
					$values[] = 'NULL';
				}
				else if (is_string($var))
				{
					$values[] = "'" . $this->sql_escape($var) . "'";
				}
				else if (is_array($var) && is_string($var[0]))
				{
					$values[] = $var[0];
				}
				else
				{
					$values[] = (is_bool($var)) ? intval($var) : $var;
				}
			}

			$query = ($query == 'INSERT') ? ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')' : ' (' . implode(', ', $fields) . ') SELECT ' . implode(', ', $values) . ' ';
		}
		else if ($query == 'MULTI_INSERT')
		{
			$ary = array();
			foreach ($assoc_ary as $id => $sql_ary)
			{
				$values = array();
				foreach ($sql_ary as $key => $var)
				{
					if (is_null($var))
					{
						$values[] = 'NULL';
					}
					else if (is_string($var))
					{
						$values[] = "'" . $this->sql_escape($var) . "'";
					}
					else
					{
						$values[] = (is_bool($var)) ? intval($var) : $var;
					}
				}
				$ary[] = '(' . implode(', ', $values) . ')';
			}

			$query = ' (' . implode(', ', array_keys($assoc_ary[0])) . ') VALUES ' . implode(', ', $ary);
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
				else if (is_string($var))
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

	/**
	* display sql error page
	*/
	function sql_error($sql = '')
	{
		global $auth, $user;

		$error = $this->_sql_error();

		if (!$this->return_on_error)
		{
			$message = '<u>SQL ERROR</u> [ ' . SQL_LAYER . ' ]<br /><br />' . $error['message'] . ' [' . $error['code'] . ']';

			// Show complete SQL error and path to administrators only
			if ($auth->acl_get('a_'))
			{			
				$this_page = (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
				$this_page .= '&' . ((isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : (isset($_ENV['QUERY_STRING']) ? $_ENV['QUERY_STRING'] : ''));

				$message .= '<br /><br /><u>CALLING PAGE</u><br /><br />'  . htmlspecialchars($this_page) . (($sql != '') ? '<br /><br /><u>SQL</u><br /><br />' . $sql : '') . '<br />';
			}
			else
			{
				$message .= '<br /><br />' . $user->lang['SQL_ERROR_OCCURRED'];
			}

			if ($this->transaction)
			{
				$this->sql_transaction('rollback');
			}
			
			trigger_error($message, E_USER_ERROR);
		}
		
		return $error;
	}

	/**
	* Explain queries
	* @child _sql_report
	*/
	function sql_report($mode, $query = '')
	{
		global $cache, $starttime, $phpbb_root_path;

		if (empty($_GET['explain']))
		{
			return;
		}

		if (!$query && $this->query_hold != '')
		{
			$query = $this->query_hold;
		}

		switch ($mode)
		{
			case 'display':
				if (!empty($cache))
				{
					$cache->unload();
				}
				$this->sql_close();

				$mtime = explode(' ', microtime());
				$totaltime = $mtime[0] + $mtime[1] - $starttime;

				echo '
					<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8869-1"><meta http-equiv="Content-Style-Type" content="text/css"><link rel="stylesheet" href="' . $phpbb_root_path . 'adm/style/sql_report.css" type="text/css">
					<style type="text/css">	th { background-image: url(\'' . $phpbb_root_path . 'adm/images/cellpic3.gif\') }	td.cat	{ background-image: url(\'' . $phpbb_root_path . 'adm/images/cellpic1.gif\') } </style>
					<title>Explain</title></head><body>
					<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
						<td><a href="' . htmlspecialchars(preg_replace('/&explain=([^&]*)/', '', $_SERVER['REQUEST_URI'])) . '"><img src="' . $phpbb_root_path . 'adm/images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0" /></a></td>
						<td width="100%" background="' . $phpbb_root_path . 'adm/images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle">SQL Report</span> &nbsp; &nbsp; &nbsp;</td>
					</tr></table>
					<br clear="all"/>
					<table width="95%" cellspacing="1" cellpadding="4" border="0" align="center"><tr>
						<td height="40" align="center" valign="middle"><b>Page generated in ' . round($totaltime, 4) . " seconds with {$this->num_queries} queries" . (($this->cache_num_queries) ? " + {$this->cache_num_queries} " . (($this->cache_num_queries == 1) ? 'query' : 'queries') . ' returning data from cache' : '') . '</b></td>
					</tr><tr>
						<td align="center" nowrap="nowrap">Time spent on MySQL queries: <b>' . round($this->sql_time, 5) . 's</b> | Time spent on PHP: <b>' . round($totaltime - $this->sql_time, 5) . 's</b></td>
					</tr></table>
					<table width="95%" cellspacing="1" cellpadding="4" border="0" align="center"><tr>
						<td>
				' . $this->sql_report . '</td>
					</tr></table>
					<br />
					</body></html>
				';
				exit;
				break;

			case 'stop':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$this->sql_report .= '
					<hr width="100%"/><br />

					<table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
					<tr>
						<th>Query #' . $this->num_queries . '</th>
					</tr>
					<tr>
						<td class="row1"><textarea style="font-family:\'Courier New\',monospace;width:100%" rows="5">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td>
					</tr>
					</table> ' . $this->html_hold . '
					<p align="center">
				';

				if ($this->query_result)
				{
					if (preg_match('/^(UPDATE|DELETE|REPLACE)/', $query))
					{
						$this->sql_report .= 'Affected rows: <b>' . $this->sql_affectedrows($this->query_result) . '</b> | ';
					}
					$this->sql_report .= 'Before: ' . sprintf('%.5f', $this->curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed: <b>' . sprintf('%.5f', $endtime - $this->curtime) . 's</b>';
				}
				else
				{
					$error = $this->sql_error();
					$this->sql_report .= '<b style="color: red">FAILED</b> - ' . SQL_LAYER . ' Error ' . $error['code'] . ': ' . htmlspecialchars($error['message']);
				}

				$this->sql_report .= '</p>';

				$this->sql_time += $endtime - $this->curtime;
			break;

			case 'start':
				$this->query_hold = $query;
				$this->html_hold = '';
			
				$this->_sql_report($mode, $query);

				$this->curtime = explode(' ', microtime());
				$this->curtime = $this->curtime[0] + $this->curtime[1];

			break;
			
			case 'add_select_row':

				$html_table = func_get_arg(2);
				$row = func_get_arg(3);
				
				if (!$html_table && sizeof($row))
				{
					$html_table = true;
					$this->html_hold .= '<table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center"><tr>';
								
					foreach (array_keys($row) as $val)
					{
						$this->html_hold .= '<th nowrap="nowrap">' . (($val) ? ucwords(str_replace('_', ' ', $val)) : '&nbsp;') . '</th>';
					}
					$this->html_hold .= '</tr>';
				}
				$this->html_hold .= '<tr>';

				$class = 'row1';
				foreach (array_values($row) as $val)
				{
					$class = ($class == 'row1') ? 'row2' : 'row1';
					$this->html_hold .= '<td class="' . $class . '">' . (($val) ? $val : '&nbsp;') . '</td>';
				}
				$this->html_hold .= '</tr>';
			
				return $html_table;

			break;

			case 'fromcache':

				$this->_sql_report($mode, $query);

				$this->cache_num_queries++;

			break;

			case 'record_fromcache':

				$endtime = func_get_arg(2);
				$splittime = func_get_arg(3);

				$time_cache = $endtime - $this->curtime;
				$time_db = $splittime - $endtime;
				$color = ($time_db > $time_cache) ? 'green' : 'red';

				$this->sql_report .= '<hr width="100%"/><br /><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0"><tr><th>Query results obtained from the cache</th></tr><tr><td class="row1"><textarea style="font-family:\'Courier New\',monospace;width:100%" rows="5">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td></tr></table><p align="center">';
				$this->sql_report .= 'Before: ' . sprintf('%.5f', $this->curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed [cache]: <b style="color: ' . $color . '">' . sprintf('%.5f', ($time_cache)) . 's</b> | Elapsed [db]: <b>' . sprintf('%.5f', $time_db) . 's</b></p>';

				// Pad the start time to not interfere with page timing
				$starttime += $time_db;

			break;

			default:
			
				$this->_sql_report($mode, $query);

			break;
		}
	}
}

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* This variable holds the class name to use later
*/
$sql_db = 'dbal_' . $dbms;

?>