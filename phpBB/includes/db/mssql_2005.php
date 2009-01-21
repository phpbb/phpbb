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
* MSSQL Database Abstraction Layer
* Minimum Requirement is MSSQL 2005+
* @package dbal
*/
class phpbb_dbal_mssql_2005 extends phpbb_dbal
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
		$this->server = $server . (($port) ? ':' . $port : '');
		$this->dbname = $database;
		$this->port = $port;

		$conn_info = array();
		if ($this->user)
		{
			$conn_info['UID'] = $this->user;
		}

		if ($password)
		{
			$conn_info['PWD'] = $password;
		}

		$this->db_connect_id = @sqlsrv_connect($this->server, $conn_info);

		if (!$this->db_connect_id || !$this->dbname)
		{
			return $this->sql_error('');
		}

		if (!@sqlsrv_query($this->db_connect_id, 'USE ' . $this->dbname))
		{
			return $this->sql_error('');
		}

		return $this->db_connect_id;
	}

	/**
	* Version information about used database. See {@link phpbb_dbal::sql_server_info() sql_server_info()} for details.
	*/
	public function sql_server_info($raw = false)
	{
		if (!phpbb::registered('acm') || ($this->sql_server_version = phpbb::$acm->get('#mssql2005_version')) === false)
		{
			$server_info = @sqlsrv_server_info($this->db_connect_id);

			$this->sql_server_version = (!empty($server_info['SQLServerVersion'])) ? $server_info['SQLServerVersion'] : 0;

			if (phpbb::registered('acm'))
			{
				phpbb::$acm->put('#mssql2005_version', $this->sql_server_version);
			}
		}

		return ($raw) ? $this->sql_server_version : 'MSSQL ' . $this->sql_server_version;
	}

	/**
	* DB-specific base query method. See {@link phpbb_dbal::_sql_query() _sql_query()} for details.
	*/
	protected function _sql_query($query)
	{
		if (strpos($query, 'BEGIN') === 0 || strpos($query, 'COMMIT') === 0)
		{
			return true;
		}

		return @sqlsrv_query($this->db_connect_id, $query);
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
		return @sqlsrv_close($this->db_connect_id);
	}

	/**
	* SQL Transaction. See {@link phpbb_dbal::_sql_transaction() _sql_transaction()} for details.
	*/
	protected function _sql_transaction($status)
	{
		switch ($status)
		{
			case 'begin':
				return @sqlsrv_query($this->db_connect_id, 'BEGIN TRANSACTION');
			break;

			case 'commit':
				return @sqlsrv_query($this->db_connect_id, 'COMMIT TRANSACTION');
			break;

			case 'rollback':
				return @sqlsrv_query($this->db_connect_id, 'ROLLBACK TRANSACTION');
			break;
		}

		return true;
	}

	/**
	* Return number of affected rows. See {@link phpbb_dbal::sql_affectedrows() sql_affectedrows()} for details.
	*/
	public function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @sqlsrv_rows_affected($this->db_connect_id) : false;
	}

	/**
	* Get last inserted id after insert statement. See {@link phpbb_dbal::sql_nextid() sql_nextid()} for details.
	*/
	public function sql_nextid()
	{
		$result_id = @sqlsrv_query($this->db_connect_id, 'SELECT SCOPE_IDENTITY()');
		if ($result_id)
		{
			if ($row = @sqlsrv_fetch_array($result_id, SQLSRV_FETCH_ASSOC))
			{
				@sqlsrv_free_stmt($result_id);
				return $row['computed'];
			}
			@sqlsrv_free_stmt($result_id);
		}

		return false;
	}

	/**
	* Fetch current row. See {@link phpbb_dbal::_sql_fetchrow() _sql_fetchrow()} for details.
	*/
	protected function _sql_fetchrow($query_id)
	{
		$row = @sqlsrv_fetch_array($query_id, SQLSRV_FETCH_ASSOC);

		// I hope i am able to remove this later... hopefully only a PHP or MSSQL bug
		if ($row)
		{
			foreach ($row as $key => $value)
			{
				$row[$key] = ($value === ' ' || $value === NULL) ? '' : $value;
			}
		}

		return $row;
	}

	/**
	* Free query result. See {@link phpbb_dbal::_sql_freeresult() _sql_freeresult()} for details.
	*/
	protected function _sql_freeresult($query_id)
	{
		return @sqlsrv_free_stmt($query_id);
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
		return str_replace("'", "''", $msg);
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
		$message = $code = array();
		foreach (@sqlsrv_errors() as $error_array)
		{
			$message[] = $error_array['message'];
			$code[] = $error_array['code'];
		}

		$error = array(
			'message'	=> implode('<br />', $message),
			'code'		=> implode('<br />', $code),
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
			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @sqlsrv_query($this->db_connect_id, $query);
				while ($void = @sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC))
				{
					// Take the time spent on parsing rows into account
				}
				@sqlsrv_free_stmt($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>