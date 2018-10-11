<?php
/**
*
* @package DBal
* @version $Id: mysql4.php,v 1.1 2008/12/08 13:28:56 orynider Exp $
* @copyright (c) 2005 phpBB Group
* @copyright (c) 2002-2008 MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @link http://www.mx-publisher.com
*
*/

/**
*/
if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
}

/**
* @ignore
*/
//if (!defined('SQL_LAYER'))
if (!is_object('dbal_mysql4'))
{
	define('SQL_LAYER', 'mysql4');
	include_once($phpbb_root_path . 'includes/db/dbal.' . $phpEx);
	$sql_db = 'dbal_' . $dbms;

/**
* @package DBal
* MySQL4 Database Abstraction Layer
* Compatible with:
* MySQL 4.0+
* MySQL 4.1+
* MySQL 5.0+
*/
class dbal_mysql4 extends dbal
{
	var $mysql_version;
	var $multi_insert = true;
	/**
	* Connect to server
	*/
	/**
	* Connect to server
	* @access public
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->db_connect_id = ($this->persistency) ? @mysql_pconnect($this->server, $this->user, $sqlpassword, $new_link) : @mysql_connect($this->server, $this->user, $sqlpassword, $new_link);

		if ($this->db_connect_id && $this->dbname != '')
		{
			if (@mysql_select_db($this->dbname, $this->db_connect_id))
			{
				// Determine what version we are using and if it natively supports UNICODE
				$this->mysql_version = mysql_get_server_info($this->db_connect_id);

				if (version_compare($this->mysql_version, '4.1.3', '>='))
				{
					$this->sql_layer = 'mysql4';
					
					@mysql_query("SET NAMES 'utf8'", $this->db_connect_id);
					// enforce strict mode on databases that support it

					if (version_compare($this->mysql_version, '5.0.2', '>='))
					{
						$result = @mysql_query('SELECT @@session.sql_mode AS sql_mode', $this->db_connect_id);
						$row = @mysql_fetch_assoc($result);
						@mysql_free_result($result);
						$modes = array_map('trim', explode(',', $row['sql_mode']));

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
						@mysql_query("SET SESSION sql_mode='{$mode}'", $this->db_connect_id);
					}

				}
				else if (version_compare($this->mysql_version, '4.0.0', '>='))
				{
					$this->sql_layer = 'mysql4';
				}
				else //if (version_compare($this->mysql_version, '4.0.0', '<'))
				{
					$this->sql_layer = 'mysql';
				}				

				return $this->db_connect_id;
			}
		}

		return $this->sql_error('');
	}
	
	/**
	* Version information about used database
	*/
	function sql_server_info()
	{
		return 'MySQL ' . $this->mysql_version;
	}

	/**
	* SQL Transaction
	* @access private
	*/
	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				$result = @mysql_query('BEGIN', $this->db_connect_id);
				$this->transaction = true;
				break;

			case 'commit':
				$result = @mysql_query('COMMIT', $this->db_connect_id);
				$this->transaction = false;

				if (!$result)
				{
					@mysql_query('ROLLBACK', $this->db_connect_id);
				}
				break;

			case 'rollback':
				$result = @mysql_query('ROLLBACK', $this->db_connect_id);
				$this->transaction = false;
				break;

			default:
				$result = true;
		}

		return $result;
	}

	/**
	* Base query method
	*/
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

				if (($this->query_result = @mysql_query($query, $this->db_connect_id)) === false)
				{
					$this->sql_error($query);
				}

				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('stop', $query);
				}
				
				if (!$this->query_result)
				{
					return false;
				}				
				
				if ($cache_ttl && method_exists($cache, 'sql_save'))
				{
					$this->open_queries[(int) $this->query_result] = $this->query_result;
					$cache->sql_save($query, $this->query_result, $cache_ttl);
				}
				else if (strpos($query, 'SELECT') === 0 && $this->query_result)
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
				// Because MySQL 4.1+ no longer supports -1 in LIMIT queries we set it to the maximum value
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

		return ($query_id) ? @mysql_num_rows($query_id) : false;
	}
	
	/**
	* Return fields num
	* Not used within core code
	*/		
	function sql_numfields($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysql_num_fields($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Return fields name(s)
	* Not used within core code
	*/		
	function sql_fieldname($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = mysql_field_name($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fieldtype($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mysql_field_type($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @mysql_affected_rows($this->db_connect_id) : false;
	}

	/**
	* Fetch current row
	*/
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

		return ($query_id) ? @mysql_fetch_assoc($query_id) : false;
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
			if ($rownum === false)
			{
				$row = $this->sql_fetchrow($query_id);
				return isset($row[$field]) ? $row[$field] : false;
			}
			else
			{
				return @mysql_result($query_id, $rownum, $field);
			}
		}

		return false;
	}

	/**
	* Seek to given row number
	* rownum is zero-based
	*/
	function sql_rowseek($rownum, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @mysql_data_seek($query_id, $rownum) : false;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		return ($this->db_connect_id) ? @mysql_insert_id($this->db_connect_id) : false;
	}

	/**
	* Free sql result
	*/
	function sql_freeresult($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if (isset($this->open_queries[(int) $query_id]))
		{
			unset($this->open_queries[(int) $query_id]);
			return @mysql_free_result($query_id);
		}

		return false;
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		if (!$this->db_connect_id)
		{
			return @mysql_real_escape_string($msg);
		}

		return @mysql_real_escape_string($msg, $this->db_connect_id);
	}
	
	/**
	* return sql error array
	* @private
	*/
	function _sql_error()
	{
		if (!$this->db_connect_id)
		{
			return array(
				'message'	=> @mysql_error(),
				'code'		=> @mysql_errno()
			);
		}

		return array(
			'message'	=> @mysql_error($this->db_connect_id),
			'code'		=> @mysql_errno($this->db_connect_id)
		);
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
	* Close sql connection
	* @private
	*/
	function _sql_close()
	{
		return @mysql_close($this->db_connect_id);
	}

	/**
	* Build db-specific report
	* @private
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

					if ($result = @mysql_query("EXPLAIN $explain_query", $this->db_connect_id))
					{
						while ($row = @mysql_fetch_assoc($result))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
					}
					@mysql_free_result($result);

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @mysql_query($query, $this->db_connect_id);
				while ($void = @mysql_fetch_assoc($result))
				{
					// Take the time spent on parsing rows into account
				}
				@mysql_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
	/**
	* Cache clear function
	*/
	function clear_cache($cache_prefix = '', $cache_folder = SQL_CACHE_FOLDER, $files_per_step = 0)
	{
		global $phpEx;
		
		$cache_folder = (empty($cache_folder) ? SQL_CACHE_FOLDER : $cache_folder);

		$cache_prefix = 'sql_' . $cache_prefix;
		$cache_folder = (!empty($cache_folder) && @is_dir($cache_folder)) ? $cache_folder : SQL_CACHE_FOLDER;
		$cache_folder = ((@is_dir($cache_folder)) ? $cache_folder : @phpbb_realpath($cache_folder));

		$res = opendir($cache_folder);
		if($res)
		{
			$files_counter = 0;
			while(($file = readdir($res)) !== false)
			{
				if(!@is_dir($file) && (substr($file, 0, strlen($cache_prefix)) === $cache_prefix) && (substr($file, -(strlen($phpEx) + 1)) === '.' . $phpEx))
				{
					@unlink($cache_folder . $file);
					$files_counter++;
				}
				if (($files_per_step > 0) && ($files_counter >= $files_per_step))
				{
					closedir($res);
					return $files_per_step;
				}
			}
		}
		@closedir($res);
	}	
}

} // if ... define

// Connect to DB
$db	= new $sql_db();
if(!$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, false))
{
	message_die(CRITICAL_ERROR, "Could not connect to the database");
}
?>