<?php
/**
*
* @package dbal
* @version $Id$
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* This is the MS SQL Server Native database abstraction layer.
* PHP mssql native driver required.
* @author Chris Pucci
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

include_once($phpbb_root_path . 'includes/db/dbal.' . $phpEx);

/**
 * Prior to version 1.1 the SQL Server Native PHP driver didn't support sqlsrv_num_rows, or cursor based seeking so we recall all rows into an array
 * and maintain our own cursor index into that array.
 */
class result_mssqlnative
{
	public function result_mssqlnative($queryresult = false)
	{
		$this->m_cursor = 0;
		$this->m_rows = array();
		$this->m_num_fields = sqlsrv_num_fields($queryresult);
		$this->m_field_meta = sqlsrv_field_metadata($queryresult);

		while ($row = sqlsrv_fetch_array($queryresult, SQLSRV_FETCH_ASSOC))
		{
			if ($row !== null)
			{
				foreach($row as $k => $v)
				{
					if (is_object($v) && method_exists($v, 'format'))
					{
						$row[$k] = $v->format("Y-m-d\TH:i:s\Z");
					}
				}
				$this->m_rows[] = $row;//read results into memory, cursors are not supported
			}
		}

		$this->m_row_count = sizeof($this->m_rows);
	}

	private function array_to_obj($array, &$obj)
	{
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$obj->$key = new stdClass();
				array_to_obj($value, $obj->$key);
			}
			else
			{
				$obj->$key = $value;
			}
		}
		return $obj;
	}

	public function fetch($mode = SQLSRV_FETCH_BOTH, $object_class = 'stdClass')
	{
		if ($this->m_cursor >= $this->m_row_count || $this->m_row_count == 0)
		{
			return false;
		}

		$ret = false;
		$arr_num = array();

		if ($mode == SQLSRV_FETCH_NUMERIC || $mode == SQLSRV_FETCH_BOTH)
		{
			foreach($this->m_rows[$this->m_cursor] as $key => $value)
			{
				$arr_num[] = $value;
			}
		}

		switch ($mode)
		{
			case SQLSRV_FETCH_ASSOC:
				$ret = $this->m_rows[$this->m_cursor];
			break;
			case SQLSRV_FETCH_NUMERIC:
				$ret = $arr_num;
			break;
			case 'OBJECT':
				$ret = $this->array_to_obj($this->m_rows[$this->m_cursor], $o = new $object_class);
			break;
			case SQLSRV_FETCH_BOTH:
			default:
				$ret = $this->m_rows[$this->m_cursor] + $arr_num;
			break;
		}
		$this->m_cursor++;
		return $ret;
	}

	public function get($pos, $fld)
	{
		return $this->m_rows[$pos][$fld];
	}

	public function num_rows()
	{
		return $this->m_row_count;
	}

	public function seek($iRow)
	{
		$this->m_cursor = min($iRow, $this->m_row_count);
	}

	public function num_fields()
	{
		return $this->m_num_fields;
	}

	public function field_name($nr)
	{
		$arr_keys = array_keys($this->m_rows[0]);
		return $arr_keys[$nr];
	}

	public function field_type($nr)
	{
		$i = 0;
		$int_type = -1;
		$str_type = '';

		foreach ($this->m_field_meta as $meta)
		{
			if ($nr == $i)
			{
				$int_type = $meta['Type'];
				break;
			}
			$i++;
		}

		//http://msdn.microsoft.com/en-us/library/cc296183.aspx contains type table
		switch ($int_type)
		{
			case SQLSRV_SQLTYPE_BIGINT: 		$str_type = 'bigint'; break;
			case SQLSRV_SQLTYPE_BINARY: 		$str_type = 'binary'; break;
			case SQLSRV_SQLTYPE_BIT: 			$str_type = 'bit'; break;
			case SQLSRV_SQLTYPE_CHAR: 			$str_type = 'char'; break;
			case SQLSRV_SQLTYPE_DATETIME: 		$str_type = 'datetime'; break;
			case SQLSRV_SQLTYPE_DECIMAL/*($precision, $scale)*/: $str_type = 'decimal'; break;
			case SQLSRV_SQLTYPE_FLOAT: 			$str_type = 'float'; break;
			case SQLSRV_SQLTYPE_IMAGE: 			$str_type = 'image'; break;
			case SQLSRV_SQLTYPE_INT: 			$str_type = 'int'; break;
			case SQLSRV_SQLTYPE_MONEY: 			$str_type = 'money'; break;
			case SQLSRV_SQLTYPE_NCHAR/*($charCount)*/: $str_type = 'nchar'; break;
			case SQLSRV_SQLTYPE_NUMERIC/*($precision, $scale)*/: $str_type = 'numeric'; break;
			case SQLSRV_SQLTYPE_NVARCHAR/*($charCount)*/: $str_type = 'nvarchar'; break;
			case SQLSRV_SQLTYPE_NTEXT: 			$str_type = 'ntext'; break;
			case SQLSRV_SQLTYPE_REAL: 			$str_type = 'real'; break;
			case SQLSRV_SQLTYPE_SMALLDATETIME: 	$str_type = 'smalldatetime'; break;
			case SQLSRV_SQLTYPE_SMALLINT: 		$str_type = 'smallint'; break;
			case SQLSRV_SQLTYPE_SMALLMONEY: 	$str_type = 'smallmoney'; break;
			case SQLSRV_SQLTYPE_TEXT: 			$str_type = 'text'; break;
			case SQLSRV_SQLTYPE_TIMESTAMP: 		$str_type = 'timestamp'; break;
			case SQLSRV_SQLTYPE_TINYINT: 		$str_type = 'tinyint'; break;
			case SQLSRV_SQLTYPE_UNIQUEIDENTIFIER: $str_type = 'uniqueidentifier'; break;
			case SQLSRV_SQLTYPE_UDT: 			$str_type = 'UDT'; break;
			case SQLSRV_SQLTYPE_VARBINARY/*($byteCount)*/: $str_type = 'varbinary'; break;
			case SQLSRV_SQLTYPE_VARCHAR/*($charCount)*/: $str_type = 'varchar'; break;
			case SQLSRV_SQLTYPE_XML: 			$str_type = 'xml'; break;
			default: $str_type = $int_type;
		}
		return $str_type;
	}

	public function free()
	{
		unset($this->m_rows);
		return;
	}
}

/**
* @package dbal
*/
class dbal_mssqlnative extends dbal
{
	var $m_insert_id = NULL;
	var $last_query_text = '';
	var $query_options = array();

	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		# Test for driver support, to avoid suppressed fatal error
		if (!function_exists('sqlsrv_connect'))
		{
			trigger_error('Native MS SQL Server driver for PHP is missing or needs to be updated. Version 1.1 or later is required to install phpBB3. You can download the driver from: http://www.microsoft.com/sqlserver/2005/en/us/PHP-Driver.aspx\n', E_USER_ERROR);
		}

		//set up connection variables
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->dbname = $database;
		$port_delimiter = (defined('PHP_OS') && substr(PHP_OS, 0, 3) === 'WIN') ? ',' : ':';
		$this->server = $sqlserver . (($port) ? $port_delimiter . $port : '');

		//connect to database
		error_reporting(E_ALL);
		$this->db_connect_id = sqlsrv_connect($this->server, array(
			'Database' => $this->dbname,
			'UID' => $this->user,
			'PWD' => $sqlpassword
		));

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database
	* @param bool $raw if true, only return the fetched sql_server_version
	* @param bool $use_cache If true, it is safe to retrieve the value from the cache
	* @return string sql server version
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		global $cache;

		if (!$use_cache || empty($cache) || ($this->sql_server_version = $cache->get('mssql_version')) === false)
		{
			$arr_server_info = sqlsrv_server_info($this->db_connect_id);
			$this->sql_server_version = $arr_server_info['SQLServerVersion'];

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
	function sql_buffer_nested_transactions()
	{
		return true;
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
				return sqlsrv_begin_transaction($this->db_connect_id);
			break;

			case 'commit':
				return sqlsrv_commit($this->db_connect_id);
			break;

			case 'rollback':
				return sqlsrv_rollback($this->db_connect_id);
			break;
		}
		return true;
	}

	/**
	* Base query method
	*
	* @param	string	$query		Contains the SQL query which shall be executed
	* @param	int		$cache_ttl	Either 0 to avoid caching or the time in seconds which the result shall be kept in cache
	* @return	mixed				When casted to bool the returned value returns true on success and false on failure
	*
	* @access	public
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

			$this->last_query_text = $query;
			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if ($this->query_result === false)
			{
				if (($this->query_result = @sqlsrv_query($this->db_connect_id, $query, array(), $this->query_options)) === false)
				{
					$this->sql_error($query);
				}
				// reset options for next query
				$this->query_options = array();

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
		return $this->query_result;
	}

	/**
	* Build LIMIT query
	*/
	function _sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$this->query_result = false;

		// total == 0 means all results - not zero results
		if ($offset == 0 && $total !== 0)
		{
			if (strpos($query, "SELECT") === false)
			{
				$query = "TOP {$total} " . $query;
			}
			else
			{
				$query = preg_replace('/SELECT(\s*DISTINCT)?/Dsi', 'SELECT$1 TOP '.$total, $query);
			}
		}
		else if ($offset > 0)
		{
			$query = preg_replace('/SELECT(\s*DISTINCT)?/Dsi', 'SELECT$1 TOP(10000000) ', $query);
			$query = 'SELECT *
					FROM (SELECT sub2.*, ROW_NUMBER() OVER(ORDER BY sub2.line2) AS line3
					FROM (SELECT 1 AS line2, sub1.* FROM (' . $query . ') AS sub1) as sub2) AS sub3';

			if ($total > 0)
			{
				$query .= ' WHERE line3 BETWEEN ' . ($offset+1) . ' AND ' . ($offset + $total);
			}
			else
			{
				$query .= ' WHERE line3 > ' . $offset;
			}
		}

		$result = $this->sql_query($query, $cache_ttl);

		return $result;
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return (!empty($this->query_result)) ? @sqlsrv_rows_affected($this->query_result) : false;
	}

	/**
	* Fetch current row
	*/
	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

		if ($query_id === false)
		{
			return false;
		}

		$row = @sqlsrv_fetch_array($query_id, SQLSRV_FETCH_ASSOC);

		if ($row)
		{
			foreach ($row as $key => $value)
			{
				$row[$key] = ($value === ' ' || $value === NULL) ? '' : $value;
			}

			// remove helper values from LIMIT queries
			if (isset($row['line2']))
			{
				unset($row['line2'], $row['line3']);
			}
		}
		return $row;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		$result_id = @sqlsrv_query($this->db_connect_id, 'SELECT @@IDENTITY');

		if ($result_id !== false)
		{
			$row = @sqlsrv_fetch_array($result_id);
			$id = $row[0];
			@sqlsrv_free_stmt($result_id);
			return $id;
		}
		else
		{
			return false;
		}
	}

	/**
	* Free sql result
	*/
	function sql_freeresult($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_freeresult($query_id);
		}

		if (isset($this->open_queries[$query_id]))
		{
			unset($this->open_queries[$query_id]);
			return @sqlsrv_free_stmt($query_id);
		}
		return false;
	}

	/**
	* Escape string used in sql query
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
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		$errors = @sqlsrv_errors(SQLSRV_ERR_ERRORS);
		$error_message = '';
		$code = 0;

		if ($errors != null)
		{
			foreach ($errors as $error)
			{
				$error_message .= "SQLSTATE: ".$error[ 'SQLSTATE']."\n";
				$error_message .= "code: ".$error[ 'code']."\n";
				$code = $error['code'];
				$error_message .= "message: ".$error[ 'message']."\n";
			}
			$this->last_error_result = $error_message;
			$error = $this->last_error_result;
		}
		else
		{
			$error = (isset($this->last_error_result) && $this->last_error_result) ? $this->last_error_result : array();
		}

		return array(
			'message'	=> $error,
			'code'		=> $code,
		);
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
		return @sqlsrv_close($this->db_connect_id);
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
				@sqlsrv_query($this->db_connect_id, 'SET SHOWPLAN_TEXT ON;');
				if ($result = @sqlsrv_query($this->db_connect_id, $query))
				{
					@sqlsrv_next_result($result);
					while ($row = @sqlsrv_fetch_array($result))
					{
						$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
					}
				}
				@sqlsrv_query($this->db_connect_id, 'SET SHOWPLAN_TEXT OFF;');
				@sqlsrv_free_stmt($result);

				if ($html_table)
				{
					$this->html_hold .= '</table>';
				}
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @sqlsrv_query($this->db_connect_id, $query);
				while ($void = @sqlsrv_fetch_array($result))
				{
					// Take the time spent on parsing rows into account
				}
				@sqlsrv_free_stmt($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}

	/**
	* Utility method used to retrieve number of rows
	* Emulates mysql_num_rows
	* Used in acp_database.php -> write_data_mssqlnative()
	* Requires a static or keyset cursor to be definde via
	* mssqlnative_set_query_options()
	*/
	function mssqlnative_num_rows($res)
	{
		if ($res !== false)
		{
			return sqlsrv_num_rows($res);
		}
		else
		{
			return false;
		}
	}

	/**
	* Allows setting mssqlnative specific query options passed to sqlsrv_query as 4th parameter.
	*/
	function mssqlnative_set_query_options($options)
	{
		$this->query_options = $options;
	}
}

?>
