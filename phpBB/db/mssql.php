<?php
/***************************************************************************
 *                                 mssql.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : supportphpbb.com
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

if(!defined("SQL_LAYER"))
{

define("SQL_LAYER","mssql");

class sql_db
{

	var $db_connect_id;
	var $result;

	var $next_id;
	var $in_transaction = 0;

	var $row;
	var $limit_offset;
	var $query_limit_success;

	var $num_queries = 0;

	//
	// Constructor
	//
	function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true)
	{
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->password = $sqlpassword;
		$this->server = $sqlserver;
		$this->dbname = $database;

		$this->db_connect_id = ( $this->persistency ) ? mssql_pconnect($this->server, $this->user, $this->password) : mssql_connect($this->server, $this->user, $this->password);

		if( $this->db_connect_id && $this->dbname != "" )
		{
			if( !mssql_select_db($this->dbname, $this->db_connect_id) )
			{
				mssql_close($this->db_connect_id);
				return false;
			}
		}

		return $this->db_connect_id;
	}

	//
	// Other base methods
	//
	function sql_close()
	{
		if($this->db_connect_id)
		{
			//
			// Commit any remaining transactions
			//
			if( $this->in_transaction )
			{
				@mssql_query("COMMIT", $this->db_connect_id);
			}

			return @mssql_close($this->db_connect_id);
		}
		else
		{
			return false;
		}
	}


	//
	// Query method
	//
	function sql_query($query = "", $transaction = FALSE)
	{
		//
		// Remove any pre-existing queries
		//
		unset($this->result);
		unset($this->row);

		if( $query != "" )
		{
			$this->num_queries++;

			if($transaction == BEGIN_TRANSACTION)
			{
				if( !mssql_query("BEGIN TRANSACTION", $this->db_connect_id) )
				{
					return false;
				}
				$this->in_transaction = TRUE;
			}

			//
			// Does query contain any LIMIT code? If so pull out relevant start and num_results
			// This isn't terribly easy with MSSQL, whatever you do will potentially impact
			// performance compared to an 'in-built' limit
			//
			// Another issue is the 'lack' of a returned true value when a query is valid but has
			// no result set (as with all the other DB interfaces). It seems though that it's
			// 'fair' to say that if a query returns a false result (ie. no resource id) then the
			// SQL was valid but had no result set. If the query returns nothing but the rowcount
			// returns something then there's a problem. This may well be a false assumption though
			// ... needs checking under Windows itself.
			//
			if( preg_match("/^SELECT(.*?)(LIMIT ([0-9]+)[, ]*([0-9]+)*)?$/s", $query, $limits) )
			{
				$query = $limits[1];

				if( !empty($limits[2]) )
				{
					$row_offset = ( $limits[4] ) ? $limits[3] : "";
					$num_rows = ( $limits[4] ) ? $limits[4] : $limits[3];

					$query = "TOP " . ( $row_offset + $num_rows ) . $query;
				}

				$this->result = mssql_query("SELECT $query", $this->db_connect_id); 

				if( $this->result )
				{
					$this->limit_offset[$this->result] = ( !empty($row_offset) ) ? $row_offset : 0;

					if( $row_offset > 0 )
					{
						mssql_data_seek($this->result, $row_offset);
					}
				}
			}
			else if( eregi("^INSERT ", $query) )
			{
				if( mssql_query(str_replace("\'", "''", $query), $this->db_connect_id) )
				{
					$this->result = time() + microtime();

					$result_id = mssql_query("SELECT @@IDENTITY AS id, @@ROWCOUNT as affected", $this->db_connect_id);
					if( $result_id )
					{
						if( $row = mssql_fetch_array($result_id) )
						{
							$this->next_id[$this->db_connect_id] = $row['id'];	
							$this->affected_rows[$this->db_connect_id] = $row['affected'];
						}
					}
				}
			}
			else
			{
				if( mssql_query(str_replace("\'", "''", $query), $this->db_connect_id) )
				{
					$this->result = time() + microtime();

					$result_id = mssql_query("SELECT @@ROWCOUNT as affected", $this->db_connect_id);
					if( $result_id )
					{
						if( $row = mssql_fetch_array($result_id) )
						{
							$this->affected_rows[$this->db_connect_id] = $row['affected'];
						}
					}
				}
			}

			if( !$this->result )
			{
				if( $this->in_transaction )
				{
					mssql_query("ROLLBACK", $this->db_connect_id);
					$this->in_transaction = FALSE;
				}

				return false;
			}

			if( $transaction == END_TRANSACTION && $this->in_transaction )
			{
				mssql_query("COMMIT", $this->db_connect_id);
				$this->in_transaction = FALSE;
			}

			return $this->result;
		}
	}

	//
	// Other query methods
	//
	function sql_numrows($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->result;
		}

		if( $query_id )
		{
			return ( !empty($this->limit_offset[$query_id]) ) ? mssql_num_rows($query_id) - $this->limit_offset[$query_id] : @mssql_num_rows($query_id);
		}
		else
		{
			return false;
		}
	}

	function sql_numfields($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->result;
		}

		return ( $query_id ) ? mssql_num_fields($query_id) : false;
	}

	function sql_fieldname($offset, $query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->result;
		}

		return ( $query_id ) ? mssql_field_name($query_id, $offset) : false;
	}

	function sql_fieldtype($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->result;
		}

		return ( $query_id ) ? mssql_field_type($query_id, $offset) : false;
	}

	function sql_fetchrow($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->result;
		}

		if( $query_id )
		{
			empty($row);

			$row = mssql_fetch_array($query_id);

			while( list($key, $value) = @each($row) )
			{
				$row[$key] = stripslashes($value);
			}

			return $row;
		}
		else
		{
			return false;
		}
	}

	function sql_fetchrowset($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->result;
		}

		if( $query_id )
		{
			$i = 0;
			empty($rowset);

			while( $row = mssql_fetch_array($query_id))
			{
				while( list($key, $value) = @each($row) )
				{
					$rowset[$i][$key] = stripslashes($value);
				}
				$i++;
			}

			return $rowset;
		}
		else
		{
			return false;
		}
	}

	function sql_fetchfield($field, $row = -1, $query_id)
	{
		if( !$query_id )
		{
			$query_id = $this->result;
		}

		if( $query_id )
		{
			if( $row != -1 )
			{
				if( $this->limit_offset[$query_id] > 0 )
				{
					$result = ( !empty($this->limit_offset[$query_id]) ) ? mssql_result($this->result, ($this->limit_offset[$query_id] + $row), $field) : false;
				}
				else
				{
					$result = mssql_result($this->result, $row, $field);
				}
			}
			else
			{
				if( empty($this->row[$query_id]) )
				{
					$this->row[$query_id] = mssql_fetch_array($query_id);
					$result = stripslashes($this->row[$query_id][$field]);
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
		if( !$query_id )
		{
			$query_id = $this->result;
		}

		if( $query_id )
		{
			return ( !empty($this->limit_offset[$query_id]) ) ? mssql_data_seek($query_id, ($this->limit_offset[$query_id] + $rownum)) : mssql_data_seek($query_id, $rownum);
		}
		else
		{
			return false;
		}
	}

	function sql_nextid()
	{
		return ( $this->next_id[$this->db_connect_id] ) ? $this->next_id[$this->db_connect_id] : false;
	}

	function sql_affectedrows()
	{
		return ( $this->affected_rows[$this->db_connect_id] ) ? $this->affected_rows[$this->db_connect_id] : false;
	}

	function sql_freeresult($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->result;
		}

		return ( $query_id ) ? mssql_free_result($query_id) : false;
	}

	function sql_error($query_id = 0)
	{
		$result['message'] = @mssql_get_last_message();
		return $result;
	}

} // class sql_db

} // if ... define

?>