<?php
/**
*
* @package dbal
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
* @modified by Boris Berdichevski 20/04/2014
*
*/

namespace phpbb\db\driver;

/**
* Sqlite Database Abstraction Layer
* Minimum Requirement: 2.8.2+
* But really support 3.7.7 only!
* @package dbal
*/
class sqlite extends \phpbb\db\driver\driver
{
	var $connect_error = '';
	var $db=null;
	var $query_result_objs = array();
	var $query_result_counter = 0;
	var $int_query;
	var $n_count = 2;		/* count of attempts */
	var $n_msec = 250000;	/* time-out in millisecons (bisyTimeout)*/

	function set_result($result, $result_id)
	{
		$this->query_result_objs[$result_id] = $result;
	}

	function get_result($result_id)
	{
		return $this->query_result_objs[$result_id];
	}

	function delete_result($result_id)
	{
		if (isset($this->query_result_objs[$result_id]) && is_object($this->query_result_objs[$result_id])) {
			$this->query_result_objs[$result_id]->finalize();
		}
		unset($this->query_result_objs[$result_id]);
	}
	/**
	* Connect to server
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		//.global $cache;
		//$cache = NULL;

		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		try{
			$this->db = new \SQLite3($this->server);
			$this->db_connect_id = 1;
			$this->db->exec('PRAGMA short_column_names = 1');
			} catch (Exception $error) {
				$this->connect_error = $error;
				return array('message' => $error);
			}
			return true;
	}

	/**
	* Version information about used database
	* @param bool $raw if true, only return the fetched sql_server_version
	* @param bool $use_cache if true, it is safe to retrieve the stored value from the cache
	* @return string sql server version
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		global $cache;

		if (!$use_cache || empty($cache) || ($this->sql_server_version = $cache->get('sqlite_version')) === false)
		{
			$vers = $this->db->version();
			$this->sql_server_version = $vers["versionString"];
			if (!empty($cache) && $use_cache)
			{
				$cache->put('sqlite_version', $this->sql_server_version);
			}
		}
		return ($raw) ? $this->sql_server_version : 'SQLite ' . $this->sql_server_version;
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
				return $this->db->query('BEGIN');
			break;

			case 'commit':
				return $this->db->query('COMMIT');
			break;

			case 'rollback':
				return $this->db->query('ROLLBACK');
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
			$was_error = false;

			if ($query != '')
			{
				global $cache;

				// EXPLAIN only in extra debug mode
				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('start', $query);
				}

				$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
				$this->sql_add_num_queries($this->query_result);
				if ($this->query_result === false)
				{
					$nc=0;
					for( $nc=0; $nc< $this->n_count; $nc++)
					{
						try {
							$this->db->busyTimeout($this->n_msec);
							$this->int_query = $query;
							$err_level = 0;
							if ($this->return_on_error) {
								$err_level = error_reporting(0);
							}
							if (strpos($query, 'SELECT') !== 0 && strpos($query, 'PRAGMA') !== 0)
							{
								//$err_level = 0;
								//if( $this->return_on_error )
								//	$err_level = error_reporting(0);

								$was_error = !($this->db->exec( $query ));
								$this->query_result_counter++;
								$this->query_result = $this->query_result_counter;
								$this->set_result( null, $this->query_result);
								//if( $this->return_on_error
								//	&& $nc >= $this->n_count -1 )
								//	error_reporting($err_level);
							}
							else
							{
								$this->query_result_counter++;
								$this->query_result = $this->query_result_counter;
								$res = $this->db->query( $query );
								$was_error = !(isset($res) && is_object($res));
								$this->set_result( $res, $this->query_result);
							}
							if ($this->return_on_error && $nc >= $this->n_count -1) {
								error_reporting($err_level);
							}
						} catch (Exception $error) {
							//if ($nc >= $this->n_count -1 )
							//	$this->sql_error($query);
							$was_error = true;
						}
						if (!$was_error) {
							break;
						}

						$was_error = false;
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
						//$this->query_result_id++;
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

			if ($was_error) {
				return false;
			}

			if( $this->query_result && $this->get_result($this->query_result) )
			{
				return $this->query_result;
			}

			$error_returned = $this->_sql_error();
			$error_code = $error_returned['code'];
			if ($error_code == 0) {
				return true;
			}
			return false;
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
			$total = -1;
		}

		$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);

		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? $this->db->changes() : false;
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

			if ( isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

			if( $query_id === false)
			{
				return false;
			}

			$query_result_obj = $this->get_result($query_id);
			if( isset($query_result_obj) && is_object($query_result_obj))
			{
				try{
					$row = $query_result_obj->fetchArray(SQLITE3_ASSOC);
				} catch (Exception $error) {
					$this->sql_error($this->int_query);
				}
			}
			else
			{
				return false;
			}

			if ( !$row || !sizeof($row) || !is_array($row))
			{
				return $row;
			}

			$rowx = array();
			foreach ($row as $key => $value)
			{
				$pos = strpos($key, '.');
				if( $pos >0 )
				{
					$keyx = substr($key, $pos+1);
					$rowx[$keyx] = $value;
				}
				else
				{
					$rowx[$key] = $value;
				}
			}

			return $rowx;
	}

	/**
	* Seek to given row number
	* rownum is zero-based
	*/
	function sql_rowseek($rownum, &$query_id)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (isset($cashe) && isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_rowseek($rownum, $query_id);
		}
		return true; //($query_id !== false) ? @sqlite_seek($query_id, $rownum) : false;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		return $this->db->lastInsertRowID();
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

			$this->delete_result($query_id);

		return true;
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		return $this->db->escapeString($msg);
	}

	/**
	* {@inheritDoc}
	*/
	function sql_lower_text($column_name)
	{
		return "LOWER(SUBSTRING($column_name, 1, DATALENGTH($column_name)))";
	}

	/**
	* Correctly adjust LIKE expression for special characters
	* For SQLite an underscore is a not-known character... this may change with SQLite3
	*/
	function sql_like_expression($expression)
	{
		// Unlike LIKE, GLOB is case sensitive (unfortunatly). SQLite users need to live with it!
		// We only catch * and ? here, not the character map possible on file globbing.
		$expression = str_replace(array(chr(0) . '_', chr(0) . '%'), array(chr(0) . '?', chr(0) . '*'), $expression);

		$expression = str_replace(array('?', '*'), array("\?", "\*"), $expression);
		$expression = str_replace(array(chr(0) . "\?", chr(0) . "\*"), array('?', '*'), $expression);

		return 'GLOB \'' . $this->sql_escape($expression) . '\'';
	}

	/**
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		if( $this->connect_error == '' ) {
			$error = array(
				'message'	=> $this->db->lastErrorMsg(),
				'code'		=> $this->db->lastErrorCode()
			);
		}
		else {
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
		return $this->db->close(); //@sqlite3_close($this->db_connect_id);
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
				break;

				case 'fromcache':
					$endtime = explode(' ', microtime());
					$endtime = $endtime[0] + $endtime[1];

					$results = $this->db->query( $query);
					while ($result= $results->fetchArray(SQLITE3_ASSOC))
					{
						// Take the time spent on parsing rows into account
					}

					$splittime = explode(' ', microtime());
					$splittime = $splittime[0] + $splittime[1];

					$this->sql_report('record_fromcache', $query, $endtime, $splittime);

					$results->finalize();

					break;
			}
	}

	/**
	* Return column types
	*/

	function fetch_column_types($table_name)
	{
		$col_types = array();
		$col_info_res  = $this->db->query( "PRAGMA table_info('". $table_name . "')");

		while ($col_info = $col_info_res->fetchArray(SQLITE3_ASSOC))
		{
			$column_name = $col_info['name'];
			$column_type = $col_info['type'];
			$col_types[$column_name] = $column_type;
		}
		$col_info_res->finalize();
		return $col_types;
	}

}
