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
* MSSQL Database Abstraction Layer
* Minimum Requirement is MSSQL 2000+
*/
class mssql extends \phpbb\db\driver\driver
{
	var $connect_error = '';

	/**
	* {@inheritDoc}
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		if (!function_exists('mssql_connect'))
		{
			$this->connect_error = 'mssql_connect function does not exist, is mssql extension installed?';
			return $this->sql_error('');
		}

		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->dbname = $database;

		$port_delimiter = (defined('PHP_OS') && substr(PHP_OS, 0, 3) === 'WIN') ? ',' : ':';
		$this->server = $sqlserver . (($port) ? $port_delimiter . $port : '');

		@ini_set('mssql.charset', 'UTF-8');
		@ini_set('mssql.textlimit', 2147483647);
		@ini_set('mssql.textsize', 2147483647);

		$this->db_connect_id = ($this->persistency) ? @mssql_pconnect($this->server, $this->user, $sqlpassword, $new_link) : @mssql_connect($this->server, $this->user, $sqlpassword, $new_link);

		if ($this->db_connect_id && $this->dbname != '')
		{
			if (!@mssql_select_db($this->dbname, $this->db_connect_id))
			{
				@mssql_close($this->db_connect_id);
				return false;
			}
		}

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* {@inheritDoc}
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		global $cache;

		if (!$use_cache || empty($cache) || ($this->sql_server_version = $cache->get('mssql_version')) === false)
		{
			$result_id = @mssql_query("SELECT SERVERPROPERTY('productversion'), SERVERPROPERTY('productlevel'), SERVERPROPERTY('edition')", $this->db_connect_id);

			$row = false;
			if ($result_id)
			{
				$row = @mssql_fetch_assoc($result_id);
				@mssql_free_result($result_id);
			}

			$this->sql_server_version = ($row) ? trim(implode(' ', $row)) : 0;

			if (!empty($cache) && $use_cache)
			{
				$cache->put('mssql_version', $this->sql_server_version);
			}
		}

		if ($raw)
		{
			return $this->sql_server_version;
		}

		return ($this->sql_server_version) ? 'MSSQL<br />' . $this->sql_server_version : 'MSSQL';
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_concatenate($expr1, $expr2)
	{
		return $expr1 . ' + ' . $expr2;
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
				return @mssql_query('BEGIN TRANSACTION', $this->db_connect_id);
			break;

			case 'commit':
				return @mssql_query('COMMIT TRANSACTION', $this->db_connect_id);
			break;

			case 'rollback':
				return @mssql_query('ROLLBACK TRANSACTION', $this->db_connect_id);
			break;
		}

		return true;
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

			$this->query_result = ($cache && $cache_ttl) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if ($this->query_result === false)
			{
				if (($this->query_result = @mssql_query($query, $this->db_connect_id)) === false)
				{
					$this->sql_error($query);
				}

				if (defined('DEBUG'))
				{
					$this->sql_report('stop', $query);
				}
				else if (defined('PHPBB_DISPLAY_LOAD_TIME'))
				{
					$this->sql_time += microtime(true) - $this->curtime;
				}

				if ($cache && $cache_ttl)
				{
					$this->open_queries[(int) $this->query_result] = $this->query_result;
					$this->query_result = $cache->sql_save($this, $query, $this->query_result, $cache_ttl);
				}
				else if (strpos($query, 'SELECT') === 0 && $this->query_result)
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

		// Since TOP is only returning a set number of rows we won't need it if total is set to 0 (return all rows)
		if ($total)
		{
			// We need to grab the total number of rows + the offset number of rows to get the correct result
			if (strpos($query, 'SELECT DISTINCT') === 0)
			{
				$query = 'SELECT DISTINCT TOP ' . ($total + $offset) . ' ' . substr($query, 15);
			}
			else
			{
				$query = 'SELECT TOP ' . ($total + $offset) . ' ' . substr($query, 6);
			}
		}

		$result = $this->sql_query($query, $cache_ttl);

		// Seek by $offset rows
		if ($offset)
		{
			$this->sql_rowseek($offset, $result);
		}

		return $result;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @mssql_rows_affected($this->db_connect_id) : false;
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

		if ($query_id === false)
		{
			return false;
		}

		$row = @mssql_fetch_assoc($query_id);

		// I hope i am able to remove this later... hopefully only a PHP or MSSQL bug
		if ($row)
		{
			foreach ($row as $key => $value)
			{
				$row[$key] = ($value === ' ' || $value === null) ? '' : $value;
			}
		}

		return $row;
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

		return ($query_id !== false) ? @mssql_data_seek($query_id, $rownum) : false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_nextid()
	{
		$result_id = @mssql_query('SELECT SCOPE_IDENTITY()', $this->db_connect_id);
		if ($result_id)
		{
			if ($row = @mssql_fetch_assoc($result_id))
			{
				@mssql_free_result($result_id);
				return $row['computed'];
			}
			@mssql_free_result($result_id);
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
			return @mssql_free_result($query_id);
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
	* {@inheritDoc}
	*/
	function sql_lower_text($column_name)
	{
		return "LOWER(SUBSTRING($column_name, 1, DATALENGTH($column_name)))";
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

	/**
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		if (function_exists('mssql_get_last_message'))
		{
			$error = array(
				'message'	=> @mssql_get_last_message(),
				'code'		=> '',
			);

			// Get error code number
			$result_id = @mssql_query('SELECT @@ERROR as code', $this->db_connect_id);
			if ($result_id)
			{
				$row = @mssql_fetch_assoc($result_id);
				$error['code'] = $row['code'];
				@mssql_free_result($result_id);
			}

			// Get full error message if possible
			$sql = 'SELECT CAST(description as varchar(255)) as message
				FROM master.dbo.sysmessages
				WHERE error = ' . $error['code'];
			$result_id = @mssql_query($sql);

			if ($result_id)
			{
				$row = @mssql_fetch_assoc($result_id);
				if (!empty($row['message']))
				{
					$error['message'] .= '<br />' . $row['message'];
				}
				@mssql_free_result($result_id);
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
	* Build db-specific query data
	* @access private
	*/
	function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	/**
	* Close sql connection
	* @access private
	*/
	function _sql_close()
	{
		return @mssql_close($this->db_connect_id);
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
				@mssql_query('SET SHOWPLAN_TEXT ON;', $this->db_connect_id);
				if ($result = @mssql_query($query, $this->db_connect_id))
				{
					@mssql_next_result($result);
					while ($row = @mssql_fetch_row($result))
					{
						$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
					}
				}
				@mssql_query('SET SHOWPLAN_TEXT OFF;', $this->db_connect_id);
				@mssql_free_result($result);

				if ($html_table)
				{
					$this->html_hold .= '</table>';
				}
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @mssql_query($query, $this->db_connect_id);
				while ($void = @mssql_fetch_assoc($result))
				{
					// Take the time spent on parsing rows into account
				}
				@mssql_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}
