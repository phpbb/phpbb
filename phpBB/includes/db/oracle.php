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
* Oracle Database Abstraction Layer
* Minimum Requirement: 9.2+
* @package dbal
*/
class phpbb_dbal_oracle extends phpbb_dbal
{
	/**
	* @var string Database type. No distinction between versions or used extensions.
	*/
	public $dbms_type = 'oracle';

	/**
	* @var array Database type map, column layout information
	*/
	public $dbms_type_map = array(
		'INT:'		=> 'number(%d)',
		'BINT'		=> 'number(20)',
		'UINT'		=> 'number(8)',
		'UINT:'		=> 'number(%d)',
		'TINT:'		=> 'number(%d)',
		'USINT'		=> 'number(4)',
		'BOOL'		=> 'number(1)',
		'VCHAR'		=> 'varchar2(255)',
		'VCHAR:'	=> 'varchar2(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'varchar2(1000)',
		'STEXT'		=> 'varchar2(3000)',
		'TEXT'		=> 'clob',
		'MTEXT'		=> 'clob',
		'XSTEXT_UNI'=> 'varchar2(300)',
		'STEXT_UNI'	=> 'varchar2(765)',
		'TEXT_UNI'	=> 'clob',
		'MTEXT_UNI'	=> 'clob',
		'TIMESTAMP'	=> 'number(11)',
		'DECIMAL'	=> 'number(5, 2)',
		'DECIMAL:'	=> 'number(%d, 2)',
		'PDECIMAL'	=> 'number(6, 3)',
		'PDECIMAL:'	=> 'number(%d, 3)',
		'VCHAR_UNI'	=> 'varchar2(255)',
		'VCHAR_UNI:'=> array('varchar2(%d)', 'limit' => array('mult', 3, 765, 'clob')),
		'VARBINARY'	=> 'raw(255)',
	);

	/**
	* @var string Last query executed. We need this for sql_nextid()
	*/
	var $last_query_text = '';

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

		$connect = $database;

		// support for "easy connect naming"
		if ($server !== '' && $server !== '/')
		{
			if (substr($server, -1, 1) == '/')
			{
				$server == substr($sqlserver, 0, -1);
			}
			$connect = $server . (($port) ? ':' . $port : '') . '/' . $database;
		}

		$this->db_connect_id = ($new_link) ? @oci_new_connect($this->user, $password, $connect, 'AL32UTF8') : (($this->persistency) ? @oci_pconnect($this->user, $password, $connect, 'AL32UTF8') : @oci_connect($this->user, $password, $connect, 'AL32UTF8'));

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database. See {@link phpbb_dbal::sql_server_info() sql_server_info()} for details.
	*/
	public function sql_server_info($raw = false)
	{
		if (!phpbb::registered('acm') || ($this->sql_server_version = phpbb::$acm->get('#oracle_version')) === false)
		{
			$sql = "SELECT value
				FROM NLS_DATABASE_PARAMETERS
				WHERE PARAMETER = 'NLS_RDBMS_VERSION'";
			$result = @ociparse($this->db_connect_id, $sql);
			@ociexecute($result, OCI_DEFAULT);
			@ocicommit($this->db_connect_id);

			$row = array();
			@ocifetchinto($result, $row, OCI_ASSOC + OCI_RETURN_NULLS);
			@ocifreestatement($result);

			$this->sql_server_version = (isset($row['VALUE'])) ? trim($row['VALUE']) : 0;

			if (phpbb::registered('acm'))
			{
				phpbb::$acm->put('#oracle_version', $this->sql_server_version);
			}
		}

		return ($raw) ? $this->sql_server_version : @oci_server_version($this->db_connect_id);
	}

	/**
	* DB-specific base query method. See {@link phpbb_dbal::_sql_query() _sql_query()} for details.
	*/
	protected function _sql_query($query)
	{
		$this->last_query_text = $query;

		$in_transaction = false;
		if (!$this->transaction)
		{
			$this->sql_transaction('begin');
		}
		else
		{
			$in_transaction = true;
		}

		$array = array();

		// We overcome Oracle's 4000 char limit by binding vars
		if (strlen($query) > 4000)
		{
			if (preg_match('/^(INSERT INTO[^(]++)\\(([^()]+)\\) VALUES[^(]++\\((.*?)\\)$/s', $query, $regs))
			{
				if (strlen($regs[3]) > 4000)
				{
					$cols = explode(', ', $regs[2]);
					preg_match_all('/\'(?:[^\']++|\'\')*+\'|[\d-.]+/', $regs[3], $vals, PREG_PATTERN_ORDER);

					$inserts = $vals[0];
					unset($vals);

					foreach ($inserts as $key => $value)
					{
						// check to see if this thing is greater than the max + 'x2
						if (!empty($value) && $value[0] === "'" && strlen($value) > 4002)
						{
							$inserts[$key] = ':' . strtoupper($cols[$key]);
							$array[$inserts[$key]] = str_replace("''", "'", substr($value, 1, -1));
						}
					}

					$query = $regs[1] . '(' . $regs[2] . ') VALUES (' . implode(', ', $inserts) . ')';
				}
			}
			else if (preg_match_all('/^(UPDATE [\\w_]++\\s+SET )([\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+)(?:,\\s*[\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+)\\s+(WHERE.*)$/s', $query, $data, PREG_SET_ORDER))
			{
				if (strlen($data[0][2]) > 4000)
				{
					$update = $data[0][1];
					$where = $data[0][3];
					preg_match_all('/([\\w_]++)\\s*=\\s*(\'(?:[^\']++|\'\')*+\'|[\d-.]++)/', $data[0][2], $temp, PREG_SET_ORDER);
					unset($data);

					$cols = array();
					foreach ($temp as $value)
					{
						// check to see if this thing is greater than the max + 'x2
						if (!empty($value[2]) && $value[2][0] === "'" && strlen($value[2]) > 4002)
						{
							$cols[] = $value[1] . '=:' . strtoupper($value[1]);
							$array[$value[1]] = str_replace("''", "'", substr($value[2], 1, -1));
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

		switch (substr($query, 0, 6))
		{
			case 'DELETE':
				if (preg_match('/^(DELETE FROM [\w_]++ WHERE)((?:\s*(?:AND|OR)?\s*[\w_]+\s*(?:(?:=|<>)\s*(?>\'(?>[^\']++|\'\')*+\'|[\d-.]+)|(?:NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]+,? ?)*+\)))*+)$/', $query, $regs))
				{
					$query = $regs[1] . $this->_rewrite_where($regs[2]);
					unset($regs);
				}
			break;

			case 'UPDATE':
				if (preg_match('/^(UPDATE [\\w_]++\\s+SET [\\w_]+\s*=\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]++|:\w++)(?:, [\\w_]+\s*=\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]++|:\w++))*+\\s+WHERE)(.*)$/s',  $query, $regs))
				{
					$query = $regs[1] . $this->_rewrite_where($regs[2]);
					unset($regs);
				}
			break;

			case 'SELECT':
				$query = preg_replace_callback('/([\w_.]++)\s*(?:(=|<>)\s*(?>\'(?>[^\']++|\'\')*+\'|[\d-.]++|([\w_.]++))|(?:NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]++,? ?)*+\))/', array($this, '_rewrite_col_compare'), $query);
			break;
		}

		$result = @oci_parse($this->db_connect_id, $query);

		if (!$result)
		{
			return false;
		}

		foreach ($array as $key => $value)
		{
			@oci_bind_by_name($result, $key, $array[$key], -1);
		}

		$success = @oci_execute($result, OCI_DEFAULT);

		if (!$success)
		{
			return false;
		}

		if (!$in_transaction)
		{
			$this->sql_transaction('commit');
		}

		return $result;
	}

	/**
	* Build LIMIT query and run it. See {@link phpbb_dbal::_sql_query_limit() _sql_query_limit()} for details.
	*/
	protected function _sql_query_limit($query, $total, $offset, $cache_ttl)
	{
		$query = 'SELECT * FROM (SELECT /*+ FIRST_ROWS */ rownum AS xrownum, a.* FROM (' . $query . ') a WHERE rownum <= ' . ($offset + $total) . ') WHERE xrownum >= ' . $offset;
		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* Close sql connection. See {@link phpbb_dbal::_sql_close() _sql_close()} for details.
	*/
	protected function _sql_close()
	{
		return @oci_close($this->db_connect_id);
	}

	/**
	* SQL Transaction. See {@link phpbb_dbal::_sql_transaction() _sql_transaction()} for details.
	*/
	protected function _sql_transaction($status)
	{
		switch ($status)
		{
			case 'begin':
				return true;
			break;

			case 'commit':
				return @oci_commit($this->db_connect_id);
			break;

			case 'rollback':
				return @oci_rollback($this->db_connect_id);
			break;
		}

		return true;
	}

	/**
	* Return number of affected rows. See {@link phpbb_dbal::sql_affectedrows() sql_affectedrows()} for details.
	*/
	public function sql_affectedrows()
	{
		return ($this->query_result) ? @oci_num_rows($this->query_result) : false;
	}

	/**
	* Get last inserted id after insert statement. See {@link phpbb_dbal::sql_nextid() sql_nextid()} for details.
	*/
	public function sql_nextid()
	{
		if (!$this->query_result || !$this->last_query_text)
		{
			return false;
		}

		if (preg_match('#^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)#is', $this->last_query_text, $tablename))
		{
			$query = 'SELECT ' . $tablename[1] . '_seq.currval FROM DUAL';
			$stmt = @oci_parse($this->db_connect_id, $query);
			@oci_execute($stmt, OCI_DEFAULT);

			$temp_array = @oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS);
			@oci_free_statement($stmt);

			return (isset($temp_array['CURRVAL'])) ? $temp_array['CURRVAL'] : false;
		}

		return false;
	}

	/**
	* Fetch current row. See {@link phpbb_dbal::_sql_fetchrow() _sql_fetchrow()} for details.
	*/
	protected function _sql_fetchrow($query_id)
	{
		$row = @oci_fetch_array($query_id, OCI_ASSOC + OCI_RETURN_NULLS);

		if (!$row)
		{
			return false;
		}

		$result_row = array();
		foreach ($row as $key => $value)
		{
			// Oracle treats empty strings as null
			if (is_null($value))
			{
				$value = '';
			}

			// OCI->CLOB?
			if (is_object($value))
			{
				$value = $value->load();
			}

			$result_row[strtolower($key)] = $value;
		}

		return $result_row;
	}

	/**
	* Free query result. See {@link phpbb_dbal::_sql_freeresult() _sql_freeresult()} for details.
	*/
	protected function _sql_freeresult($query_id)
	{
		return @oci_free_statement($query_id);
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
		return str_replace(array("'", "\0"), array("''", ''), $msg);
	}

	/**
	* Expose a DBMS specific function. See {@link phpbb_dbal::sql_function() sql_function()} for details.
	*/
	public function sql_function($type, $col)
	{
		switch ($type)
		{
			case 'length_varchar':
				return 'LENGTH(' . $col . ')';
			break;

			case 'length_text':
				return 'dbms_lob.getlength(' . $col . ')';
			break;
		}
	}

	/**
	* Handle data by using prepared statements. See {@link phpbb_dbal::sql_handle_data() sql_handle_data()} for details.
	public function sql_handle_data($type, $table, $data, $where = '')
	{
		if ($type === 'INSERT')
		{
			$stmt = oci_parse($this->db_connect_id, "INSERT INTO $table (". implode(', ', array_keys($data)) . ") VALUES (:" . implode(', :', array_keys($data)) . ')');
		}
		else
		{
			$query = "UPDATE $table SET ";

			$set = array();
			foreach (array_keys($data) as $key)
			{
				$set[] = "$key = :$key";
			}
			$query .= implode(', ', $set);

			if ($where !== '')
			{
				$query .= $where;
			}

			$stmt = oci_parse($this->db_connect_id, $query);
		}

		foreach ($data as $column => $value)
		{
			oci_bind_by_name($stmt, ":$column", $data[$column], -1);
		}

		oci_execute($stmt);
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
		$error = @oci_error();
		$error = (empty($error)) ? @oci_error($this->query_result) : $error;
		$error = (empty($error)) ? @oci_error($this->db_connect_id) : $error;

		if (empty($error))
		{
			$error = array(
				'message'	=> '',
				'code'		=> '',
			);
		}

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

				// Grab a plan table, any will do
				$sql = "SELECT table_name
					FROM USER_TABLES
					WHERE table_name LIKE '%PLAN_TABLE%'";
				$stmt = @oci_parse($this->db_connect_id, $sql);
				@oci_execute($stmt);
				$result = array();

				if ($result = @oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS))
				{
					$table = $result['TABLE_NAME'];

					// This is the statement_id that will allow us to track the plan
					$statement_id = substr(md5($query), 0, 30);

					// Remove any stale plans
					$stmt2 = @oci_parse($this->db_connect_id, "DELETE FROM $table WHERE statement_id='$statement_id'");
					@oci_execute($stmt2);
					@oci_free_statement($stmt2);

					// Explain the plan
					$sql = "EXPLAIN PLAN
						SET STATEMENT_ID = '$statement_id'
						FOR $query";
					$stmt2 = @ociparse($this->db_connect_id, $sql);
					@oci_execute($stmt2);
					@oci_free_statement($stmt2);

					// Get the data from the plan
					$sql = "SELECT operation, options, object_name, object_type, cardinality, cost
						FROM plan_table
						START WITH id = 0 AND statement_id = '$statement_id'
						CONNECT BY PRIOR id = parent_id
							AND statement_id = '$statement_id'";
					$stmt2 = @oci_parse($this->db_connect_id, $sql);
					@oci_execute($stmt2);

					$row = array();
					while ($row = @oci_fetch_array($stmt2, OCI_ASSOC + OCI_RETURN_NULLS))
					{
						$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
					}
					@oci_free_statement($stmt2);

					// Remove the plan we just made, we delete them on request anyway
					$stmt2 = @oci_parse($this->db_connect_id, "DELETE FROM $table WHERE statement_id='$statement_id'");
					@oci_execute($stmt2);
					@oci_free_statement($stmt2);
				}

				@oci_free_statement($stmt);

				if ($html_table)
				{
					$this->html_hold .= '</table>';
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @oci_parse($this->db_connect_id, $query);
				$success = @oci_execute($result, OCI_DEFAULT);
				$row = array();

				while ($void = @oci_fetch_array($result, OCI_ASSOC + OCI_RETURN_NULLS))
				{
					// Take the time spent on parsing rows into account
				}
				@oci_free_statement($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}

	/**
	* Oracle specific code to handle the fact that it does not compare columns properly
	* @access private
	*/
	private function _rewrite_col_compare($args)
	{
		if (sizeof($args) == 4)
		{
			if ($args[2] == '=')
			{
				return '(' . $args[0] . ' OR (' . $args[1] . ' is NULL AND ' . $args[3] . ' is NULL))';
			}
			else if ($args[2] == '<>')
			{
				// really just a fancy way of saying foo <> bar or (foo is NULL XOR bar is NULL) but SQL has no XOR :P
				return '(' . $args[0] . ' OR ((' . $args[1] . ' is NULL AND ' . $args[3] . ' is NOT NULL) OR (' . $args[1] . ' is NOT NULL AND ' . $args[3] . ' is NULL)))';
			}
		}
		else
		{
			return $this->_rewrite_where($args[0]);
		}
	}

	/**
	* Oracle specific code to handle it's lack of sanity
	* @access private
	*/
	private function _rewrite_where($where_clause)
	{
		preg_match_all('/\s*(AND|OR)?\s*([\w_.]++)\s*(?:(=|<[=>]?|>=?)\s*((?>\'(?>[^\']++|\'\')*+\'|[\d-.]+))|((NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]+,? ?)*+\)))/', $where_clause, $result, PREG_SET_ORDER);
		$out = '';

		foreach ($result as $val)
		{
			if (!isset($val[5]))
			{
				if ($val[4] !== "''")
				{
					$out .= $val[0];
				}
				else
				{
					$out .= ' ' . $val[1] . ' ' . $val[2];
					if ($val[3] == '=')
					{
						$out .= ' is NULL';
					}
					else if ($val[3] == '<>')
					{
						$out .= ' is NOT NULL';
					}
				}
			}
			else
			{
				$in_clause = array();
				$sub_exp = substr($val[5], strpos($val[5], '(') + 1, -1);
				$extra = false;
				preg_match_all('/\'(?>[^\']++|\'\')*+\'|[\d-.]++/', $sub_exp, $sub_vals, PREG_PATTERN_ORDER);
				$i = 0;

				foreach ($sub_vals[0] as $sub_val)
				{
					// two things:
					// 1) This determines if an empty string was in the IN clausing, making us turn it into a NULL comparison
					// 2) This fixes the 1000 list limit that Oracle has (ORA-01795)
					if ($sub_val !== "''")
					{
						$in_clause[(int) $i++/1000][] = $sub_val;
					}
					else
					{
						$extra = true;
					}
				}

				if (!$extra && $i < 1000)
				{
					$out .= $val[0];
				}
				else
				{
					$out .= ' ' . $val[1] . '(';
					$in_array = array();

					// constuct each IN() clause
					foreach ($in_clause as $in_values)
					{
						$in_array[] = $val[2] . ' ' . (isset($val[6]) ? $val[6] : '') . 'IN(' . implode(', ', $in_values) . ')';
					}

					// Join the IN() clauses against a few ORs (IN is just a nicer OR anyway)
					$out .= implode(' OR ', $in_array);

					// handle the empty string case
					if ($extra)
					{
						$out .= ' OR ' . $val[2] . ' is ' . (isset($val[6]) ? $val[6] : '') . 'NULL';
					}
					$out .= ')';

					unset($in_array, $in_clause);
				}
			}
		}

		return $out;
	}
}

?>