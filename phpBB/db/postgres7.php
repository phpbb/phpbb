<?php
  /***************************************************************************
   *                               postgres7.php
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

define("SQL_LAYER","postgresql");

class sql_db
{

	var $db_connect_id;
	var $query_result;
	var $in_transaction = 0;
	var $row;
	var $rownum = array();
	var $num_queries = 0;

	//
	// Constructor
	//
	function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $persistency=true)
	{

		$this->connect_string = "";
		if($sqluser)
		{
			$this->connect_string .= "user=$sqluser ";
		}
		if($sqlpassword)
		{
			$this->connect_string .= "password=$sqlpassword ";
		}
		if($sqlserver)
		{
			if(ereg(":",$sqlserver))
			{
				list($sqlserver,$sqlport) = split(":",$sqlserver);
				$this->connect_string .= "host=$sqlserver port=$sqlport ";
			}
			else
			{
				if($sqlserver != "localhost")
				{
					$this->connect_string .= "host=$sqlserver ";
				}
			}
		}
		if($database)
		{
			$this->dbname = $database;
			$make_connect = $this->connect_string . "dbname=$database";
		}
		else
		{
			$make_connect = $this->connect_string;
		}

		$this->persistency = $persistency;
		if($this->persistency)
		{
			$this->db_connect_id = @pg_pconnect($make_connect);
		}
		else
		{
			$this->db_connect_id = @pg_connect($make_connect);
		}

		if($this->db_connect_id)
		{
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
		if($this->db_connect_id)
		{
			//
			// Commit any remaining transactions
			//
			if( $this->in_transaction )
			{
				@pg_exec($this->db_connect_id, "COMMIT");
			}

			if($this->query_result)
			{
				@pg_freeresult($this->query_result);
			}
			$result = @pg_close($this->db_connect_id);
			return $result;
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
		// Remove any pre-existing queries
		unset($this->query_result);
		if($query != "")
		{
			$this->num_queries++;

			$query = preg_replace("/LIMIT ([0-9]+),([ 0-9]+)/", "LIMIT \\2, \\1", $query);

			if($transaction == BEGIN_TRANSACTION)
			{
				$result = @pg_exec($this->db_connect_id, "BEGIN");
				if(!$result)
				{
					return false;
				}
			}

			$this->query_result = @pg_exec($this->db_connect_id, $query);
			if($this->query_result)
			{
				if($transaction == END_TRANSACTION)
				{
					$result = @pg_exec($this->db_connect_id, "COMMIT");
					if(!$result)
					{
						@pg_exec($this->db_connect_id, "ROLLBACK");
						return false;
					}
					$this->in_transaction = FALSE;
				}

				$this->last_query_text[$this->query_result] = $query;
				$this->rownum[$this->query_result] = 0;

				unset($this->row[$this->query_result]);
				unset($this->rowset[$this->query_result]);

				return $this->query_result;
			}
			else
			{
				if($this->in_transaction)
				{
					@pg_exec($this->db_connect_id, "ROLLBACK");
				}
				return false;
			}
		}
		else
		{
			if($transaction == END_TRANSACTION)
			{
				$result = @pg_exec($this->db_connect_id, "COMMIT");
				if(!$result)
				{
					@pg_exec($this->db_connect_id, "ROLLBACK");
					return false;
				}
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
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @pg_numrows($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_affectedrows($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @pg_cmdtuples($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_numfields($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @pg_numfields($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fieldname($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @pg_fieldname($query_id, $offset);
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
			$result = @pg_fieldtype($query_id, $offset);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fetchrow($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$this->row = @pg_fetch_array($query_id, $this->rownum[$query_id]);
			if($this->row)
			{
				$this->rownum[$query_id]++;
				return $this->row;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
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
			$this->rownum[$query_id] = 0;

			while($this->rowset = @pg_fetch_array($query_id, $this->rownum[$query_id], PGSQL_ASSOC))
			{
				$result[] = $this->rowset;
				$this->rownum[$query_id]++;
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fetchfield($field, $row_offset=-1, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			if($row_offset != -1)
			{
				$this->row = @pg_fetch_array($query_id, $row_offset, PGSQL_ASSOC);
			}
			else
			{
				if($this->rownum[$query_id])
				{
					$this->row = @pg_fetch_array($query_id, $this->rownum[$query_id]-1, PGSQL_ASSOC);
				}
				else
				{
					$this->row = @pg_fetch_array($query_id, $this->rownum[$query_id], PGSQL_ASSOC);
					if($this->row)
					{
						$this->rownum[$query_id]++;
					}
				}
			}
			return $this->row[$field];
		}
		else
		{
			return false;
		}
	}
	function sql_rowseek($offset, $query_id = 0){
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			if($offset>-1)
			{
				$this->rownum[$query_id] = $offset;
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	function sql_nextid($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id && $this->last_query_text[$query_id] != "")
		{
			if( eregi("^(INSERT{1}|^INSERT INTO{1})[[:space:]][\"]?([a-zA-Z0-9\_\-]+)[\"]?", $this->last_query_text[$query_id], $tablename))
			{
				$query = "SELECT last_value
					FROM ".$tablename[2]."_id_seq";
				$temp_q_id =  @pg_exec($this->db_connect_id, $query);
				$temp_result = @pg_fetch_array($temp_q_id, 0, PGSQL_ASSOC);
				if($temp_result)
				{
					return $temp_result['last_value'];
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	function sql_freeresult($query_id = 0){
		if(!$query_id){
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @pg_freeresult($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_error($query_id = 0)
	{
		if(!$query_id){
			$query_id = $this->query_result;
		}
		$result['message'] = @pg_errormessage($this->db_connect_id);
		$result['code'] = -1;
		return $result;
	}

} // class ... db_sql

} // if ... defined

?>