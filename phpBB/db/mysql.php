<?php
/***************************************************************************
 *                                 mysql.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if ( !defined('SQL_LAYER') )
{

define('SQL_LAYER', 'mysql');

class sql_db
{
	var $db_connect_id;
	var $query_result;
	var $return_on_error = false;
	var $transaction = false;

	//
	// Constructor
	//
	function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $port, $persistency = false)
	{
		$this->open_queries = array();
		$this->num_queries = 0;

		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->password = $sqlpassword;
		$this->server = $sqlserver . ( ( $port ) ? ':' . $port : '' ); 
		$this->dbname = $database;

		$this->db_connect_id = ( $this->persistency ) ? @mysql_pconnect($this->server, $this->user, $this->password) : @mysql_connect($this->server, $this->user, $this->password);

		if ( $this->db_connect_id && $this->dbname != '')
		{
			if ( @mysql_select_db($this->dbname) )
			{
				return $this->db_connect_id;
			}
		}

		$this->sql_error('');
	}

	//
	// Other base methods
	//
	function sql_close()
	{
		if ( !$this->db_connect_id )
		{
			return false;
		}

		if ( count($this->open_queries) )
		{
			foreach($this->open_queries as $query_id)
			{
				@mysql_free_result($query_id);
			}
		}

		return @mysql_close($this->db_connect_id);
	}

	function sql_return_on_error($fail = false)
	{
		$this->return_on_error = $fail;
	}

	function sql_num_queries()
	{
		return $this->num_queries;
	}

	function sql_transaction($status = 'begin')
	{
		switch ( $status )
		{
			case 'begin':
				$this->transaction = true;
//				$result = mysql_query('BEGIN', $this->db_connect_id);
				break;
			case 'commit':
				$this->transaction = false;
//				$result = mysql_query('COMMIT', $this->db_connect_id);
				break;
			case 'rollback':
				$this->transaction = false;
//				$result = mysql_query('ROLLBACK', $this->db_connect_id);
				break;
			default:
				$result = true;
		}

		return $result;
	}

	//
	// Base query method
	//
	function sql_query($query = '', $transaction = false)
	{
		if ( $query != '' )
		{
			$this->query_result = false;
			$this->num_queries++;

			if ( !($this->query_result = @mysql_query($query, $this->db_connect_id)) )
			{
				$this->sql_error($query);
			}

			$this->open_queries[] = $this->query_result;
		}
		else
		{
			return false;
		}

		return ( $this->query_result) ? $this->query_result : ( ( $transaction == END_TRANSACTION ) ? true : false );
	}

	function sql_query_limit($query = '', $total, $offset, $transaction = false)
	{
		if ( $query != '' )
		{
			$this->query_result = false;
			$this->num_queries++;

			if ( !empty($total) )
			{
				$query .= ' LIMIT ' . ( ( !empty($offset) ) ? $offset . ', ' . $total : $total );
			}

			if ( !($this->query_result = @mysql_query($query, $this->db_connect_id)) )
			{
				$this->sql_error($query);
			}

			$this->open_queries[] = $this->query_result;
		}
		else
		{
			return false;
		}

		return ( $this->query_result) ? $this->query_result : ( ( $transaction == END_TRANSACTION ) ? true : false );
	}

	// Idea for this from Ikonboard
	function sql_query_array($query = '', $assoc_ary = false, $transaction = false)
	{
		if (!is_array($assoc_ary))
		{
			return false;
		}

		if (preg_match('/^INSERT/', $query))
		{
			$fields = array();
			$values = array();
			foreach ($assoc_ary as $key => $var)
			{
				$fields[] = $key;

				if (is_null($var))
				{
					$values[] = 'NULL';
				}
				elseif (is_string($var))
				{
					$values[] = "'" . str_replace("'", "''", $var) . "'";
				}
				else
				{
					$values[] = $var;
				}
			}

			$query = $query . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
		}
		else
		{
			$values = array();
			foreach ($assoc_ary as $key => $var)
			{
				if (is_null($var))
				{
					$values[] = "$key = NULL";
				}
				elseif (is_string($var))
				{
					$values[] = "$key = '" . str_replace("'", "''", $var) . "'";
				}
				else
				{
					$values[] = "$key = $var";
				}
			}

			$query = preg_replace('/^(.*? SET )(.*?)$/is', '\1' . implode(', ', $values) . ' \2', $query);
		}

		return $this->sql_query($query);
	}

	//
	// Other query methods
	//
	// NOTE :: Want to remove _ALL_ reliance on sql_numrows from core code ...
	//         don't want this here by a middle Milestone
	//
	function sql_numrows($query_id = false)
	{
		if ( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @mysql_num_rows($query_id) : false;
	}

	function sql_affectedrows()
	{
		return ( $this->db_connect_id ) ? @mysql_affected_rows($this->db_connect_id) : false;
	}

	function sql_fetchrow($query_id = 0)
	{
		if ( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @mysql_fetch_array($query_id) : false;
	}

	function sql_fetchrowset($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
			while($this->rowset[$query_id] = @mysql_fetch_array($query_id))
			{
				$result[] = $this->rowset[$query_id];
			}
			return $result;
		}
		else
		{
			return false;
		}
	}

	function sql_fetchfield($field, $rownum = -1, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			if($rownum > -1)
			{
				$result = @mysql_result($query_id, $rownum, $field);
			}
			else
			{
				if(empty($this->row[$query_id]) && empty($this->rowset[$query_id]))
				{
					if($this->sql_fetchrow())
					{
						$result = $this->row[$query_id][$field];
					}
				}
				else
				{
					if($this->rowset[$query_id])
					{
						$result = $this->rowset[$query_id][$field];
					}
					else if($this->row[$query_id])
					{
						$result = $this->row[$query_id][$field];
					}
				}
			}
			return $result;
		}
		else
		{
			return false;
		}
	}

	function sql_rowseek($rownum, $query_id = 0)
	{
		if ( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @mysql_data_seek($query_id, $rownum) : false;
	}

	function sql_nextid()
	{
		return ( $this->db_connect_id ) ? @mysql_insert_id($this->db_connect_id) : false;
	}

	function sql_freeresult($query_id = false)
	{
		if ( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? @mysql_free_result($query_id) : false;
	}

	function sql_error($sql = '')
	{
		global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

		if ( !$this->return_on_error )
		{
			if ( $this->transaction )
			{
				$this->sql_transaction(ROLLBACK);
			}

			$this_page = ( !empty($HTTP_SERVER_VARS['PHP_SELF']) ) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_ENV_VARS['PHP_SELF'];
			$this_page .= '&' . ( ( !empty($HTTP_SERVER_VARS['QUERY_STRING']) ) ? $HTTP_SERVER_VARS['QUERY_STRING'] : $HTTP_ENV_VARS['QUERY_STRING'] );

			$message = '<u>SQL ERROR</u> [ ' . SQL_LAYER . ' ]<br /><br />' . @mysql_error() . '<br /><br /><u>PAGE</u><br /><br />'  . $this_page . ( ( $sql != '' ) ? '<br /><br /><u>SQL</u><br /><br />' . $sql : '' ) . '<br />';
			message_die(ERROR, $message);
		}

		$result['message'] = @mysql_error();
		$result['code'] = @mysql_errno();

		return $result;
	}

} // class sql_db

} // if ... define

?>