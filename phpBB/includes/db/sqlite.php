<?php
/***************************************************************************
 *                                 mysql.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if (!defined('SQL_LAYER'))
{

define('SQL_LAYER', 'sqlite');

class sql_db
{
	var $db_connect_id;
	var $query_result;
	var $return_on_error = false;
	var $transaction = false;
	var $sql_report = '';
	var $sql_time = 0;
	var $num_queries = 0;
	var $open_queries = array();

	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->password = $sqlpassword;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->db_connect_id = ($this->persistency) ? sqlite_popen($this->server, 0, $error) : sqlite_open($this->server, 0, $error);

		return ($this->db_connect_id) ? true : $error;
	}

	// Other base methods
	function sql_close()
	{
		if (!$this->db_connect_id)
		{
			return false;
		}

		return sqlite_close($this->db_connect_id);
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
				$this->transaction = true;
				$result = sqlite_query('BEGIN', $this->db_connect_id);
				break;

			case 'commit':
				$this->transaction = false;
				$result = sqlite_query('COMMIT', $this->db_connect_id);
				break;

			case 'rollback':
				$this->transaction = false;
				$result = sqlite_query('ROLLBACK', $this->db_connect_id);
				break;

			default:
				$result = true;
		}

		return $result;
	}

	// Base query method
	function sql_query($query = '', $expire_time = 0)
	{
		if ($query != '')
		{
			global $cache;

			$query = preg_replace('#FROM \((.*?)\)[\n\t ]+WHERE #s', 'FROM \1 WHERE ', $query);

			if (!$expire_time || !$cache->sql_load($query, $expire_time))
			{
				if ($expire_time)
				{
					$cache_result = true;
				}

				$this->query_result = false;
				$this->num_queries++;

				if (!empty($_GET['explain']))
				{
					global $starttime;

					$curtime = explode(' ', microtime());
					$curtime = $curtime[0] + $curtime[1] - $starttime;
				}

				if (!($this->query_result = sqlite_query($query, $this->db_connect_id)))
				{
					$this->sql_error($query);
				}

				if (!empty($_GET['explain']))
				{
					$endtime = explode(' ', microtime());
					$endtime = $endtime[0] + $endtime[1] - $starttime;

					$this->sql_report .= "<pre>Query:\t" . htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n\t", $query)) . "\n\n";

					if ($this->query_result)
					{
						$this->sql_report .= "Time before:  $curtime\nTime after:   $endtime\nElapsed time: <b>" . ($endtime - $curtime) . "</b>\n</pre>";
					}
					else
					{
						$error = $this->sql_error();
						$this->sql_report .= '<b>FAILED</b> - SQLite ' . $error['code'] . ': ' . htmlspecialchars($error['message']) . '<br><br><pre>';
					}

					$this->sql_time += $endtime - $curtime;

					if (preg_match('#^SELECT#', $query))
					{
						$html_table = FALSE;
						if ($result = sqlite_query("EXPLAIN $query", $this->db_connect_id))
						{
							while ($row = sqlite_fetch_array($result, SQLITE_ASSOC))
							{
								if (!$html_table && count($row))
								{
									$html_table = TRUE;
									$this->sql_report .= "<table width=100% border=1 cellpadding=2 cellspacing=1>\n";
									$this->sql_report .= "<tr>\n<td><b>" . implode("</b></td>\n<td><b>", array_keys($row)) . "</b></td>\n</tr>\n";
								}
								$this->sql_report .= "<tr>\n<td>" . implode("&nbsp;</td>\n<td>", array_values($row)) . "&nbsp;</td>\n</tr>\n";
							}
						}

						if ($html_table)
						{
							$this->sql_report .= '</table><br>';
						}
					}

					$this->sql_report .= "<hr>\n";
				}

				if (preg_match('#^SELECT#', $query))
				{
					$this->open_queries[] = $this->query_result;
				}
			}

			if (!empty($cache_result))
			{
				$cache->sql_save($query, $this->query_result);
			}
		}
		else
		{
			return false;
		}

		return ($this->query_result) ? $this->query_result : false;
	}

	function sql_query_limit($query, $total, $offset = 0, $expire_time = 0)
	{
		if ($query != '')
		{
			$this->query_result = false;

			$query .= ' LIMIT ' . ((!empty($offset)) ? $total . ' OFFSET ' . $offset : $total);

			return $this->sql_query($query, $expire_time);
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
		else if ($query == 'UPDATE')
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
			$query = implode(', ', $values);
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

		return ($query_id) ? sqlite_num_rows($query_id) : false;
	}

	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? sqlite_changes($this->db_connect_id) : false;
	}

	function sql_fetchrow($query_id = 0)
	{
		global $cache;

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($cache->sql_exists($query_id))
		{
			return $cache->sql_fetchrow($query_id);
		}

		return ($query_id) ? sqlite_fetch_array($query_id, SQLITE_ASSOC) : false;
	}

	function sql_fetchrowset($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
			while($this->rowset[$query_id] = sqlite_fetch_array($query_id, SQLITE_ASSOC))
			{
				$result[] = $this->rowset[$query_id];
			}
			return $result;
		}
		else
		{
			return false;
		}
	}

	function sql_fetchfield($field, $rownum = -1, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}

		if($query_id)
		{
			return ($rownum > -1) ? ((sqlite_seek($query_id, $rownum)) ? sqlite_column($query_id, $field) : false) : sqlite_column($query_id, $field);
		}
	}

	function sql_rowseek($rownum, $query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? sqlite_seek($query_id, $rownum) : false;
	}

	function sql_nextid()
	{
		return ($this->db_connect_id) ? sqlite_last_insert_rowid($this->db_connect_id) : false;
	}

	function sql_freeresult($query_id = false)
	{
		return true;
	}

	function sql_escape($msg)
	{
		return sqlite_escape_string(stripslashes($msg));
	}

	function sql_error($sql = '')
	{
		if (!$this->return_on_error)
		{
			if ($this->transaction)
			{
				$this->sql_transaction('rollback');
			}

			$this_page = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
			$this_page .= '&' . ((!empty($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING']);

			$message = '<u>SQL ERROR</u> [ ' . SQL_LAYER . ' ]<br /><br />' . sqlite_error_string(sqlite_last_error($this->db_connect_id)) . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . htmlspecialchars($this_page) . (($sql != '') ? '<br /><br /><u>SQL</u><br /><br />' . $sql : '') . '<br />';
			trigger_error($message, E_USER_ERROR);
		}

		$result = array(
			'message'	=> sqlite_error_string(sqlite_last_error($this->db_connect_id)),
			'code'		=> sqlite_last_error($this->db_connect_id)
		);

		return $result;
	}

} // class sql_db

} // if ... define

?>