<?php
/**
*
* @package dbal
* @version $Id$
* @copyright (c) 2009 phpBB Group
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
* MSSQL Database Abstraction Layer
* Minimum Requirement: DB2 8.2.2+
* Minimum extension version: PECL ibm_db2 1.6.0+
* @package dbal
*/
class phpbb_dbal_db2 extends phpbb_dbal
{
	/**
	* @var string Database type. No distinction between versions or used extensions.
	*/
	public $dbms_type = 'db2';

	/**
	* @var array Database type map, column layout information
	*/
	public $dbms_type_map = array(
		'INT:'		=> 'integer',
		'BINT'		=> 'float',
		'UINT'		=> 'integer',
		'UINT:'		=> 'integer',
		'TINT:'		=> 'smallint',
		'USINT'		=> 'smallint',
		'BOOL'		=> 'smallint',
		'VCHAR'		=> 'varchar(255)',
		'VCHAR:'	=> 'varchar(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'clob(65K)',
		'STEXT'		=> 'varchar(3000)',
		'TEXT'		=> 'clob(65K)',
		'MTEXT'		=> 'clob(16M)',
		'XSTEXT_UNI'=> 'varchar(100)',
		'STEXT_UNI'	=> 'varchar(255)',
		'TEXT_UNI'	=> 'clob(65K)',
		'MTEXT_UNI'	=> 'clob(16M)',
		'TIMESTAMP'	=> 'integer',
		'DECIMAL'	=> 'float',
		'VCHAR_UNI'	=> 'varchar(255)',
		'VCHAR_UNI:'=> 'varchar(%d)',
		'VARBINARY'	=> 'varchar(255)',
	);

	/**
	* @var array Database features
	*/
	public $features = array(
		'multi_insert'			=> true,
		'count_distinct'		=> true,
		'multi_table_deletion'	=> true,
		'truncate'				=> false,
	);

	/**
	* Connect to server. See {@link phpbb_dbal::sql_connect() sql_connect()} for details.
	*/
	public function sql_connect($server, $user, $password, $database, $port = false, $persistency = false , $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $user;
		$this->server = $server . (($port) ? ':' . $port : '');
		$this->dbname = $database;
		$this->port = $port;

		$this->db_connect_id = ($this->persistency) ? @db2_pconnect($this->dbname, $this->user, $password, array('autocommit' => DB2_AUTOCOMMIT_ON, 'DB2_ATTR_CASE' => DB2_CASE_LOWER)) : @db2_connect($this->dbname, $this->user, $password, array('autocommit' => DB2_AUTOCOMMIT_ON, 'DB2_ATTR_CASE' => DB2_CASE_LOWER));

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database. See {@link phpbb_dbal::sql_server_info() sql_server_info()} for details.
	*/
	public function sql_server_info($raw = false)
	{
		if (!phpbb::registered('acm') || ($this->sql_server_version = phpbb::$acm->get('#db2_version')) === false)
		{
			$info = @db2_server_info($this->db_connect_id);

			$this->sql_server_info = is_object($info) ? $info->DBMS_VER : 0;

			if (phpbb::registered('acm'))
			{
				phpbb::$acm->put('#db2_version', $this->sql_server_version);
			}
		}

		return ($raw) ? $this->sql_server_version : 'IBM DB2 ' . $this->sql_server_version;
	}

	/**
	* DB-specific base query method. See {@link phpbb_dbal::_sql_query() _sql_query()} for details.
	*/
	protected function _sql_query($query)
	{
		$array = array();

		// Cope with queries larger than 32K
		if (strlen($query) > 32740)
		{
			if (preg_match('/^(INSERT INTO[^(]++)\\(([^()]+)\\) VALUES[^(]++\\((.*?)\\)$/s', $query, $regs))
			{
				if (strlen($regs[3]) > 32740)
				{
					preg_match_all('/\'(?:[^\']++|\'\')*+\'|[\d-.]+/', $regs[3], $vals, PREG_PATTERN_ORDER);

					$inserts = $vals[0];
					unset($vals);

					foreach ($inserts as $key => $value)
					{
						// check to see if this thing is greater than the max + 'x2
						if (!empty($value) && $value[0] === "'" && strlen($value) > 32742)
						{
							$inserts[$key] = '?';
							$array[] = str_replace("''", "'", substr($value, 1, -1));
						}
					}

					$query = $regs[1] . '(' . $regs[2] . ') VALUES (' . implode(', ', $inserts) . ')';
				}
			}
			else if (preg_match_all('/^(UPDATE ([\\w_]++)\\s+SET )([\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+)(?:,\\s*[\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+)\\s+(WHERE.*)$/s', $query, $data, PREG_SET_ORDER))
			{
				if (strlen($data[0][3]) > 32740)
				{
					$update = $data[0][1];
					$where = $data[0][4];
					preg_match_all('/(\\w++) = (\'(?:[^\']++|\'\')*+\'|\\d++)/', $data[0][3], $temp, PREG_SET_ORDER);
					unset($data);

					$cols = array();
					foreach ($temp as $value)
					{
						// check to see if this thing is greater than the max + 'x2
						if (!empty($value[2]) && $value[2][0] === "'" && strlen($value[2]) > 32742)
						{
							$array[] = str_replace("''", "'", substr($value[2], 1, -1));
							$cols[] = $value[1] . '=?';
						}
						else
						{
							$cols[] = $value[1] . '=' . $value[2];
						}
					}

					$query = $update . implode(', ', $cols) . ' ' . $where;
					unset($cols);
				}
			}
		}

		if (sizeof($array))
		{
			$result = @db2_prepare($this->db_connect_id, $query);

			if (!$result)
			{
				return false;
			}

			if (!@db2_execute($result, $array))
			{
				return false;
			}
		}
		else
		{
			$result = @db2_exec($this->db_connect_id, $query);
		}

		return $result;
	}

	/**
	* Build LIMIT query and run it. See {@link phpbb_dbal::_sql_query_limit() _sql_query_limit()} for details.
	*/
	protected function _sql_query_limit($query, $total, $offset, $cache_ttl)
	{
		if ($total && $offset == 0)
		{
			return $this->sql_query($query . ' fetch first ' . $total . ' rows only', $cache_ttl);
		}

		// Seek by $offset rows
		if ($offset)
		{
			$limit_sql = 'SELECT a2.*
				FROM (
					SELECT ROW_NUMBER() OVER() AS rownum, a1.*
						FROM (
							' . $query . '
						) a1
				) a2
			WHERE a2.rownum BETWEEN ' . ($offset + 1) . ' AND ' . ($offset + $total);

			return $this->sql_query($limit_sql, $cache_ttl);
		}

		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* Close sql connection. See {@link phpbb_dbal::_sql_close() _sql_close()} for details.
	*/
	protected function _sql_close()
	{
		return @db2_close($this->db_connect_id);
	}

	/**
	* SQL Transaction. See {@link phpbb_dbal::_sql_transaction() _sql_transaction()} for details.
	*/
	protected function _sql_transaction($status)
	{
		switch ($status)
		{
			case 'begin':
				return @db2_autocommit($this->db_connect_id, DB2_AUTOCOMMIT_OFF);
			break;

			case 'commit':
				$result = @db2_commit($this->db_connect_id);
				@db2_autocommit($this->db_connect_id, DB2_AUTOCOMMIT_ON);
				return $result;
			break;

			case 'rollback':
				$result = @db2_rollback($this->db_connect_id);
				@db2_autocommit($this->db_connect_id, DB2_AUTOCOMMIT_ON);
				return $result;
			break;
		}

		return true;
	}

	/**
	* Return number of affected rows. See {@link phpbb_dbal::sql_affectedrows() sql_affectedrows()} for details.
	*/
	public function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @db2_num_rows($this->db_connect_id) : false;
	}

	/**
	* Get last inserted id after insert statement. See {@link phpbb_dbal::sql_nextid() sql_nextid()} for details.
	*/
	public function sql_nextid()
	{
		if (function_exists('db2_last_insert_id'))
		{
			return @db2_last_insert_id($this->db_connect_id);
		}

		$result_id = @db2_exec($this->db_connect_id, 'VALUES IDENTITY_VAL_LOCAL()');

		if ($result_id)
		{
			if ($row = @db2_fetch_assoc($result_id))
			{
				@db2_free_result($result_id);
				return (int) $row[1];
			}
			@db2_free_result($result_id);
		}

		return false;
	}

	/**
	* Fetch current row. See {@link phpbb_dbal::_sql_fetchrow() _sql_fetchrow()} for details.
	*/
	protected function _sql_fetchrow($query_id)
	{
		return @db2_fetch_assoc($query_id);
	}

	/**
	* Free query result. See {@link phpbb_dbal::_sql_freeresult() _sql_freeresult()} for details.
	*/
	protected function _sql_freeresult($query_id)
	{
		return @db2_free_result($query_id);
	}

	/**
	* Correctly adjust LIKE expression for special characters. See {@link phpbb_dbal::_sql_like_expression() _sql_like_expression()} for details.
	*/
	protected function _sql_like_expression($expression)
	{
		return $expression . " ESCAPE '\\'";
	}

	/**
	* Escape string used in sql query. See {@link phpbb_dbal::sql_escape() sql_escape()} for details.
	*/
	public function sql_escape($msg)
	{
		return @db2_escape_string($msg);
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

	/**
	* Handle data by using prepared statements. See {@link phpbb_dbal::sql_handle_data() sql_handle_data()} for details.
	public function sql_handle_data($type, $table, $data, $where = '')
	{
		if ($type == 'INSERT')
		{
			$stmt = db2_prepare($this->db_connect_id, "INSERT INTO $table (". implode(', ', array_keys($data)) . ") VALUES (" . substr(str_repeat('?, ', sizeof($data)) ,0, -1) . ')');
		}
		else
		{
			$query = "UPDATE $table SET ";

			$set = array();
			foreach (array_keys($data) as $key)
			{
				$set[] = "$key = ?";
			}
			$query .= implode(', ', $set);

			if ($where !== '')
			{
				$query .= $where;
			}

			$stmt = db2_prepare($this->db_connect_id, $query);
		}

		// get the stmt onto the top of the function arguments
		array_unshift($data, $stmt);

		call_user_func_array('db2_execute', $data);
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
		$message = @db2_stmt_errormsg();
		$code = @db2_stmt_error();

		if (!$message && !$code)
		{
			$message = @db2_conn_errormsg();
			$code = @db2_conn_error();
		}

		$error = array(
			'message'	=> $message,
			'code'		=> $code,
		);

		return $error;
	}

	/**
	* Run DB-specific code to build SQL Report to explain queries, show statistics and runtime information. See {@link phpbb_dbal::_sql_report() _sql_report()} for details.
	*/
	protected function _sql_report($mode, $query = '')
	{
		switch ($mode)
		{
			case 'start':

				$html_table = false;
				@db2_exec($this->db_connect_id, 'DELETE FROM EXPLAIN_INSTANCE');
				@db2_exec($this->db_connect_id, 'EXPLAIN PLAN FOR ' . $query);

				// Get the data from the plan
				$sql = "SELECT O.Operator_ID, S2.Target_ID, O.Operator_Type, S.Object_Name, CAST(O.Total_Cost AS INTEGER) Cost
					FROM EXPLAIN_OPERATOR O
					LEFT OUTER JOIN EXPLAIN_STREAM S2 ON O.Operator_ID = S2.Source_ID
					LEFT OUTER JOIN EXPLAIN_STREAM S ON O.Operator_ID = S.Target_ID AND O.Explain_Time = S.Explain_Time AND S.Object_Name IS NOT NULL
					ORDER BY O.Explain_Time ASC, Operator_ID ASC";
				$query_id = @db2_exec($this->db_connect_id, $sql);

				if ($query_id)
				{
					while ($row = @db2_fetch_assoc($query_id))
					{
						$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
					}

					@db2_free_result($query_id);
				}

				if ($html_table)
				{
					$this->html_hold .= '</table>';
				}
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @db2_exec($this->db_connect_id, $query);
				while ($void = @db2_fetch_assoc($result, IBASE_TEXT))
				{
					// Take the time spent on parsing rows into account
				}
				@db2_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>