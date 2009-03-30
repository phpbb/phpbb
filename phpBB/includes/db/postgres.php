<?php
/**
*
* @package dbal
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* PostgreSQL Database Abstraction Layer
* Minimum Requirement: 8.2+
* @package dbal
*/
class phpbb_dbal_postgres extends phpbb_dbal
{
	/**
	* @var string Database type. No distinction between versions or used extensions.
	*/
	public $dbms_type = 'postgres';

	/**
	* @var array Database type map, column layout information
	*/
	public $dbms_type_map = array(
		'INT:'		=> 'INT4',
		'BINT'		=> 'INT8',
		'UINT'		=> 'INT4', // unsigned
		'UINT:'		=> 'INT4', // unsigned
		'USINT'		=> 'INT2', // unsigned
		'BOOL'		=> 'INT2', // unsigned
		'TINT:'		=> 'INT2',
		'VCHAR'		=> 'varchar(255)',
		'VCHAR:'	=> 'varchar(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'varchar(1000)',
		'STEXT'		=> 'varchar(3000)',
		'TEXT'		=> 'varchar(8000)',
		'MTEXT'		=> 'TEXT',
		'XSTEXT_UNI'=> 'varchar(100)',
		'STEXT_UNI'	=> 'varchar(255)',
		'TEXT_UNI'	=> 'varchar(4000)',
		'MTEXT_UNI'	=> 'TEXT',
		'TIMESTAMP'	=> 'INT4', // unsigned
		'DECIMAL'	=> 'decimal(5,2)',
		'DECIMAL:'	=> 'decimal(%d,2)',
		'PDECIMAL'	=> 'decimal(6,3)',
		'PDECIMAL:'	=> 'decimal(%d,3)',
		'VCHAR_UNI'	=> 'varchar(255)',
		'VCHAR_UNI:'=> 'varchar(%d)',
		'VARBINARY'	=> 'bytea',
	);

	/**
	* @var string PostgreSQL schema (if supplied with $database -> database.schema)
	*/
	public $schema = '';

	/**
	* Connect to server. See {@link phpbb_dbal::sql_connect() sql_connect()} for details.
	*/
	public function sql_connect($server, $user, $password, $database, $port = false, $persistency = false , $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $user;
		$this->server = $server;
		$this->dbname = $database;
		$this->port = $port;

		$connect_string = '';

		if ($this->user)
		{
			$connect_string .= 'user=' . $this->user . ' ';
		}

		if ($password)
		{
			$connect_string .= 'password=' . $password . ' ';
		}

		if ($this->server)
		{
			if (strpos($this->server, ':') !== false)
			{
				list($this->server, $this->port) = explode(':', $this->server, 2);
			}

			if ($this->server !== 'localhost')
			{
				$connect_string .= 'host=' . $this->server . ' ';
			}

			if ($this->port)
			{
				$connect_string .= 'port=' . $this->port . ' ';
			}
		}

		$this->schema = '';

		if ($this->dbname)
		{
			if (strpos($this->dbname, '.') !== false)
			{
				list($this->dbname, $this->schema) = explode('.', $this->dbname, 2);
			}
			$connect_string .= 'dbname=' . $this->dbname;
		}

		$this->db_connect_id = ($this->persistency) ? @pg_pconnect($connect_string, ($new_link) ? PGSQL_CONNECT_FORCE_NEW : false) : @pg_connect($connect_string, ($new_link) ? PGSQL_CONNECT_FORCE_NEW : false);

		if (!$this->db_connect_id)
		{
			return $this->sql_error(htmlspecialchars_decode(phpbb::$last_notice['message']));
		}

		if ($this->schema)
		{
			@pg_query($this->db_connect_id, 'SET search_path TO ' . $this->schema);
		}

		return $this->db_connect_id;
	}

	/**
	* Version information about used database. See {@link phpbb_dbal::sql_server_info() sql_server_info()} for details.
	*/
	public function sql_server_info($raw = false)
	{
		if (!phpbb::registered('acm') || ($this->sql_server_version = phpbb::$acm->get('#pgsql_version')) === false)
		{
			$query_id = @pg_query($this->db_connect_id, 'SELECT VERSION() AS version');
			$row = @pg_fetch_assoc($query_id, null);
			@pg_free_result($query_id);

			$this->sql_server_version = (!empty($row['version'])) ? trim(substr($row['version'], 10)) : 0;

			if (phpbb::registered('acm'))
			{
				phpbb::$acm->put('#pgsql_version', $this->sql_server_version);
			}
		}

		return ($raw) ? $this->sql_server_version : 'PostgreSQL ' . $this->sql_server_version;
	}

	/**
	* DB-specific base query method. See {@link phpbb_dbal::_sql_query() _sql_query()} for details.
	*/
	protected function _sql_query($query)
	{
		return @pg_query($this->db_connect_id, $query);
	}

	/**
	* Build LIMIT query and run it. See {@link phpbb_dbal::_sql_query_limit() _sql_query_limit()} for details.
	*/
	protected function _sql_query_limit($query, $total, $offset, $cache_ttl)
	{
		// if $total is set to 0 we do not want to limit the number of rows
		if ($total == 0)
		{
			$total = 'ALL';
		}

		$query .= "\n LIMIT $total OFFSET $offset";
		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* Close sql connection. See {@link phpbb_dbal::_sql_close() _sql_close()} for details.
	*/
	protected function _sql_close()
	{
		return @pg_close($this->db_connect_id);
	}

	/**
	* SQL Transaction. See {@link phpbb_dbal::_sql_transaction() _sql_transaction()} for details.
	*/
	protected function _sql_transaction($status)
	{
		switch ($status)
		{
			case 'begin':
				return @pg_query($this->db_connect_id, 'BEGIN');
			break;

			case 'commit':
				return @pg_query($this->db_connect_id, 'COMMIT');
			break;

			case 'rollback':
				return @pg_query($this->db_connect_id, 'ROLLBACK');
			break;
		}

		return true;
	}

	/**
	* Return number of affected rows. See {@link phpbb_dbal::sql_affectedrows() sql_affectedrows()} for details.
	*/
	public function sql_affectedrows()
	{
		return ($this->query_result) ? @pg_affected_rows($this->query_result) : false;
	}

	/**
	* Get last inserted id after insert statement. See {@link phpbb_dbal::sql_nextid() sql_nextid()} for details.
	*/
	public function sql_nextid()
	{
		if (!$this->db_connect_id)
		{
			return false;
		}

		$query = "SELECT lastval() AS last_value";
		$temp_q_id = @pg_query($this->db_connect_id, $query);

		if (!$temp_q_id)
		{
			return false;
		}

		$temp_result = @pg_fetch_assoc($temp_q_id, NULL);
		@pg_free_result($query_id);

		return ($temp_result) ? $temp_result['last_value'] : false;
	}

	/**
	* Fetch current row. See {@link phpbb_dbal::_sql_fetchrow() _sql_fetchrow()} for details.
	*/
	protected function _sql_fetchrow($query_id)
	{
		return @pg_fetch_assoc($query_id, null);
	}

	/**
	* Free query result. See {@link phpbb_dbal::_sql_freeresult() _sql_freeresult()} for details.
	*/
	protected function _sql_freeresult($query_id)
	{
		return @pg_free_result($query_id);
	}

	/**
	* Correctly adjust LIKE expression for special characters. See {@link phpbb_dbal::_sql_like_expression() _sql_like_expression()} for details.
	*/
	protected function _sql_like_expression($expression)
	{
		return $expression;
	}

	/**
	* Escape string used in sql query. See {@link phpbb_dbal::sql_escape() sql_escape()} for details.
	* Note: Do not use for bytea values if we may use them at a later stage
	*/
	public function sql_escape($msg)
	{
		return @pg_escape_string($msg);
	}

	/**
	* Expose a DBMS specific function. See {@link phpbb_dbal::sql_function() sql_function()} for details.
	*/
	public function sql_function($type, $col)
	{
		switch ($type)
		{
			case 'length_varchar':
			case 'length_text':
				return 'LENGTH(' . $col . ')';
			break;
		}
	}

/*
	/**
	* Handle data by using prepared statements. See {@link phpbb_dbal::sql_handle_data() sql_handle_data()} for details.
	public function sql_handle_data($type, $table, $data, $where = '')
	{
		// for now, stmtname is an empty string, it might change to something more unique in the future
		if ($type === 'INSERT')
		{
			$stmt = pg_prepare($this->dbms_type, '', "INSERT INTO $table (". implode(', ', array_keys($data)) . ") VALUES ($" . implode(', $', range(1, sizeof($data))) . ')');
		}
		else
		{
			$query = "UPDATE $table SET ";

			$set = array();
			foreach (array_keys($data) as $key_id => $key)
			{
				$set[] = $key . ' = $' . $key_id;
			}
			$query .= implode(', ', $set);

			if ($where !== '')
			{
				$query .= $where;
			}

			$stmt = pg_prepare($this->db_connect_id, '', $query);
		}

		// add the stmtname to the top
		array_unshift($data, '');

		// add the connection resource
		array_unshift($data, $this->db_connect_id);

		call_user_func_array('pg_execute', $data);
	}
*/

	/**
	* Build DB-specific query bits. See {@link phpbb_dbal::_sql_custom_build() _sql_custom_build()} for details.
	*/
	protected function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	/**
	* return sql error array. See {@link phpbb_dbal::_sql_error() _sql_error()} for details.
	*/
	protected function _sql_error()
	{
		return array(
			'message'	=> (!$this->db_connect_id) ? @pg_last_error() : @pg_last_error($this->db_connect_id),
			'code'		=> ''
		);
	}

	/**
	* Run DB-specific code to build SQL Report to explain queries, show statistics and runtime information. See {@link phpbb_dbal::_sql_report() _sql_report()} for details.
	*/
	protected function _sql_report($mode, $query = '')
	{
		switch ($mode)
		{
			case 'start':

				$explain_query = $query;
				if (preg_match('/UPDATE ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}
				else if (preg_match('/DELETE FROM ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}

				if (preg_match('/^SELECT/', $explain_query))
				{
					$html_table = false;

					if ($result = @pg_query($this->db_connect_id, "EXPLAIN $explain_query"))
					{
						while ($row = @pg_fetch_assoc($result, NULL))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
					}
					@pg_free_result($result);

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @pg_query($this->db_connect_id, $query);
				while ($void = @pg_fetch_assoc($result, NULL))
				{
					// Take the time spent on parsing rows into account
				}
				@pg_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>