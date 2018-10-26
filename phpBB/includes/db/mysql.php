<?php
/**
*
* @package DBal
* @version $Id: mysql.php,v 1.1 2008/12/08 13:28:56 orynider Exp $
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
if (!is_object('dbal_mysql'))
{
	define('SQL_LAYER', 'mysql');
	include_once($phpbb_root_path . 'includes/db/dbal.' . $phpEx);
	$sql_db = 'dbal_' . $dbms; // Repopulated for multiple db connections

/**
* @package DBal
* MySQL Database Abstraction Layer
* Minimum Requirement is 3.23+/4.0+/4.1+
*/
class dbal_mysql extends dbal
{
	var $mysql_version;
	var $multi_insert = true;
	
	/**
	* Connect to server
	* downgraded for phpBB2 backend
	* @access public
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		$this->sql_layer = 'mysql4';

		$this->db_connect_id = ($this->persistency) ? @mysql_pconnect($this->server, $this->user, $sqlpassword, $new_link) : @mysql_connect($this->server, $this->user, $sqlpassword, $new_link);
		
		if ($this->db_connect_id && $this->dbname != '')
		{
			if (@mysql_select_db($this->dbname, $this->db_connect_id))
			{
				// Determine what version we are using and if it natively supports UNICODE
				$this->mysql_version = mysql_get_server_info($this->db_connect_id);
				
				if (version_compare($this->mysql_version, '4.1.3', '>='))
				{
					if (UTF_STATUS === 'phpbb3')
					{				
						@mysql_query("SET NAMES 'utf8'", $this->db_connect_id);
						// enforce strict mode on databases that support it
					}
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
				else if (version_compare($this->mysql_version, '4.0.0', '<'))
				{
					$this->sql_layer = 'mysql';
				}

				return $this->db_connect_id;
			}
		}
		return $this->sql_error();
	}

	function sql_connect_id()
	{
		return "SELECT CONNECTION_ID()";
	}
	
	/**
	* Version information about used database
	*/
	function sql_server_info()
	{
		return 'MySQL ' . $this->mysql_version;
	}

	/**
	* sql transaction
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
			$result = @mysql_field_type($query_id, $offset);
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
	 * @param string $table_name	Table name
	 * @return array		Array of column names (all lower case)
	 */
	function sql_list_columns($table_name)
	{
		$columns = array();

		switch ($this->sql_layer)
		{
			case 'mysql_40':
			case 'mysql_41':
				$sql = "SHOW COLUMNS FROM $table_name";
			break;

			case 'oracle':
				$sql = "SELECT column_name
					FROM user_tab_columns
					WHERE LOWER(table_name) = '" . strtolower($table_name) . "'";
			break;

			case 'sqlite3':
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
			break;
		}

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
	 * Gets a list of columns of a table.
	 *
	 * @param string $table_name	table name
	 * @return array of columns names (all lower case)
	 */
	function sql_list_columns($table_name)
	{
		$columns = array();
		$result = $this->sql_query("SHOW COLUMNS FROM $table_name");
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
		$query = $this->sql_query("SHOW COLUMNS FROM $table_name LIKE '$column'");
		$exists = $this->sql_numrows($query);
		
		if($exists > 0)
		{
			return true;
		}
		else
		{
			$columns = $this->sql_list_columns($table_name);
			return isset($columns[$column]);
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
		// Execute on master server to ensure if we've just created a table that we get the correct result
		$query = (version_compare($this->sql_get_version(), '5.0.2', '>=')) ? $this->sql_query("SHOW FULL TABLES FROM `".$this->dbname."` WHERE table_type = 'BASE TABLE' AND `Tables_in_".$this->dbname."` = '$table_name'") : $this->sql_query("SHOW TABLES LIKE '$table_name'");
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
	 * Return a result array for a query.
	 *
	 * @param resource $query for the query_id.
	 * @param int $resulttype for the type of array to return: 
	 *  MYSQL_NUM, MYSQL_BOTH or MYSQL_ASSOC
	 * @return array for the query of result.
	 */
	function sql_fetch_array($query, $resulttype=MYSQL_ASSOC)
	{
		switch($resulttype)
		{
			case MYSQL_NUM:
			case MYSQL_BOTH:
			case MYSQL_ASSOC:
			break;
			
			default:
				$resulttype = MYSQL_ASSOC;
			break;
		}
		return @mysql_fetch_array($query, $resulttype);
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
				$row = $this->sql_fetchrow($query_id);
				return isset($row[$column]) ? $row[$column] : false;
			}
			else
			{
				return @mysql_result($query_id, $rownum, $column);
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
	 * Gets db version.
	 *
	 * @return string Version of this db.
	 */
	function sql_get_version()
	{
		if($this->version)
		{
			return $this->version;
		}
		
		$query = $this->sql_query("SELECT VERSION() as version");
		$ver = $this->sql_fetch_array($query);
		$version = $ver['version'];
		
		if($version)
		{
			$version = explode(".", $version, 3);
			$this->version = (int)$version[0].".".(int)$version[1].".".(int)$version[2];
		}
		return $this->version;
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
		$table_name_status = $this->get_table_status($table_name);

		if (isset($table_name_status['Engine']))
		{
			if ($table_name_status['Engine'] === 'MyISAM')
			{
				return $table_name_status['Rows'];
			}
			else if ($table_name_status['Engine'] === 'InnoDB' && $table_name_status['Rows'] > 100000)
			{
				return '~' . $table_name_status['Rows'];
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
		$table_name_status = $this->get_table_status($table_name);

		if (isset($table_name_status['Engine']) && $table_name_status['Engine'] === 'MyISAM')
		{
			return $table_name_status['Rows'];
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
		$table_name_status = $this->sql_fetchrow($result);
		$this->sql_freeresult($result);

		return $table_name_status;
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
	mx_message_die(CRITICAL_ERROR, "Could not connect to the database");
}
?>