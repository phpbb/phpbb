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

include_once(PHPBB_ROOT_PATH . 'includes/db/dbal.' . PHP_EXT);

/**
* Unified ODBC functions
* Unified ODBC functions support any database having ODBC driver, for example Adabas D, IBM DB2, iODBC, Solid, Sybase SQL Anywhere...
* Here we only support MSSQL Server 2000+ because of the provided schema
*
* @note number of bytes returned for returning data depends on odbc.defaultlrl php.ini setting.
* If it is limited to 4K for example only 4K of data is returned max, resulting in incomplete theme data for example.
* @note odbc.defaultbinmode may affect UTF8 characters
*
* @package dbal
*/
class phpbb_dbal_mssql_odbc extends phpbb_dbal
{
	/**
	* @var string Database type. No distinction between versions or used extensions.
	*/
	public $dbms_type = 'mssql';

	/**
	* @var array Database type map, column layout information
	*/
	public $dbms_type_map = array(
		'INT:'		=> '[int]',
		'BINT'		=> '[float]',
		'UINT'		=> '[int]',
		'UINT:'		=> '[int]',
		'TINT:'		=> '[int]',
		'USINT'		=> '[int]',
		'BOOL'		=> '[int]',
		'VCHAR'		=> '[varchar] (255)',
		'VCHAR:'	=> '[varchar] (%d)',
		'CHAR:'		=> '[char] (%d)',
		'XSTEXT'	=> '[varchar] (1000)',
		'STEXT'		=> '[varchar] (3000)',
		'TEXT'		=> '[varchar] (8000)',
		'MTEXT'		=> '[text]',
		'XSTEXT_UNI'=> '[varchar] (100)',
		'STEXT_UNI'	=> '[varchar] (255)',
		'TEXT_UNI'	=> '[varchar] (4000)',
		'MTEXT_UNI'	=> '[text]',
		'TIMESTAMP'	=> '[int]',
		'DECIMAL'	=> '[float]',
		'DECIMAL:'	=> '[float]',
		'PDECIMAL'	=> '[float]',
		'PDECIMAL:'	=> '[float]',
		'VCHAR_UNI'	=> '[varchar] (255)',
		'VCHAR_UNI:'=> '[varchar] (%d)',
		'VARBINARY'	=> '[varchar] (255)',
	);

	/**
	* Connect to server. See {@link phpbb_dbal::sql_connect() sql_connect()} for details.
	*/
	public function sql_connect($server, $user, $password, $database, $port = false, $persistency = false , $new_link = false)
	{
		$this->persistency = $persistency;
		$this->user = $user;
		$this->dbname = $database;
		$this->port = $port;

		$port_delimiter = (defined('PHP_OS') && substr(PHP_OS, 0, 3) === 'WIN') ? ',' : ':';
		$this->server = $server . (($port) ? $port_delimiter . $port : '');

		$max_size = @ini_get('odbc.defaultlrl');
		if (!empty($max_size))
		{
			$unit = strtolower(substr($max_size, -1, 1));
			$max_size = (int) $max_size;

			if ($unit == 'k')
			{
				$max_size = floor($max_size / 1024);
			}
			else if ($unit == 'g')
			{
				$max_size *= 1024;
			}
			else if (is_numeric($unit))
			{
				$max_size = floor((int) ($max_size . $unit) / 1048576);
			}
			$max_size = max(8, $max_size) . 'M';

			@ini_set('odbc.defaultlrl', $max_size);
		}

		$this->db_connect_id = ($this->persistency) ? @odbc_pconnect($this->server, $this->user, $password) : @odbc_connect($this->server, $this->user, $password);

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* Version information about used database. See {@link phpbb_dbal::sql_server_info() sql_server_info()} for details.
	*/
	public function sql_server_info($raw = false)
	{
		if (!phpbb::registered('acm') || ($this->sql_server_version = phpbb::$acm->get('#mssqlodbc_version')) === false)
		{
			$result_id = @odbc_exec($this->db_connect_id, "SELECT SERVERPROPERTY('productversion'), SERVERPROPERTY('productlevel'), SERVERPROPERTY('edition')");

			$row = false;
			if ($result_id)
			{
				$row = @odbc_fetch_array($result_id);
				@odbc_free_result($result_id);
			}

			$this->sql_server_version = ($row) ? trim(implode(' ', $row)) : 0;

			if (phpbb::registered('acm'))
			{
				phpbb::$acm->put('#mssqlodbc_version', $this->sql_server_version);
			}
		}

		if ($raw)
		{
			return $this->sql_server_version;
		}

		return ($this->sql_server_version) ? 'MSSQL (ODBC)<br />' . $this->sql_server_version : 'MSSQL (ODBC)';
	}

	/**
	* DB-specific base query method. See {@link phpbb_dbal::_sql_query() _sql_query()} for details.
	*/
	protected function _sql_query($query)
	{
		return @odbc_exec($this->db_connect_id, $query);
	}

	/**
	* Build LIMIT query and run it. See {@link phpbb_dbal::_sql_query_limit() _sql_query_limit()} for details.
	*/
	protected function _sql_query_limit($query, $total, $offset, $cache_ttl)
	{
		// Since TOP is only returning a set number of rows we won't need it if total is set to 0 (return all rows)
		if ($total)
		{
			// We need to grab the total number of rows + the offset number of rows to get the correct result
			if (strpos($query, 'SELECT DISTINCT') === 0)
			{
				$query = 'SELECT DISTINCT TOP ' . ($total + $offset) . ' ' . substr($query, 15);
			}
			else
			{
				$query = 'SELECT TOP ' . ($total + $offset) . ' ' . substr($query, 6);
			}
		}

		$result = $this->sql_query($query, $cache_ttl);

		// Seek by $offset rows
		if ($offset)
		{
			// We do not fetch the row for rownum == 0 because then the next resultset would be the second row
			for ($i = 0; $i < $offset; $i++)
			{
				if (!$this->sql_fetchrow($result))
				{
					return false;
				}
			}
		}

		return $result;
	}

	/**
	* Close sql connection. See {@link phpbb_dbal::_sql_close() _sql_close()} for details.
	*/
	protected function _sql_close()
	{
		return @odbc_close($this->db_connect_id);
	}

	/**
	* SQL Transaction. See {@link phpbb_dbal::_sql_transaction() _sql_transaction()} for details.
	*/
	protected function _sql_transaction($status)
	{
		switch ($status)
		{
			case 'begin':
				return @odbc_exec($this->db_connect_id, 'BEGIN TRANSACTION');
			break;

			case 'commit':
				return @odbc_exec($this->db_connect_id, 'COMMIT TRANSACTION');
			break;

			case 'rollback':
				return @odbc_exec($this->db_connect_id, 'ROLLBACK TRANSACTION');
			break;
		}

		return true;
	}

	/**
	* Return number of affected rows. See {@link phpbb_dbal::sql_affectedrows() sql_affectedrows()} for details.
	*/
	public function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @odbc_num_rows($this->query_result) : false;
	}

	/**
	* Get last inserted id after insert statement. See {@link phpbb_dbal::sql_nextid() sql_nextid()} for details.
	*/
	public function sql_nextid()
	{
		$result_id = @odbc_exec($this->db_connect_id, 'SELECT @@IDENTITY');

		if ($result_id)
		{
			if (@odbc_fetch_array($result_id))
			{
				$id = @odbc_result($result_id, 1);
				@odbc_free_result($result_id);
				return $id;
			}
			@odbc_free_result($result_id);
		}

		return false;
	}

	/**
	* Fetch current row. See {@link phpbb_dbal::_sql_fetchrow() _sql_fetchrow()} for details.
	* @note number of bytes returned depends on odbc.defaultlrl php.ini setting. If it is limited to 4K for example only 4K of data is returned max.
	*/
	protected function _sql_fetchrow($query_id)
	{
		return @odbc_fetch_array($query_id);
	}

	/**
	* Free query result. See {@link phpbb_dbal::_sql_freeresult() _sql_freeresult()} for details.
	*/
	protected function _sql_freeresult($query_id)
	{
		return @odbc_free_result($query_id);
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
				return 'DATALENGTH(' . $col . ')';
			break;
		}
	}

	/**
	* Handle data by using prepared statements. See {@link phpbb_dbal::sql_handle_data() sql_handle_data()} for details.
	public function sql_handle_data($type, $table, $data, $where = '')
	{
		if ($type === 'INSERT')
		{
			$stmt = odbc_prepare($this->db_connect_id, "INSERT INTO $table (". implode(', ', array_keys($data)) . ") VALUES (" . substr(str_repeat('?, ', sizeof($data)) ,0, -1) . ')');
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

			$stmt = odbc_prepare($this->db_connect_id, $query);
		}

		// get the stmt onto the top of the function arguments
		array_unshift($data, $stmt);

		call_user_func_array('odbc_execute', $data);
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
			'message'	=> @odbc_errormsg(),
			'code'		=> @odbc_error()
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

				$result = @odbc_exec($this->db_connect_id, $query);
				while ($void = @odbc_fetch_array($result))
				{
					// Take the time spent on parsing rows into account
				}
				@odbc_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>