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

define("SQL_LAYER","mssql");

class sql_db
{

	var $db_connect_id;
	var $query_result;
	var $query_limit_offset;
	var $query_limit_numrows;
	var $query_limit_success;
	var $next_id;
	var $row;

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

		if($this->persistency)
		{
			$this->db_connect_id = @mssql_pconnect($this->server, $this->user, $this->password);
		}
		else
		{
			$this->db_connect_id = @mssql_connect($this->server, $this->user, $this->password);
		}

		if($this->db_connect_id)
		{
			if($this->dbname != "")
			{
				$dbselect = @mssql_select_db($this->dbname, $this->db_connect_id);
				if(!$dbselect)
				{
					@mssql_close($this->db_connect_id);
				}
				$this->db_connect_id = $dbselect;
			}
		}
		return $this->db_connect_id;
	}
	//
	// Other base methods
	//
	function sql_setdb($database)
	{
		$this->dbname = $database;
		$dbselect = @mssql_select_db($this->dbname);
		if(!$dbselect)
		{
			sql_close();
			$this->db_connect_id = $dbselect;
		}
		return $this->db_connect_id;
	}
	function sql_close()
	{
		if($this->db_connect_id)
		{
			if($this->query_result)
			{
				@mssql_free_result($this->query_result);
			}
			$result = @mssql_close($this->db_connect_id);
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

				mssql_query("SET ROWCOUNT ".($row_offset + $num_rows));
				$this->query_result = @mssql_query($query, $this->db_connect_id);
				mssql_query("SET ROWCOUNT 0");

				$this->query_limit_success[$this->query_result] = true;

				$this->query_limit_offset[$this->query_result] = -1;
				$this->query_limit_numrows[$this->query_result] = $num_rows;
				if($this->query_result && $row_offset>0)
				{
					$result = @mssql_data_seek($this->query_result, $row_offset);
					if(!$result)
					{
						$this->query_limit_success[$query_id] = false;
					}
					$this->query_limit_offset[$this->query_result] = $row_offset;
				}
			}
			else if(eregi("^INSERT ", $query))
			{
				$this->query_result = @mssql_query($query, $this->db_connect_id);

				$next_id_query = @mssql_query("SELECT @@IDENTITY AS this_id");
				$this->next_id[$this->query_result] = $this->sql_fetchfield("this_id", -1, $next_id_query);

				$this->query_limit_offset[$this->query_result] = -1;
				$this->query_limit_numrows[$this->query_result] = -1;
			}
			else 
			{
				$this->query_result = @mssql_query($query, $this->db_connect_id);

				$this->query_limit_offset[$this->query_result] = -1;
				$this->query_limit_numrows[$this->query_result] = -1;
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
				$result = @mssql_num_rows($query_id) - $this->query_limit_offset[$query_id];
			}
			else
			{ 
				$result = @mssql_num_rows($query_id);
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
				$this->row = @mssql_fetch_array($query_id);
				return $this->row;
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
			if($this->query_limit_success[$query_id])
			{
				empty($this->rowset);
				while($this->rowset = mssql_fetch_array($query_id))
				{
					$result[] = $this->rowset;
				}
			}
			else if($this->query_limit_numrows[$query_id] == -1)
			{
				empty($this->rowset);
				while($this->rowset = @mssql_fetch_array($this->query_result))
				{
					$result[] = $this->rowset;
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
