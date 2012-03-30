<?php
/**
*
* @package dbal
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* Sqlite Database Abstraction Layer
* Minimum Requirement: 3+
* @package dbal
*/
class dbal_sqlite_3 extends dbal
{

      /**
      * Connect to server
      */
      function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
      {
            $this->persistency = $persistency;
            $this->user = $sqluser;
            $this->server = $sqlserver . (($port) ? ':' . $port : '');
            $this->dbname = $database;

            $error = 'Could not open database';
            $this->db_connect_id = @sqlite3_open($this->server);

            if ($this->db_connect_id)
            {
                  @sqlite3_query($this->db_connect_id, 'PRAGMA short_column_names = 1');
            }

            return ($this->db_connect_id) ? true : array('message' => $error);
      }

      /**
      * Version information about used database
      * @param bool $raw if true, only return the fetched sql_server_version
      * @return string sql server version
      */
      function sql_server_info($raw = false)
      {
            $this->sql_server_version = @sqlite3_libversion();
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
                        return @sqlite3_query($this->db_connect_id, 'BEGIN');
                  break;

                  case 'commit':
                        return @sqlite3_query($this->db_connect_id, 'COMMIT');
                  break;

                  case 'rollback':
                        return @sqlite3_query($this->db_connect_id, 'ROLLBACK');
                  break;
            }

            return true;
      }

      /**
      * Base query method
      *
      * @param      string      $query            Contains the SQL query which shall be executed
      * @param      int            $cache_ttl      Either 0 to avoid caching or the time in seconds which the result shall be kept in cache
      * @return      mixed                        When casted to bool the returned value returns true on success and false on failure
      *
      * @access      public
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
                  $this->sql_add_num_queries($this->query_result);

                  if ($this->query_result === false)
                  {
                        $this->query_result = @sqlite3_query($this->db_connect_id, $query );

                        if ($this->query_result === false)
                        {
                             $this->sql_error($query);
                        }
                        else
                        if ( strpos($query, 'SELECT') !== 0 && strpos($query, 'PRAGMA') !== 0)
                        {
                              @sqlite3_query_exec($this->query_result, true );
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
            return ($this->db_connect_id) ? @sqlite3_changes($this->db_connect_id) : false;
      }

      /**
      * Fetch current row
      */
      function sql_fetchrow($query_id = false)
      {
            global $cache;

            if ($query_id == false)
            {
                  $query_id = $this->query_result;
            }

            if (isset($cache->sql_rowset[$query_id]))
            {
                  return $cache->sql_fetchrow($query_id);
            }

            if($query_id == false)
            {
            		return false;
            }
            
            $row = @sqlite3_fetch_array($query_id);
            if (!sizeof($row) || !is_array($row))
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

            if ($query_id == false)
            {
                  $query_id = $this->query_result;
            }

            if (isset($cache->sql_rowset[$query_id]))
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
            return ($this->db_connect_id) ? @sqlite3_last_insert_rowid($this->db_connect_id) : false;
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

            return true;
      }

      /**
      * Escape string used in sql query
      */
      function sql_escape($msg)
      {
            return @sqlite3_escapeString($msg);
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
            return array(
                  'message'      => @sqlite_error_string(@sqlite_last_error($this->db_connect_id)),
                  'code'            => @sqlite_last_error($this->db_connect_id)
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
            return @sqlite3_close($this->db_connect_id);
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

                        $result = @sqlite3_query($this->db_connect_id, $query);
                        while ($void = @sqlite3_fetch_array($result))
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
      * Return column types
      */

      function fetch_column_types($table_name)
      {
                  $col_types = array();
                  $tbl = @sqlite3_query($this->db_connect_id, "PRAGMA table_info('". $table_name . "')");
                  
                  while ($col_info = @sqlite3_fetch_array($tbl))
                  {
                        $column_name = $col_info[name];
                        $column_type = $col_info[type];
                        $col_types[$column_name] = $column_type;
                  }
                  return $col_types;
      }
}

?>
