<?php
/**
*
* @package DBal
* @version $Id: mysqli.php,v 1.1 2009/09/30 16:16:55 orynider Exp $
* @copyright (c) 2005 phpBB Group
* @copyright (c) 2002-2008 MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @link http://www.mx-publisher.com
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @ignore
*/
//if (!defined('SQL_LAYER'))
if (!is_object('dbal_mysqli'))
{
	define('SQL_LAYER', 'mysqli');
	include_once($phpbb_root_path . 'includes/db/dbal.' . $phpEx);
	$sql_db = 'dbal_' . $dbms; // Repopulated for multiple db connections

/**
* @package DBal
* MySQLi Database Abstraction Layer
* mysqli-extension has to be compiled with:
* MySQL 4.1+ or MySQL 5.0+
*/
class dbal_mysqli extends dbal
{
	var $multi_insert = true;

	/**
	* Connect to server
	* downgraded for phpBB2 backend
	* {@inheritDoc}
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		if (!function_exists('mysqli_connect'))
		{
			$this->connect_error = 'mysqli_connect function does not exist, is mysqli extension installed?';
			return $this->sql_error('');
		}

		$this->persistency = $persistency;
		$this->user = $sqluser;

		// If persistent connection, set dbhost to localhost when empty and prepend it with 'p:' prefix
		$this->server = ($this->persistency) ? 'p:' . (($sqlserver) ? $sqlserver : 'localhost') : $sqlserver;

		$this->dbname = $database;
		$port = (!$port) ? null : $port;

		// If port is set and it is not numeric, most likely mysqli socket is set.
		// Try to map it to the $socket parameter.
		$socket = null;
		if ($port)
		{
			if (is_numeric($port))
			{
				$port = (int) $port;
			}
			else
			{
				$socket = $port;
				$port = null;
			}
		}

		$this->db_connect_id = mysqli_init();

		if (!@mysqli_real_connect($this->db_connect_id, $this->server, $this->user, $sqlpassword, $this->dbname, $port, $socket, MYSQLI_CLIENT_FOUND_ROWS))
		{
			$this->db_connect_id = '';
		}

		if ($this->db_connect_id && $this->dbname != '')
		{		
			// mysqli only supported by phpBB3
			if (defined('UTF_STATUS') && isset($lang['ENCODING']))
			{
				@mysqli_query($this->db_connect_id, "SET NAMES". $lang['ENCODING']); // enforce strict mode on databases that support it
			}
			else
			{			
				@mysqli_query($this->db_connect_id, "SET NAMES 'utf8'");
			}
			// enforce strict mode on databases that support it
			if (version_compare($this->sql_server_info(true), '5.0.2', '>='))
			{
				$result = @mysqli_query($this->db_connect_id, 'SELECT @@session.sql_mode AS sql_mode');
				if ($result)
				{
					$row = mysqli_fetch_assoc($result);
					mysqli_free_result($result);

					$modes = array_map('trim', explode(',', $row['sql_mode']));
				}
				else
				{
					$modes = array();
				}

				// TRADITIONAL includes STRICT_ALL_TABLES and STRICT_TRANS_TABLES
				if (!in_array('TRADITIONAL', $modes))
				{
					if (!in_array('STRICT_ALL_TABLES', $modes))
					{
						$modes[] = 'STRICT_ALL_TABLES';
					}

					if (!in_array('STRICT_TRANS_TABLES', $modes))
					{
						$modes[] = 'STRICT_TRANS_TABLES';
					}
				}

				$mode = implode(',', $modes);
				@mysqli_query($this->db_connect_id, "SET SESSION sql_mode='{$mode}'");
			}
			return $this->db_connect_id;
		}

		return $this->sql_error('');
	}

	/**
	* {@inheritDoc}
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		global $cache;

		if (!$use_cache || empty($cache) || ($this->sql_server_version = $cache->get('mysqli_version')) === false)
		{
			$result = @mysqli_query($this->db_connect_id, 'SELECT VERSION() AS version');
			if ($result)
			{
				$row = mysqli_fetch_assoc($result);
				mysqli_free_result($result);

				$this->sql_server_version = $row['version'];

				if (!empty($cache) && $use_cache)
				{
					$cache->put('mysqli_version', $this->sql_server_version);
				}
			}
		}

		return ($raw) ? $this->sql_server_version : 'MySQL(i) ' . $this->sql_server_version;
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
				return @mysqli_autocommit($this->db_connect_id, false);
			break;

			case 'commit':
				$result = @mysqli_commit($this->db_connect_id);
				@mysqli_autocommit($this->db_connect_id, true);
				return $result;
			break;

			case 'rollback':
				$result = @mysqli_rollback($this->db_connect_id);
				@mysqli_autocommit($this->db_connect_id, true);
				return $result;
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
				if (($this->query_result = @mysqli_query($this->db_connect_id, $query)) === false)
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

				if ($this->query_result === false)
				{
					return false;
				}

				if ($cache && $cache_ttl)
				{
					$this->query_result = $cache->sql_save($this, $query, $this->query_result, $cache_ttl);
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

		return ($this->query_result) ? $this->query_result : false;
	}

	/**
	* Build LIMIT query
	*/
	function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		if ($query != '')
		{
			$this->query_result = false;

			// if $total is set to 0 we do not want to limit the number of rows
			if ($total == 0)
			{
				// MySQL 4.1+ no longer supports -1 in limit queries
				$total = '18446744073709551615';
			}

			$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);

			return $this->sql_query($query, $cache_ttl);
		}
		else
		{
			return false;
		}
	}

	/**
	* Return number of rows
	* Not used within core code
	*/
	function sql_numrows($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @mysqli_num_rows($query_id) : false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @mysqli_affected_rows($this->db_connect_id) : false;
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

		if ($cache && !is_object($query_id) && $cache->sql_exists($query_id))
		{
			return $cache->sql_fetchrow($query_id);
		}

		if ($query_id)
		{
			$result = mysqli_fetch_assoc($query_id);
			return $result !== null ? $result : false;
		}

		return false;
	}

	/**
	* Fetch field
	* if rownum is false, the current row is used, else it is pointing to the row (zero-based)
	*/
	function sql_fetchfield($field, $rownum = false, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($rownum !== false)
			{
				$this->sql_rowseek($rownum, $query_id);
			}

			$row = $this->sql_fetchrow($query_id);
			return isset($row[$field]) ? $row[$field] : false;
		}

		return false;
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

		if ($cache && !is_object($query_id) && $cache->sql_exists($query_id))
		{
			return $cache->sql_rowseek($rownum, $query_id);
		}

		return ($query_id) ? @mysqli_data_seek($query_id, $rownum) : false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_nextid()
	{
		return ($this->db_connect_id) ? @mysqli_insert_id($this->db_connect_id) : false;
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

		if (!$query_id)
		{
			return false;
		}

		if ($query_id === true)
		{
			return true;
		}

		return mysqli_free_result($query_id);
	}

	/**
	* {@inheritDoc}
	*/
	function sql_escape($msg)
	{
		return @mysqli_real_escape_string($this->db_connect_id, $msg);
	}

	/**
	* Gets the estimated number of rows in a specified table.
	*
	* @param string $table_name		Table name
	*
	* @return string				Number of rows in $table_name.
	*								Prefixed with ~ if estimated (otherwise exact).
	*
	* @access public
	*/
	function get_estimated_row_count($table_name)
	{
		$table_status = $this->get_table_status($table_name);

		if (isset($table_status['Engine']))
		{
			if ($table_status['Engine'] === 'MyISAM')
			{
				return $table_status['Rows'];
			}
			else if ($table_status['Engine'] === 'InnoDB' && $table_status['Rows'] > 100000)
			{
				return '~' . $table_status['Rows'];
			}
		}

		return parent::get_row_count($table_name);
	}

	/**
	* Gets the exact number of rows in a specified table.
	*
	* @param string $table_name		Table name
	*
	* @return string				Exact number of rows in $table_name.
	*
	* @access public
	*/
	function get_row_count($table_name)
	{
		$table_status = $this->get_table_status($table_name);

		if (isset($table_status['Engine']) && $table_status['Engine'] === 'MyISAM')
		{
			return $table_status['Rows'];
		}

		return parent::get_row_count($table_name);
	}

	/**
	* Gets some information about the specified table.
	*
	* @param string $table_name		Table name
	*
	* @return array
	*
	* @access protected
	*/
	function get_table_status($table_name)
	{
		$sql = "SHOW TABLE STATUS
			LIKE '" . $this->sql_escape($table_name) . "'";
		$result = $this->sql_query($sql);
		$table_status = $this->sql_fetchrow($result);
		$this->sql_freeresult($result);

		return $table_status;
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
	* Build db-specific query data
	* @access private
	*/
	function _sql_custom_build($stage, $data)
	{
		switch ($stage)
		{
			case 'FROM':
				$data = '(' . $data . ')';
			break;
		}

		return $data;
	}	
	
	/**
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		if ($this->db_connect_id)
		{
			$error = array(
				'message'	=> @mysqli_error($this->db_connect_id),
				'code'		=> @mysqli_errno($this->db_connect_id)
			);
		}
		else if (function_exists('mysqli_connect_error'))
		{
			$error = array(
				'message'	=> @mysqli_connect_error(),
				'code'		=> @mysqli_connect_errno(),
			);
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
	* Close sql connection
	* @access private
	*/
	function _sql_close()
	{
		return @mysqli_close($this->db_connect_id);
	}

	/**
	* Build db-specific report
	* @access private
	*/
	function _sql_report($mode, $query = '')
	{
		static $test_prof;

		// current detection method, might just switch to see the existence of INFORMATION_SCHEMA.PROFILING
		if ($test_prof === null)
		{
			$test_prof = false;
			if (strpos(mysqli_get_server_info($this->db_connect_id), 'community') !== false)
			{
				$ver = mysqli_get_server_version($this->db_connect_id);
				if ($ver >= 50037 && $ver < 50100)
				{
					$test_prof = true;
				}
			}
		}

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

					// begin profiling
					if ($test_prof)
					{
						@mysqli_query($this->db_connect_id, 'SET profiling = 1;');
					}

					if ($result = @mysqli_query($this->db_connect_id, "EXPLAIN $explain_query"))
					{
						while ($row = mysqli_fetch_assoc($result))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
						mysqli_free_result($result);
					}

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}

					if ($test_prof)
					{
						$html_table = false;

						// get the last profile
						if ($result = @mysqli_query($this->db_connect_id, 'SHOW PROFILE ALL;'))
						{
							$this->html_hold .= '<br />';
							while ($row = mysqli_fetch_assoc($result))
							{
								// make <unknown> HTML safe
								if (!empty($row['Source_function']))
								{
									$row['Source_function'] = str_replace(array('<', '>'), array('&lt;', '&gt;'), $row['Source_function']);
								}

								// remove unsupported features
								foreach ($row as $key => $val)
								{
									if ($val === null)
									{
										unset($row[$key]);
									}
								}
								$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
							}
							mysqli_free_result($result);
						}

						if ($html_table)
						{
							$this->html_hold .= '</table>';
						}

						@mysqli_query($this->db_connect_id, 'SET profiling = 0;');
					}
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @mysqli_query($this->db_connect_id, $query);
				if ($result)
				{
					while ($void = mysqli_fetch_assoc($result))
					{
						// Take the time spent on parsing rows into account
					}
					mysqli_free_result($result);
				}

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

} // if ... define

// Connect to DB
$db	= new $sql_db();
if(!$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, false))
{
	mx_message_die(CRITICAL_ERROR, "Could not connect to the database");
}
?>