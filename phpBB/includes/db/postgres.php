<?php
/**
*
* @package DBal
* @version $Id: postgres.php,v 1.1 2008/12/08 13:28:56 orynider Exp $
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
if (!is_object('dbal_postgres'))
{
	define('SQL_LAYER', 'postgres');
	include_once($phpbb_root_path . 'includes/db/dbal.' . $phpEx);
	$sql_db = 'dbal_' . $dbms; // Repopulated for multiple db connections

/**
* @package DBal
* PostgreSQL Database Abstraction Layer
* Minimum Requirement is Version 7.3+
*/
class dbal_postgres extends dbal
{
	var $last_query_text = '';
	var $pgsql_version;

	/**
	* Connect to server
	* downgraded for phpBB2 backend
	* @access public
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
			$connect_string .= "password=$sqlpassword ";
		}

		if ($sqlserver)
		{
			if (strpos($sqlserver, ':') !== false)
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

		$this->db_connect_id = ($this->persistency) ? @pg_pconnect($connect_string, $new_link) : @pg_connect($connect_string, $new_link);

		if ($this->db_connect_id)
		{
			// determine what version of PostgreSQL is running, we can be more efficient if they are running 8.2+
			if (version_compare(PHP_VERSION, '5.0.0', '>='))
			{
				$this->pgsql_version = @pg_parameter_status($this->db_connect_id, 'server_version');
			}
			else
			{
				$query_id = @pg_query($this->db_connect_id, 'SELECT VERSION()');
				$row = @pg_fetch_assoc($query_id, null);
				@pg_free_result($query_id);

				if (!empty($row['version']))
				{
					$this->pgsql_version = substr($row['version'], 10);
				}
			}

			if (!empty($this->pgsql_version) && $this->pgsql_version[0] >= '8' && $this->pgsql_version[2] >= '2')
			{
				$this->multi_insert = true;
			}

			if ($schema !== '')
			{
				@pg_query($this->db_connect_id, 'SET search_path TO ' . $schema);
			}
			return $this->db_connect_id;
		}

		return $this->sql_error('');
	}
	
	function sql_connect_id()
	{
		return "SELECT pg_backend_pid()";
	}

	/**
	* Version information about used database
	*/
	function sql_server_info()
	{
		return 'PostgreSQL ' . $this->pgsql_version;
	}

	/**
	* sql transaction
	*/
	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				$result = @pg_query($this->db_connect_id, 'BEGIN');
				$this->transaction = true;
				break;

			case 'commit':
				$result = @pg_query($this->db_connect_id, 'COMMIT');
				$this->transaction = false;

				if (!$result)
				{
					@pg_query($this->db_connect_id, 'ROLLBACK');
				}
				break;

			case 'rollback':
				$result = @pg_query($this->db_connect_id, 'ROLLBACK');
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

			if (strpos($query, 'SELECT') === 0 && strpos($query, 'FROM (') !== false)
			{
				$query = preg_replace('#FROM \(([^)]+)\)\s#', 'FROM \1 ', $query);
			}

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('start', $query);
			}

			$this->last_query_text = $query;
			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;

			if (!$this->query_result)
			{
				$this->num_queries++;

				if (($this->query_result = @pg_exec($this->db_connect_id, $query)) === false)
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

			$query .= "\n LIMIT $total OFFSET $offset";

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

		return ($query_id) ? @pg_num_rows($query_id) : false;
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
			$result = @pg_num_fields($query_id);
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
			$result = pg_field_name($query_id, $offset);
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
			$result = pg_field_type($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Check if a field exists in a table.
	 *
	 * @param string $column for field name.
	 * @param string $table_name for table name.
	 * @return boolean true or false if not exists.
	 */
	function sql_fieldexists($column, $table_name)
	{
		$query = $this->sql_query("SELECT COUNT(column_name) as column_names FROM information_schema.columns WHERE table_name=$table_name AND column_name=$column");
		$exists = $this->sql_numrows($query);
		
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
	 * Gets a list of columns of a table.
	 *
	 * @param string $table_name	table name
	 * @return array of columns names (all lower case)
	 */
	function sql_list_columns($table_name)
	{
		$columns = array();

		$sql = "SELECT a.attname
			FROM pg_class c, pg_attribute a
			WHERE c.relname = '{$table_name}'
				AND a.attnum > 0
				AND a.attrelid = c.oid";
		$result = $this->sql_query($sql);

		while ($row = $this->sql_fetchrow($result))
		{
			$column = strtolower(current($row));
			$columns[$column] = $column;
		}
		$this->sql_freeresult($result);

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
		$query = $this->sql_query("SELECT COUNT(column_name) as column_names FROM information_schema.columns WHERE table_name='{$table_name}' AND column_name='{$column}'");
		$exists = $this->sql_fetch_field($query, "column_names");

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
	 * Check if a table exists in a database.
	 *
	 * @param string $table_name The table name.
	 * @return boolean true or false if not exists.
	 */
	function sql_table_exists($table_name)
	{
		// Execute on master server to ensure if we've just created a table that we get the correct result
		$query = $this->sql_query("SELECT COUNT(table_name) as table_names FROM information_schema.tables WHERE table_schema = 'public' AND table_name='{$table_name}'");
		$exists = $this->sql_fetch_field($query, 'table_names');
		
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
		return ($this->query_result) ? @pg_cmdtuples($this->query_result) : false;
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

		return ($query_id) ? @pg_fetch_assoc($query_id, NULL) : false;
	}
	
	/**
	 * Return a result array for a query.
	 *
	 * @param resource $query for the query_id.
	 * @param int $resulttype for the type of array to return:
	 * PGSQL_NUM, PGSQL_BOTH or PGSQL_ASSOC
	 * @return array for the query of result. 
	 */
	function sql_fetch_array($query, $resulttype=PGSQL_ASSOC)
	{
		switch($resulttype)
		{
			case PGSQL_NUM:
			case PGSQL_BOTH:
			case PGSQL_BOTH:
			break;
			default:
				$resulttype = PGSQL_ASSOC;
			break;
		}
		return @pg_fetch_array($query, NULL, $resulttype);
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
			if ($rownum !== false)
			{
				$this->sql_rowseek($rownum, $query_id);
			}

			$row = $this->sql_fetchrow($query_id);
			return isset($row[$column]) ? $row[$column] : false;
		}

		return false;
	}
	
	/**
	 * Return a specific field from a query.
	 *
	 * @param resource $query The query ID.
	 * @param string $column The name of the field to return.
	 * @param int|bool The number of the row to fetch it from.
	 * @return string|bool|null As per http://php.net/manual/en/function.pg-fetch-result.php
	 */
	function sql_fetch_field($query, $column, $row=false)
	{
		if($row === false)
		{
			$array = $this->sql_fetch_array($query);
			return $array[$column];
		}
		else
		{
			return @pg_fetch_result($query, $row, $column);
		}
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

		return ($query_id) ? @pg_result_seek($query_id, $rownum) : false;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		$query_id = $this->query_result;

		if ($query_id && $this->last_query_text != '')
		{
			if (preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $this->last_query_text, $table_name))
			{
				$query = "SELECT currval('" . $table_name[1] . "_id_seq') AS last_value";
				$temp_q_id =  @pg_query($this->db_connect_id, $query);
				if (!$temp_q_id)
				{
					return false;
				}

				$temp_result = @pg_fetch_assoc($temp_q_id, NULL);
				@pg_free_result($query_id);

				return ($temp_result) ? $temp_result['last_value'] : false;
			}
		}

		return false;
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
			return @pg_free_result($query_id);
		}
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		// Do not use for bytea values
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
			'message'	=> (!$this->db_connect_id) ? @pg_last_error() : @pg_last_error($this->db_connect_id),
			'code'		=> ''
		);
	}

	/**
	* Close sql connection
	* @private
	*/
	function _sql_close()
	{
		return @pg_close($this->db_connect_id);
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

				$result = @pg_query($this->db_connect_id, $query);
				while ($void = @pg_fetch_assoc($result, NULL))
				{
					// Take the time spent on parsing rows into account
				}
				@pg_free_result($result);

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

} // if ... defined

// Connect to DB
$db	= new $sql_db();
if(!$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, false))
{
	mx_message_die(CRITICAL_ERROR, "Could not connect to the database");
}
?>