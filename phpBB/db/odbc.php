<?php
/***************************************************************************
 *                                 mssql.php
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

if(!defined("SQL_LAYER"))
{

define("SQL_LAYER","odbc");

class sql_db
{

	var $db_connect_id;
	var $query_result;
	var $query_resultset;
	var $query_numrows;
	var $query_limit_offset;
	var $query_limit_numrows;
	var $query_limit_success;
	var $next_id;
	var $row;
	var $row_index;

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

		if($this->user || $this->password)
		{
//			$this->server = "DSN=".$this->server;
		}
		if($this->user)
		{
			$this->dsn .= ";UID=".$this->user;
		}
		if($this->password)
		{
			$this->dsn .= ";PWD=".$this->password;
		}
		
		if($this->persistency)
		{
			$this->db_connect_id = odbc_pconnect($this->server, "", "");
		}
		else
		{
			$this->db_connect_id = odbc_connect($this->server, "", "");
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
	function sql_setdb($database)
	{
		return false;
	}
	function sql_close()
	{
		if($this->db_connect_id)
		{
			if($this->query_result)
			{
				@odbc_free_result($this->query_result);
			}
			$result = @odbc_close($this->db_connect_id);
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
	function sql_query($query = "")
	{
		//
		// Remove any pre-existing queries
		//
		unset($this->query_result);
		unset($this->row);
		if($query != "")
		{
			//
			// Does query contain any LIMIT code?
			// If so pull out relevant start and num_results
			// This isn't terribly easy with MSSQL, whatever
			// you do will potentially impact performance
			// compared to an 'in-built' limit
			//
			if(eregi(" LIMIT ", $query))
			{
				eregi("^([a-zA-Z0-9 \*\,\'\"\+\?\.\(\)]+) LIMIT ([0-9]+)[, ]*([0-9]+)*", $query, $limits);
	
				$query = $limits[1];
				if($limits[3])
				{
					$row_offset = $limits[2];
					$num_rows = $limits[3];
				}
				else
				{
					$row_offset = 0;
					$num_rows = $limits[2];
				}

//				odbc_exec("SET ROWCOUNT ".($row_offset + $num_rows));
				$this->query_result = @odbc_query($this->db_connect_id, $query);
//				odbc_exec("SET ROWCOUNT 0");

				$this->query_limit_success[$this->query_result] = true;

				$this->query_limit_offset[$this->query_result] = -1;
				$this->query_limit_numrows[$this->query_result] = $num_rows;
				if($this->query_result && $row_offset>0)
				{
					$result = @odbc_data_seek($this->query_result, $row_offset);
					if(!$result)
					{
						$this->query_limit_success[$query_id] = false;
					}
					$this->query_limit_offset[$this->query_result] = $row_offset;
				}
			}
			else if(eregi("^INSERT ", $query))
			{
				$this->query_result = @odbc_exec($this->db_connect_id, $query);

//				$next_id_query = @odbc_exec("SELECT @@IDENTITY AS this_id");
				$this->next_id[$this->query_result] = $this->sql_fetchfield("this_id", -1, $next_id_query);

				$this->query_limit_offset[$this->query_result] = -1;
				$this->query_limit_numrows[$this->query_result] = -1;
			}
			else 
			{
				$this->query_result = @odbc_exec($this->db_connect_id, $query);

				$this->query_limit_offset[$this->query_result] = -1;
				$this->query_limit_numrows[$this->query_result] = -1;
			}

			if($this->query_result && !eregi("^INSERT ",$query))
			{
				$this->result_rowset[$this->query_result] = $this->sql_fetchrowset($this->query_result);
				$this->result_numrows[$this->query_result] = count($this->result_rowset[$this->query_result]);
				$this->row_index[$this->query_result] = 0;
			}

			return $this->query_result;
		}
		else
		{
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
			if($this->query_limit_offset[$query_id] > 0)
			{
				$result = @odbc_num_rows($query_id) - $this->query_limit_offset[$query_id];
			}
			else
			{ 
				$result = $this->result_numrows[$query_id];
			}
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
			$result = @mssql_num_fields($query_id);
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
			$result = @mssql_field_name($query_id, $offset);
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
			$result = @mssql_field_type($query_id, $offset);
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
	
			if($this->query_limit_offset[$query_id] > 0)
			{
				if($this->query_limit_success)
				{
					$this->row = @mssql_fetch_array($query_id);
					return $this->row;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return $this->result_rowset[$query_id][$this->row_index[$query_id]];
				$this->row_index[$query_id]++;
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
			if(!empty($this->result_rowset[$query_id])){
				return $this->result_rowset[$query_id];
			}

			for($i = 1; $i < @odbc_num_fields($query_id)+1; $i++)
			{
				$this->result_field_names[] = odbc_field_name($query_id, $i);
			}

			if($this->query_limit_success[$query_id])
			{
			}
			else if($this->query_limit_numrows[$query_id] == -1)
			{
				$i = 0;
				while(@odbc_fetch_row($query_id))
				{
					for($j = 1; $j < count($this->result_field_names)+1; $j++)
					{
						$result[$i][$this->result_field_names[$j-1]] = odbc_result($query_id, $j);
					}
					$i++;
				}
			}
			else
			{
				$result = false;
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_fetchfield($field, $row = -1, $query_id)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			if($row != -1)
			{
				if($this->query_limit_offset[$query_id] > 0)
				{
					if($this->query_limit_offset[$query_id] > 0 && $this->query_limit_success)
					{
						$result = @mssql_result($this->query_result, ($this->query_limit_offset[$query_id] + $row), $field);
					}
					else
					{
						return false;
					}
				}
				else
				{
					$result = @mssql_result($this->query_result, $row, $field);
				}
			}
			else
			{
				if(empty($this->row))
				{
					$this->row = @mssql_fetch_array($query_id);
					$result = $this->row[$field];
				}
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_rowseek($offset, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			if($this->query_limit_offset[$query_id] > 0)
			{
				$result = @mssql_data_seek($query_id, ($this->query_limit_offset[$query_id] + $rownum));
			}
			else
			{
				$result = @mssql_data_seek($query_id, $rownum);
			}
			return $result;
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
		if($query_id)
		{
			return $this->next_id[$query_id]+1;
		}
		else
		{
			return false;
		}
	}
	function sql_freeresult($query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			$result = @mssql_free_result($query_id);
			return $result;
		}
		else
		{
			return false;
		}
	}
	function sql_error($query_id = 0)
	{
		$result[message] = @mssql_get_last_message();
		return $result;
	}

} // class sql_db

} // if ... define

?>
