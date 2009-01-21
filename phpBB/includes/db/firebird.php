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
* Firebird/Interbase Database Abstraction Layer
* Minimum Requirement: 2.0+
* @package dbal
*/
class phpbb_dbal_firebird extends phpbb_dbal
{
	/**
	* @var string Database type. No distinction between versions or used extensions.
	*/
	public $dbms_type = 'firebird';

	/**
	* @var array Database type map, column layout information
	*/
	public $dbms_type_map = array(
		'INT:'		=> 'INTEGER',
		'BINT'		=> 'DOUBLE PRECISION',
		'UINT'		=> 'INTEGER',
		'UINT:'		=> 'INTEGER',
		'TINT:'		=> 'INTEGER',
		'USINT'		=> 'INTEGER',
		'BOOL'		=> 'INTEGER',
		'VCHAR'		=> 'VARCHAR(255) CHARACTER SET NONE',
		'VCHAR:'	=> 'VARCHAR(%d) CHARACTER SET NONE',
		'CHAR:'		=> 'CHAR(%d) CHARACTER SET NONE',
		'XSTEXT'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
		'STEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
		'TEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
		'MTEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
		'XSTEXT_UNI'=> 'VARCHAR(100) CHARACTER SET UTF8',
		'STEXT_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
		'TEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
		'MTEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
		'TIMESTAMP'	=> 'INTEGER',
		'DECIMAL'	=> 'DOUBLE PRECISION',
		'DECIMAL:'	=> 'DOUBLE PRECISION',
		'PDECIMAL'	=> 'DOUBLE PRECISION',
		'PDECIMAL:'	=> 'DOUBLE PRECISION',
		'VCHAR_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
		'VCHAR_UNI:'=> 'VARCHAR(%d) CHARACTER SET UTF8',
		'VARBINARY'	=> 'CHAR(255) CHARACTER SET NONE',
	);

	/**
	* @var string Last query executed. We need this for sql_nextid()
	*/
	var $last_query_text = '';

	/**
	* @var resource Attached service handle.
	*/
	var $service_handle = false;

	/**
	* @var array Database features
	*/
	public $features = array(
		'multi_insert'			=> false,
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
		$this->dbname = str_replace('\\', '/', $database);

		// There are three possibilities to connect to an interbase db
		if (!$this->server)
		{
			$use_database = $this->dbname;
		}
		else if (strpos($this->server, '//') === 0)
		{
			$use_database = $this->server . $this->dbname;
		}
		else
		{
			$use_database = $this->server . ':' . $this->dbname;
		}

		$this->db_connect_id = ($this->persistency) ? @ibase_pconnect($use_database, $this->user, $password, false, false, 3) : @ibase_connect($use_database, $this->user, $password, false, false, 3);

		if (!$this->db_connect_id)
		{
			return $this->sql_error('');
		}

		$this->service_handle = ($this->server) ? @ibase_service_attach($this->server, $this->user, $password) : false;

		return $this->db_connect_id;
	}

	/**
	* Version information about used database. See {@link phpbb_dbal::sql_server_info() sql_server_info()} for details.
	*/
	public function sql_server_info($raw = false)
	{
		if (!phpbb::registered('acm') || ($this->sql_server_version = phpbb::$acm->get('#firebird_version')) === false)
		{
			$version = false;

			if ($this->service_handle !== false)
			{
				$val = @ibase_server_info($this->service_handle, IBASE_SVC_SERVER_VERSION);
				preg_match('#V([\d.]+)#', $val, $version);
				$version = (!empty($version[1])) ? $version[1] : false;
			}

			$this->sql_server_version = (!$version) ? '2.0' : $version;

			if (phpbb::registered('acm'))
			{
				phpbb::$acm->put('#firebird_version', $this->sql_server_version);
			}
		}

		return ($raw) ? $this->sql_server_version : 'Firebird/Interbase ' . $this->sql_server_version;
	}

	/**
	* DB-specific base query method. See {@link phpbb_dbal::_sql_query() _sql_query()} for details.
	*/
	protected function _sql_query($query)
	{
		$this->last_query_text = $query;

		$array = array();

		// We overcome Firebird's 32767 char limit by binding vars
		if (strlen($query) > 32767)
		{
			if (preg_match('/^(INSERT INTO[^(]++)\\(([^()]+)\\) VALUES[^(]++\\((.*?)\\)$/s', $query, $regs))
			{
				if (strlen($regs[3]) > 32767)
				{
					preg_match_all('/\'(?:[^\']++|\'\')*+\'|[\d-.]+/', $regs[3], $vals, PREG_PATTERN_ORDER);

					$inserts = $vals[0];
					unset($vals);

					foreach ($inserts as $key => $value)
					{
						// check to see if this thing is greater than the max + 'x2
						if (!empty($value) && $value[0] === "'" && strlen($value) > 32769)
						{
							$inserts[$key] = '?';
							$array[] = str_replace("''", "'", substr($value, 1, -1));
						}
					}

					$query = $regs[1] . '(' . $regs[2] . ') VALUES (' . implode(', ', $inserts) . ')';
				}
			}
			else if (preg_match('/^(UPDATE ([\\w_]++)\\s+SET )([\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|\\d+)(?:,\\s*[\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+)\\s+(WHERE.*)$/s', $query, $data))
			{
				if (strlen($data[3]) > 32767)
				{
					$update = $data[1];
					$where = $data[4];
					preg_match_all('/(\\w++)\\s*=\\s*(\'(?:[^\']++|\'\')*+\'|[\d-.]++)/', $data[3], $temp, PREG_SET_ORDER);
					unset($data);

					$cols = array();
					foreach ($temp as $value)
					{
						// check to see if this thing is greater than the max + 'x2
						if (!empty($value[2]) && $value[2][0] === "'" && strlen($value[2]) > 32769)
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
			$p_query = @ibase_prepare($this->db_connect_id, $query);
			array_unshift($array, $p_query);
			$result = call_user_func_array('ibase_execute', $array);
			unset($array);
		}
		else
		{
			$result = @ibase_query($this->db_connect_id, $query);

			if ($result && !$this->transaction)
			{
				@ibase_commit_ret();
			}
		}

		return $result;
	}

	/**
	* Build LIMIT query and run it. See {@link phpbb_dbal::_sql_query_limit() _sql_query_limit()} for details.
	*/
	protected function _sql_query_limit($query, $total, $offset, $cache_ttl)
	{
		$query = 'SELECT FIRST ' . $total . ((!empty($offset)) ? ' SKIP ' . $offset : '') . substr($query, 6);
		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* Close sql connection. See {@link phpbb_dbal::_sql_close() _sql_close()} for details.
	*/
	protected function _sql_close()
	{
		if ($this->service_handle !== false)
		{
			@ibase_service_detach($this->service_handle);
		}

		return @ibase_close($this->db_connect_id);
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
				return @ibase_commit();
			break;

			case 'rollback':
				return @ibase_rollback();
			break;
		}

		return true;
	}

	/**
	* Return number of affected rows. See {@link phpbb_dbal::sql_affectedrows() sql_affectedrows()} for details.
	*/
	public function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @ibase_affected_rows($this->db_connect_id) : false;
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

		if (preg_match('#^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)#i', $this->last_query_text, $tablename))
		{
			$sql = 'SELECT GEN_ID(' . $tablename[1] . '_gen, 0) AS new_id FROM RDB$DATABASE';

			if (!($temp_q_id = @ibase_query($this->db_connect_id, $sql)))
			{
				return false;
			}

			$temp_result = @ibase_fetch_assoc($temp_q_id);
			@ibase_free_result($temp_q_id);

			return ($temp_result) ? $temp_result['NEW_ID'] : false;
		}

		return false;
	}

	/**
	* Fetch current row. See {@link phpbb_dbal::_sql_fetchrow() _sql_fetchrow()} for details.
	*/
	protected function _sql_fetchrow($query_id)
	{
		$cur_row = @ibase_fetch_object($query_id, IBASE_TEXT);

		if (!$cur_row)
		{
			return false;
		}

		foreach (get_object_vars($cur_row) as $key => $value)
		{
			$row[strtolower($key)] = (is_string($value)) ? trim(str_replace(array("\\0", "\\n"), array("\0", "\n"), $value)) : $value;
		}

		return (sizeof($row)) ? $row : false;
	}

	/**
	* Free query result. See {@link phpbb_dbal::_sql_freeresult() _sql_freeresult()} for details.
	*/
	protected function _sql_freeresult($query_id)
	{
		return @ibase_free_result($query_id);
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
			case 'length_text':
				return 'OCTET_LENGTH(' . $col . ')';
			break;
		}
	}

	/**
	* Handle data by using prepared statements. See {@link phpbb_dbal::sql_handle_data() sql_handle_data()} for details.
	public function sql_handle_data($type, $table, $data, $where = '')
	{
		if ($type == 'INSERT')
		{
			$stmt = ibase_prepare($this->db_connect_id, "INSERT INTO $table (". implode(', ', array_keys($data)) . ") VALUES (" . substr(str_repeat('?, ', sizeof($data)) ,0, -1) . ')');
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

			$stmt = ibase_prepare($this->db_connect_id, $query);
		}

		// get the stmt onto the top of the function arguments
		array_unshift($data, $stmt);

		call_user_func_array('ibase_execute', $data);
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
			'message'	=> @ibase_errmsg(),
			'code'		=> @ibase_errcode()
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
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @ibase_query($this->db_connect_id, $query);
				while ($void = @ibase_fetch_object($result, IBASE_TEXT))
				{
					// Take the time spent on parsing rows into account
				}
				@ibase_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>