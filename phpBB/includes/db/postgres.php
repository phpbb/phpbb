<?php
/** 
*
* @package dbal_postgres
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined("SQL_LAYER"))
{

define("SQL_LAYER","postgresql");

/**
* @package dbal_postgres
* PostgreSQL Database Abstraction Layer
* Minimum Requirement is Version 7.3+
*/
class sql_db
{

	var $db_connect_id;
	var $query_result;
	var $in_transaction = 0;
	var $row = array();
	var $rowset = array();
	var $rownum = array();
	var $num_queries = 0;

	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true)
	{
		$this->connect_string = '';

		if ($sqluser)
		{
			$this->connect_string .= "user=$sqluser ";
		}

		if ($sqlpassword)
		{
			$this->connect_string .= "password=$sqlpassword ";
		}

		if ($sqlserver)
		{
			if (ereg(":", $sqlserver))
			{
				list($sqlserver, $sqlport) = split(":", $sqlserver);
				$this->connect_string .= "host=$sqlserver port=$sqlport ";
			}
			else
			{
				if ($sqlserver != "localhost")
				{
					$this->connect_string .= "host=$sqlserver ";
				}
			}
		}

		if ($database)
		{
			$this->dbname = $database;
			$this->connect_string .= "dbname=$database";
		}

		$this->persistency = $persistency;

		$this->db_connect_id = ($this->persistency) ? @pg_pconnect($this->connect_string) : @pg_connect($this->connect_string);

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	function sql_return_on_error($fail = false)
	{
		$this->return_on_error = $fail;
	}

	function sql_num_queries()
	{
		return $this->num_queries;
	}

	//
	// Other base methods
	//
	function sql_close()
	{
		if ($this->db_connect_id)
		{
			//
			// Commit any remaining transactions
			//
			if ($this->in_transaction)
			{
				@pg_exec($this->db_connect_id, "COMMIT");
			}

			if ($this->query_result)
			{
				@pg_freeresult($this->query_result);
			}

			return @pg_close($this->db_connect_id);
		}
		else
		{
			return false;
		}
	}

	//
	// Query method
	//
	function sql_query($query = "", $transaction = false)
	{
		//
		// Remove any pre-existing queries
		//
		unset($this->query_result);
		if ($query != "")
		{
			$this->num_queries++;

			$query = preg_replace("/LIMIT ([0-9]+),([ 0-9]+)/", "LIMIT \\2 OFFSET \\1", $query);

			if ($transaction == BEGIN_TRANSACTION && !$this->in_transaction)
			{
				$this->in_transaction = TRUE;

				if (!@pg_exec($this->db_connect_id, "BEGIN"))
				{
					return false;
				}
			}

			$this->query_result = @pg_exec($this->db_connect_id, $query);
			if ($this->query_result)
			{
				if ($transaction == END_TRANSACTION)
				{
					$this->in_transaction = FALSE;

					if (!@pg_exec($this->db_connect_id, "COMMIT"))
					{
						@pg_exec($this->db_connect_id, "ROLLBACK");
						return false;
					}
				}

				$this->last_query_text[$this->query_result] = $query;
				$this->rownum[$this->query_result] = 0;

				unset($this->row[$this->query_result]);
				unset($this->rowset[$this->query_result]);

				return $this->query_result;
			}
			else
			{
				if ($this->in_transaction)
				{
					@pg_exec($this->db_connect_id, "ROLLBACK");
				}
				$this->in_transaction = FALSE;

				return false;
			}
		}
		else
		{
			if ($transaction == END_TRANSACTION && $this->in_transaction)
			{
				$this->in_transaction = FALSE;

				if (!@pg_exec($this->db_connect_id, "COMMIT"))
				{
					@pg_exec($this->db_connect_id, "ROLLBACK");
					return false;
				}
			}

			return true;
		}
	}

	//
	// Other query methods
	//
	function sql_numrows($query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @pg_numrows($query_id) : false;
	}

	function sql_numfields($query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @pg_numfields($query_id) : false;
	}

	function sql_fieldname($offset, $query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @pg_fieldname($query_id, $offset) : false;
	}

	function sql_fieldtype($offset, $query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @pg_fieldtype($query_id, $offset) : false;
	}

	function sql_fetchrow($query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			$this->row = @pg_fetch_array($query_id, $this->rownum[$query_id]);

			if ($this->row)
			{
				$this->rownum[$query_id]++;
				return $this->row;
			}
		}

		return false;
	}

	function sql_fetchrowset($query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
			$this->rownum[$query_id] = 0;

			while ($this->rowset = @pg_fetch_array($query_id, $this->rownum[$query_id], PGSQL_ASSOC))
			{
				$result[] = $this->rowset;
				$this->rownum[$query_id]++;
			}

			return $result;
		}

		return false;
	}

	function sql_fetchfield($field, $row_offset=-1, $query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return false;
	}

	function sql_rowseek($offset, $query_id = 0)
	{

		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($offset > -1)
			{
				$this->rownum[$query_id] = $offset;
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	function sql_nextid()
	{
		$query_id = $this->query_result;

		if ($query_id && $this->last_query_text[$query_id] != "")
		{
			if (preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $this->last_query_text[$query_id], $tablename))
			{
				$query = "SELECT currval('" . $tablename[1] . "_id_seq') AS last_value";
				$temp_q_id =  @pg_exec($this->db_connect_id, $query);
				if (!$temp_q_id)
				{
					return false;
				}

				$temp_result = @pg_fetch_array($temp_q_id, 0, PGSQL_ASSOC);

				return ($temp_result) ? $temp_result['last_value'] : false;
			}
		}

		return false;
	}

	function sql_affectedrows($query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @pg_cmdtuples($query_id) : false;
	}

	function sql_freeresult($query_id = 0)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @pg_freeresult($query_id) : false;
	}

	function sql_error($sql = '')
	{
		if (!$this->return_on_error)
		{
			$this_page = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : $_ENV['PHP_SELF'];
			$this_page .= '&' . ((!empty($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : $_ENV['QUERY_STRING']);

			$message = '<u>SQL ERROR</u> [ ' . SQL_LAYER . ' ]<br /><br />' . @pg_errormessage() . '<br /><br /><u>CALLING PAGE</u><br /><br />'  . htmlspecialchars($this_page) . (($sql != '') ? '<br /><br /><u>SQL</u><br /><br />' . $sql : '') . '<br />';

			if ($this->transaction)
			{
				$this->sql_transaction('rollback');
			}
			
			trigger_error($message, E_USER_ERROR);
		}

		$result = array(
			'message'	=> @pg_errormessage(),
			'code'		=> ''
		);

		return $result;
	}

} // class ... db_sql

} // if ... defined

?>