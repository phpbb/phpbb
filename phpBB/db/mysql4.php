<?php
/***************************************************************************
 *                                 mysql4.php
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

define("SQL_LAYER","mysql4");

class sql_db
{

	var $db_connect_id;
	var $query_result;
	var $row = array();
	var $rowset = array();
	var $num_queries = 0;
	var $in_transaction = 0;

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

		$this->db_connect_id = ($this->persistency) ? mysql_pconnect($this->server, $this->user, $this->password) : mysql_connect($this->server, $this->user, $this->password);

		if( $this->db_connect_id )
		{
			if( $database != "" )
			{
				$this->dbname = $database;
				$dbselect = mysql_select_db($this->dbname);

				if( !$dbselect )
				{
					mysql_close($this->db_connect_id);
					$this->db_connect_id = $dbselect;
				}
			}

			return $this->db_connect_id;
		}
		else
		{
			return false;
		}
	}

	//
	// Other base methods
	//
	function sql_close()
	{
		if( $this->db_connect_id )
		{
			//
			// Commit any remaining transactions
			//
			if( $this->in_transaction )
			{
				mysql_query("COMMIT", $this->db_connect_id);
			}

			return mysql_close($this->db_connect_id);
		}
		else
		{
			return false;
		}
	}

	//
	// Base query method
	//
	function sql_query($query = "", $transaction = FALSE)
	{
		//
		// Remove any pre-existing queries
		//
		unset($this->query_result);

		if( $query != "" )
		{
			$this->num_queries++;
			if( $transaction == BEGIN_TRANSACTION && !$this->in_transaction )
			{
				$result = mysql_query("BEGIN", $this->db_connect_id);
				if(!$result)
				{
					return false;
				}
				$this->in_transaction = TRUE;
			}

			$this->query_result = mysql_query($query, $this->db_connect_id);
		}
		else
		{
			if( $transaction == END_TRANSACTION && $this->in_transaction )
			{
				$result = mysql_query("COMMIT", $this->db_connect_id);
			}
		}

		if( $this->query_result )
		{
			unset($this->row[$this->query_result]);
			unset($this->rowset[$this->query_result]);

			if( $transaction == END_TRANSACTION && $this->in_transaction )
			{
				$this->in_transaction = FALSE;

				if ( !mysql_query("COMMIT", $this->db_connect_id) )
				{
					mysql_query("ROLLBACK", $this->db_connect_id);
					return false;
				}
			}
			
			return $this->query_result;
		}
		else
		{
			if( $this->in_transaction )
			{
				mysql_query("ROLLBACK", $this->db_connect_id);
				$this->in_transaction = FALSE;
			}
			return false;
		}
	}

	//
	// Other query methods
	//
	function sql_numrows($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? mysql_num_rows($query_id) : false;
	}

	function sql_affectedrows()
	{
		return ( $this->db_connect_id ) ? mysql_affected_rows($this->db_connect_id) : false;
	}

	function sql_numfields($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? mysql_num_fields($query_id) : false;
	}

	function sql_fieldname($offset, $query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? mysql_field_name($query_id, $offset) : false;
	}

	function sql_fieldtype($offset, $query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? mysql_field_type($query_id, $offset) : false;
	}

	function sql_fetchrow($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		if( $query_id )
		{
			$this->row[$query_id] = mysql_fetch_array($query_id, MYSQL_ASSOC);
			return $this->row[$query_id];
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
			$query_id = $this->query_result;
		}

		if( $query_id )
		{
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);

			while($this->rowset[$query_id] = mysql_fetch_array($query_id, MYSQL_ASSOC))
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
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		if( $query_id )
		{
			if( $rownum > -1 )
			{
				$result = mysql_result($query_id, $rownum, $field);
			}
			else
			{
				if( empty($this->row[$query_id]) && empty($this->rowset[$query_id]) )
				{
					if( $this->sql_fetchrow() )
					{
						$result = $this->row[$query_id][$field];
					}
				}
				else
				{
					if( $this->rowset[$query_id] )
					{
						$result = $this->rowset[$query_id][$field];
					}
					else if( $this->row[$query_id] )
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
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		return ( $query_id ) ? mysql_data_seek($query_id, $rownum) : false;
	}

	function sql_nextid()
	{
		return ( $this->db_connect_id ) ? mysql_insert_id($this->db_connect_id) : false;
	}

	function sql_freeresult($query_id = 0)
	{
		if( !$query_id )
		{
			$query_id = $this->query_result;
		}

		if ( $query_id )
		{
			unset($this->row[$query_id]);
			unset($this->rowset[$query_id]);

			mysql_free_result($query_id);

			return true;
		}
		else
		{
			return false;
		}
	}

	function sql_error()
	{
		$result['message'] = mysql_error($this->db_connect_id);
		$result['code'] = mysql_errno($this->db_connect_id);

		return $result;
	}

} // class sql_db

} // if ... define

?>
