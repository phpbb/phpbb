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
* PostgreSQL Database Abstraction Layer
* Minimum Requirement is Version 8.3+
*/
class postgres extends \phpbb\db\driver\driver
{
	var $multi_insert = true;
	var $last_query_text = '';
	var $connect_error = '';

	/**
	* {@inheritDoc}
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$connect_string = '';

		if ($sqluser)
		{
			$connect_string .= "user=$sqluser ";
		}

		if ($sqlpassword)
		{
			$connect_string .= "password='$sqlpassword' ";
		}

		if ($sqlserver)
		{
			// $sqlserver can carry a port separated by : for compatibility reasons
			// If $sqlserver has more than one : it's probably an IPv6 address.
			// In this case we only allow passing a port via the $port variable.
			if (substr_count($sqlserver, ':') === 1)
			{
				list($sqlserver, $port) = explode(':', $sqlserver);
			}

			if ($sqlserver !== 'localhost')
			{
				$connect_string .= "host=$sqlserver ";
			}

			if ($port)
			{
				$connect_string .= "port=$port ";
			}
		}

		$schema = '';

		if ($database)
		{
			$this->dbname = $database;
			if (strpos($database, '.') !== false)
			{
				list($database, $schema) = explode('.', $database);
			}
			$connect_string .= "dbname=$database";
		}

		$this->persistency = $persistency;

		if ($this->persistency)
		{
			if (!function_exists('pg_pconnect'))
			{
				$this->connect_error = 'pg_pconnect function does not exist, is pgsql extension installed?';
				return $this->sql_error('');
			}
			$collector = new \phpbb\error_collector;
			$collector->install();
			$this->db_connect_id = (!$new_link) ? @pg_pconnect($connect_string) : @pg_pconnect($connect_string, PGSQL_CONNECT_FORCE_NEW);
		}
		else
		{
			if (!function_exists('pg_connect'))
			{
				$this->connect_error = 'pg_connect function does not exist, is pgsql extension installed?';
				return $this->sql_error('');
			}
			$collector = new \phpbb\error_collector;
			$collector->install();
			$this->db_connect_id = (!$new_link) ? @pg_connect($connect_string) : @pg_connect($connect_string, PGSQL_CONNECT_FORCE_NEW);
		}

		$collector->uninstall();

		if ($this->db_connect_id)
		{
			if ($schema !== '')
			{
				@pg_query($this->db_connect_id, 'SET search_path TO ' . $schema);
			}
			return $this->db_connect_id;
		}

		$this->connect_error = $collector->format_errors();
		return $this->sql_error('');
	}

	/**
	* {@inheritDoc}
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		global $cache;

		if (!$use_cache || empty($cache) || ($this->sql_server_version = $cache->get('pgsql_version')) === false)
		{
			$query_id = @pg_query($this->db_connect_id, 'SELECT VERSION() AS version');
			if ($query_id)
			{
				$row = pg_fetch_assoc($query_id, null);
				pg_free_result($query_id);

				$this->sql_server_version = (!empty($row['version'])) ? trim(substr($row['version'], 10)) : 0;

				if (!empty($cache) && $use_cache)
				{
					$cache->put('pgsql_version', $this->sql_server_version);
				}
			}
		}

		return ($raw) ? $this->sql_server_version : 'PostgreSQL ' . $this->sql_server_version;
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
				return @pg_query($this->db_connect_id, 'BEGIN');
			break;

			case 'commit':
				return @pg_query($this->db_connect_id, 'COMMIT');
			break;

			case 'rollback':
				return @pg_query($this->db_connect_id, 'ROLLBACK');
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

			$this->last_query_text = $query;
			$this->query_result = ($cache && $cache_ttl) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if ($this->query_result === false)
			{
				if (($this->query_result = @pg_query($this->db_connect_id, $query)) === false)
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
	* Build db-specific query data
	* @access private
	*/
	function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	/**
	* Build LIMIT query
	*/
	function _sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$this->query_result = false;

		// if $total is set to 0 we do not want to limit the number of rows
		if ($total == 0)
		{
			$total = 'ALL';
		}

		$query .= "\n LIMIT $total OFFSET $offset";

		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* {@inheritDoc}
	*/
	function sql_affectedrows()
	{
		return ($this->query_result) ? @pg_affected_rows($this->query_result) : false;
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

		return ($query_id) ? pg_fetch_assoc($query_id, null) : false;
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

		return ($query_id) ? @pg_result_seek($query_id, $rownum) : false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_nextid()
	{
		$query_id = $this->query_result;

		if ($query_id !== false && $this->last_query_text != '')
		{
			if (preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $this->last_query_text, $tablename))
			{
				$query = "SELECT currval('" . $tablename[1] . "_seq') AS last_value";
				$temp_q_id = @pg_query($this->db_connect_id, $query);

				if (!$temp_q_id)
				{
					return false;
				}

				$temp_result = pg_fetch_assoc($temp_q_id, null);
				pg_free_result($query_id);

				return ($temp_result) ? $temp_result['last_value'] : false;
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
			return pg_free_result($query_id);
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_escape($msg)
	{
		return @pg_escape_string($msg);
	}

	/**
	* Build LIKE expression
	* @access private
	*/
	function _sql_like_expression($expression)
	{
		return $expression;
	}

	/**
	* Build NOT LIKE expression
	* @access private
	*/
	function _sql_not_like_expression($expression)
	{
		return $expression;
	}

	/**
	* {@inheritDoc}
	*/
	function cast_expr_to_bigint($expression)
	{
		return 'CAST(' . $expression . ' as DECIMAL(255, 0))';
	}

	/**
	* {@inheritDoc}
	*/
	function cast_expr_to_string($expression)
	{
		return 'CAST(' . $expression . ' as VARCHAR(255))';
	}

	/**
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		// pg_last_error only works when there is an established connection.
		// Connection errors have to be tracked by us manually.
		if ($this->db_connect_id)
		{
			$message = @pg_last_error($this->db_connect_id);
		}
		else
		{
			$message = $this->connect_error;
		}

		return array(
			'message'	=> $message,
			'code'		=> ''
		);
	}

	/**
	* Close sql connection
	* @access private
	*/
	function _sql_close()
	{
		return @pg_close($this->db_connect_id);
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

				$explain_query = $query;
				if (preg_match('/UPDATE ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}
				else if (preg_match('/DELETE FROM ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}

				if (preg_match('/^SELECT/', $explain_query))
				{
					$html_table = false;

					if ($result = @pg_query($this->db_connect_id, "EXPLAIN $explain_query"))
					{
						while ($row = pg_fetch_assoc($result, null))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
						pg_free_result($result);
					}

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @pg_query($this->db_connect_id, $query);
				if ($result)
				{
					while ($void = pg_fetch_assoc($result, null))
					{
						// Take the time spent on parsing rows into account
					}
					pg_free_result($result);
				}

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}
