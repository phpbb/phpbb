<?php
/***************************************************************************
 *                                 mysql.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            :(C) 2001 The phpBB Group
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
 *  (at your option) any later version.
 *
 ***************************************************************************/

if (!defined('SQL_LAYER'))
{

define('SQL_LAYER', 'firebird');

class sql_db
{
	var $db_connect_id;
	var $query_result;
	var $return_on_error = false;
	var $transaction = false;
	var $sql_report = '';
	var $sql_time = 0;
	var $escape_max = array('match' => array('\\', '\'', '"'), 'replace' => array('\\\\', '\'\'', '\"'));
	var $escape_min = array('match' => array('\\', '"'), 'replace' => array('\\\\', '\"'));

	// Constructor
	function sql_db($sqlserver, $sqluser, $sqlpassword, $database = '', $port = '', $persistency = false)
	{
		$this->open_queries = array();
		$this->num_queries = 0;

		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->password = $sqlpassword;
		$this->server = $sqlserver;

		$this->db_connect_id =($this->persistency) ? @ibase_pconnect($this->server, $this->user, $this->password) : @ibase_connect($this->server, $this->user, $this->password);

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	// Other base methods
	function sql_close()
	{
		if (!$this->db_connect_id)
		{
			return false;
		}

		if (count($this->open_queries))
		{
			foreach($this->open_queries as $query_id)
			{
				@ibase_free_query($query_id);
			}
		}

		return @ibase_close($this->db_connect_id);
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
		switch($status)
		{
			case 'begin':
				$this->transaction = true;
				break;

			case 'commit':
				$result = ibase_commit();
				$this->transaction = false;
				break;

			case 'rollback':
				$result = ibase_rollback();
				$this->transaction = false;
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

				if (!($this->query_result = @ibase_query($query, $this->db_connect_id)))
				{
					$this->sql_error($query);
				}

				if (!$this->transaction)
				{
					@ibase_commit();
				}

				if (!empty($_GET['explain']))
				{
					$endtime = explode(' ', microtime());
					$endtime = $endtime[0] + $endtime[1] - $starttime;

					$this->sql_report .= "<pre>Query:\t" . htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n\t", $query)) . "\n\n";

					if ($this->query_result)
					{
						$this->sql_report .= "Time before:  $curtime\nTime after:   $endtime\nElapsed time: <b>" .($endtime - $curtime) . "</b>\n</pre>";
					}
					else
					{
						$error = $this->sql_error();
						$this->sql_report .= '<b>FAILED</b> - MySQL Error ' . $error['code'] . ': ' . htmlspecialchars($error['message']) . '<br><br><pre>';
					}

					$this->sql_time += $endtime - $curtime;
/*
					if (preg_match('/^SELECT/', $query))
					{
						$html_table = FALSE;
						if ($result = mysql_query("EXPLAIN $query", $this->db_connect_id))
						{
							while($row = mysql_fetch_assoc($result))
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
*/
					$this->sql_report .= "<hr>\n";
				}

				$this->open_queries[] = $this->query_result;
			}

			if (!empty($cache_result))
			{
				$cache->sql_save($query, $this->query_result);
				@ibase_free_result(array_pop($this->open_queries));
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
			$this->num_queries++;

			$query .= ' ROWS ' . $total .((!empty($offset)) ? ' TO ' . $offset : '');

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
			foreach($assoc_ary as $key => $var)
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
					$values[] =(is_bool($var)) ? intval($var) : $var;
				}
			}

			$query = '(' . implode(', ', $fields) . ') VALUES(' . implode(', ', $values) . ')';
		}
		else if ($query == 'UPDATE')
		{
			$values = array();
			foreach($assoc_ary as $key => $var)
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
					$values[] =(is_bool($var)) ? "$key = " . intval($var) : "$key = $var";
				}
			}
			$query = implode(', ', $values);
		}

		return $query;
	}

	// Other query methods
	function sql_numrows($query_id = false)
	{
		return 0;
	}

	function sql_affectedrows()
	{
		return 0;
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

		return ($query_id) ? get_object_vars(@ibase_fetch_object($query_id)) : false;
	}

	function sql_fetchrowset($query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}
		if ($query_id)
		{
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
			while($this->rowset[$query_id] = get_object_vars(@ibase_fetch_object($query_id))
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
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($rownum > -1)
			{
				$result = @mysql_result($query_id, $rownum, $field);
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
					else if ($this->row[$query_id])
					{
						$result = $this->row[$query_id][$field];
					}
				}
			}
			return $result;
		}
		else
		{
			return false;
		}
	}

	function sql_rowseek($rownum, $query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		for($i = 1; $i < $rownum; $i++)
		{
			if (!$this->sql_fetchrow($query_id))
			{
				return false;
			}
		}

		return true;
	}

	function sql_nextid()
	{
		if ($this->query_result)
		{
			$query = "SELECT Gen_ID('" . $tablename[1] . "_id_seq',1) AS last_value 
				FROM RDB\$DATABASE";
			if (!($temp_q_id =  @ibase_query($this->db_connect_id, $query)))
			{
				return false;
			}

			$temp_result = @ibase_fetch_object($temp_q_id);
			$this->sql_freeresult($temp_q_id);

			return ($temp_result) ? $temp_result->last_value : false;
		}
	}

	function sql_freeresult($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @ibase_free_result($query_id) : false;
	}

	function sql_escape($msg)
	{
		return (@ini_get('magic_quotes_sybase') || strtoupper(@ini_get('magic_quotes_sybase')) = 'ON') ? str_replace($replace_min('match'), $replace_min('replace'), $msg) : str_replace($replace_max('match'), $replace_max('replace'), $msg);
	}

	function sql_error($sql = '')
	{
		if (!$this->return_on_error)
		{
			if ($this->transaction)
			{
				$this->sql_transaction('rollback');
			}

			$this_page =(!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
			$this_page .= '&' .((!empty($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING']);

			$message = '<u>SQL ERROR</u> [ ' . SQL_LAYER . ' ]<br /><br />' . @ibase_errmsg() . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . $this_page .(($sql != '') ? '<br /><br /><u>SQL</u><br /><br />' . $sql : '') . '<br />';
			trigger_error($message, E_USER_ERROR);
		}

		$result['message'] = @ibase_errmsg();
		$result['code'] = '';

		return $result;
	}

} // class sql_db

} // if ... define

?>