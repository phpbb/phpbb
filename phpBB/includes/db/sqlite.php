<?php
/**
*
* @package DBal
* @version $Id: sqlite.php,v 1.1 2008/12/08 13:28:56 orynider Exp $
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
if (!is_object('dbal_sqlite'))
{
	define('SQL_LAYER', 'sqlite');
	include_once($phpbb_root_path . 'includes/db/dbal.' . $phpEx);
	$sql_db = 'dbal_' . $dbms; // Repopulated for multiple db connections

/**
* @package DBal
* Sqlite Database Abstraction Layer
*/
class dbal_sqlite extends dbal
{
	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$error = '';
		$this->db_connect_id = ($this->persistency) ? @sqlite_popen($this->server, 0666, $error) : @sqlite_open($this->server, 0666, $error);;

		if ($this->db_connect_id)
		{
			@sqlite_query('PRAGMA short_column_names = 1', $this->db_connect_id);
		}

		return ($this->db_connect_id) ? true : array('message' => $error);
	}
	
	
	function sql_connect_id() 
	{
		return $this->db_connect_id;
	}
	
	/**
	* Version information about used database
	*/
	function sql_server_info()
	{
		return 'SQLite ' . @sqlite_libversion();
	}

	/**
	* sql transaction
	*/
	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				$result = @sqlite_query('BEGIN', $this->db_connect_id);
				$this->transaction = true;
				break;

			case 'commit':
				$result = @sqlite_query('COMMIT', $this->db_connect_id);
				$this->transaction = false;

				if (!$result)
				{
					@sqlite_query('ROLLBACK', $this->db_connect_id);
				}
				break;

			case 'rollback':
				$result = @sqlite_query('ROLLBACK', $this->db_connect_id);
				$this->transaction = false;
				break;

			default:
				$result = true;
		}

		return $result;
	}

	/**
	* Close sql connection
	* @private
	*/
	function sql_close()
	{
		if($this->db_connect_id)
		{
			//
			// Commit any remaining transactions
			//
			if( $this->in_transaction )
			{
				@sqlite_query("COMMIT", $this->db_connect_id);
			}

			return @sqlite_close($this->db_connect_id);
		}
		else
		{
			return false;
		}
	}

	/**
	* Base query method
	*/
	function sql_query($query = '', $cache_ttl = 0)
	{
		if (!empty($query))
		{
			global $cache;

			$query = preg_replace('#FROM \(([^)]*)\)(,|[\n\r\t ]+(?:WHERE|LEFT JOIN)) #', 'FROM \1\2 ', $query);

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('start', $query);
			}

			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;

			if (!$this->query_result)
			{
				$this->num_queries++;

				if (($this->query_result = @sqlite_query($query, $this->db_connect_id)) === false)
				{
					$this->sql_error($query);
				}

				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('stop', $query);
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

		return ($query_id) ? @sqlite_num_rows($query_id) : false;
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
			$result = @sqlite_num_fields($query_id);
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
			$result = sqlite_field_name($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Check if a field type in a database.
	 *
	 * @param string $offset of sql array.
	 * @param string $query_id from sql array.
	 * @return field type.
	 */
	function sql_fieldtype($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @sqlite_field_type($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Gets a list of columns of a table.
	 *
	 * @param string $table_name table name
	 * @return array of columns names (all lower case)
	 */
	function sql_list_columns($table_name)
	{
		$columns = array();
		$sql = "SELECT sql
			FROM sqlite_master
			WHERE type = 'table'
				AND name = '{$table_name}'";
		$result = $this->sql_query($sql);
		
		if (!$result)
		{
			return false;
		}
		
		$row = $this->sql_fetchrow($result);
		$this->sql_freeresult($result);
		
		preg_match('#\((.*)\)#s', $row['sql'], $matches);
		
		$cols = trim($matches[1]);
		$col_array = preg_split('/,(?![\s\w]+\))/m', $cols);

		foreach ($col_array as $declaration)
		{
			$entities = preg_split('#\s+#', trim($declaration));
			if ($entities[0] == 'PRIMARY')
			{
				continue;
			}
			
			$column = strtolower($entities[0]);
			$columns[$column] = $column;
		}
		
		return $columns;
	}
	
	/**
	 * Check if a field exists in a table.
	 *
	 * @param string $column for field name.
	 * @param string $table_name for table name.
	 * @return boolean true or false if not exists.
	 */
	function sql_field_exists($column, $table_name)
	{
		$query = $this->sql_query("PRAGMA table_info('{$table_name}')");
		while($row = @sqlite_fetch_array($query))
		{
			if($row['name'] == $column)
			{
				++$count;
			}
		}
		$query->closeCursor();
		
		if($count > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Check if a table exists in a database.
	 *
	 * @param string $table_name for the table name.
	 * @return boolean true or false if not exists.
	 */
	function sql_table_exists($table_name)
	{
		//$count = $this->num_rows($this->sql_query("SELECT * FROM sqlite_master WHERE type='table' AND name='{$table_name}'"));
		$query = $this->sql_query("SELECT COUNT(name) as count FROM sqlite_master WHERE type='table' AND name='{$table_name}'");
		$count = $this->sql_fetch_field($query, "count");
		$query->closeCursor();
		
		if($exists > 0)
		{
			return true;
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
		return ($this->db_connect_id) ? @sqlite_changes($this->db_connect_id) : false;
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

		return ($query_id) ? @sqlite_fetch_array($query_id, SQLITE_ASSOC) : false;
	}
	
	/**
	 * Return a result array for a query
	 *
	 * @param PDO statement $query for the query.
	 * @param int $resulttype One of PDO's constants: 
	 * FETCH_ASSOC, FETCH_BOUND, FETCH_CLASS, 
	 * FETCH_INTO, FETCH_LAZY, FETCH_NAMED, 
	 * FETCH_NUM, FETCH_OBJ or FETCH_BOTH
	 *
	 * @return array for the query of result.
	 */
	function sql_fetch_array($query, $resulttype=PDO::FETCH_BOTH)
	{
		switch($resulttype)
		{
			case FETCH_ASSOC: 
	 		case FETCH_BOUND: 
	 		case FETCH_CLASS: 
	 		case FETCH_INTO: 
	 		case FETCH_LAZY: 
	 		case FETCH_NAMED: 
	 		case FETCH_NUM: 
	 		case FETCH_OBJ:
	 		case FETCH_BOTH:
			break;
			
			default:
				$resulttype = FETCH_ASSOC;
			break;
		}
		return  @sqlite_fetch_array($query, $resulttype);
	}
	
	/**
	* Fetch field
	* if rownum is false, the current row is used, else it is pointing to the row (zero-based)
	*/
	function sql_fetchfield($column, $rownum = false, $query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($rownum === false)
			{
				return @sqlite_column($query_id, $column);
			}
			else
			{
				@sqlite_seek($query_id, $rownum);
				return @sqlite_column($query_id, $column);
			}
		}

		return false;
	}
	
	/**
	 * Returns a specific field from a query.
	 *
	 * @param PDO Statement $query for the query ID.
	 * @param string $column for the name of the field to return.
	 * @param int/bool $row for the number of the row to fetch it from.
	 * @return mixed
	 */
	function sql_fetch_field($query, $column, $row = false)
	{
		if($row !== false)
		{
			@sqlite_seek($query, $row);
		}
		$array = $this->sql_fetch_array($query);
		return $array[$column];
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

		return ($query_id) ? @sqlite_seek($query_id, $rownum) : false;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		return ($this->db_connect_id) ? @sqlite_last_insert_rowid($this->db_connect_id) : false;
	}

	/**
	* Free sql result
	*/
	function sql_freeresult($query_id = false)
	{
		return true;
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		return @sqlite_escape_string($msg);
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
	* return sql error array
	* @private
	*/
	function _sql_error()
	{
		return array(
			'message'	=> @sqlite_error_string(@sqlite_last_error($this->db_connect_id)),
			'code'		=> @sqlite_last_error($this->db_connect_id)
		);
	}

	/**
	* Close sql connection
	* @private
	*/
	function _sql_close()
	{
		return @sqlite_close($this->db_connect_id);
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
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @sqlite_query($query, $this->db_connect_id);
				while ($void = @sqlite_fetch_array($result, SQLITE_ASSOC))
				{
					// Take the time spent on parsing rows into account
				}

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
	mx_message_die(CRITICAL_ERROR, "Could not connect to the database");
}
?>