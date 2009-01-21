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
* MSSQL Database Abstraction Layer
* Minimum Requirement is MSSQL 2000+
* @package dbal
*/
class phpbb_dbal_mssql extends phpbb_dbal
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
		$this->server = $server . (($this->port) ? $port_delimiter . $this->port : '');

		@ini_set('mssql.charset', 'UTF-8');
		@ini_set('mssql.textlimit', 2147483647);
		@ini_set('mssql.textsize', 2147483647);

		$this->db_connect_id = ($this->persistency) ? @mssql_pconnect($this->server, $this->user, $password, $new_link) : @mssql_connect($this->server, $this->user, $password, $new_link);

		if (!$this->db_connect_id || !$this->dbname)
		{
			return $this->sql_error(phpbb::$last_notice['message']);
		}

		if (!@mssql_select_db($this->dbname, $this->db_connect_id))
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
		if (!phpbb::registered('acm') || ($this->sql_server_version = phpbb::$acm->get('#mssql_version')) === false)
		{
			$result_id = @mssql_query("SELECT SERVERPROPERTY('productversion'), SERVERPROPERTY('productlevel'), SERVERPROPERTY('edition')", $this->db_connect_id);

			$row = false;
			if ($result_id)
			{
				$row = @mssql_fetch_assoc($result_id);
				@mssql_free_result($result_id);
			}

			$this->sql_server_version = ($row) ? trim(implode(' ', $row)) : 0;

			if (phpbb::registered('acm'))
			{
				phpbb::$acm->put('#mssql_version', $this->sql_server_version);
			}
		}

		if ($raw)
		{
			return $this->sql_server_version;
		}

		return ($this->sql_server_version) ? 'MSSQL<br />' . $this->sql_server_version : 'MSSQL';
	}

	/**
	* DB-specific base query method. See {@link phpbb_dbal::_sql_query() _sql_query()} for details.
	*/
	protected function _sql_query($query)
	{
		return @mssql_query($query, $this->db_connect_id);
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
			@mssql_data_seek($result, $offset);
		}

		return $result;
	}

	/**
	* Close sql connection. See {@link phpbb_dbal::_sql_close() _sql_close()} for details.
	*/
	protected function _sql_close()
	{
		return @mssql_close($this->db_connect_id);
	}

	/**
	* SQL Transaction. See {@link phpbb_dbal::_sql_transaction() _sql_transaction()} for details.
	*/
	protected function _sql_transaction($status)
	{
		switch ($status)
		{
			case 'begin':
				return @mssql_query('BEGIN TRANSACTION', $this->db_connect_id);
			break;

			case 'commit':
				return @mssql_query('COMMIT TRANSACTION', $this->db_connect_id);
			break;

			case 'rollback':
				return @mssql_query('ROLLBACK TRANSACTION', $this->db_connect_id);
			break;
		}

		return true;
	}

	/**
	* Return number of affected rows. See {@link phpbb_dbal::sql_affectedrows() sql_affectedrows()} for details.
	*/
	public function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @mssql_rows_affected($this->db_connect_id) : false;
	}

	/**
	* Get last inserted id after insert statement. See {@link phpbb_dbal::sql_nextid() sql_nextid()} for details.
	*/
	public function sql_nextid()
	{
		$result_id = @mssql_query('SELECT SCOPE_IDENTITY()', $this->db_connect_id);

		if ($result_id)
		{
			if ($row = @mssql_fetch_assoc($result_id))
			{
				@mssql_free_result($result_id);
				return $row['computed'];
			}
			@mssql_free_result($result_id);
		}

		return false;
	}

	/**
	* Fetch current row. See {@link phpbb_dbal::_sql_fetchrow() _sql_fetchrow()} for details.
	*/
	protected function _sql_fetchrow($query_id)
	{
		$row = @mssql_fetch_assoc($query_id);

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
		return @mssql_free_result($query_id);
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
		$error = array(
			'message'	=> @mssql_get_last_message(),
			'code'		=> ''
		);

		// Get error code number
		$result_id = @mssql_query('SELECT @@ERROR as code', $this->db_connect_id);
		if ($result_id)
		{
			$row = @mssql_fetch_assoc($result_id);
			$error['code'] = $row['code'];
			@mssql_free_result($result_id);
		}

		// Get full error message if possible
		$sql = 'SELECT CAST(description as varchar(255)) as message
			FROM master.dbo.sysmessages
			WHERE error = ' . $error['code'];
		$result_id = @mssql_query($sql);

		if ($result_id)
		{
			$row = @mssql_fetch_assoc($result_id);
			if (!empty($row['message']))
			{
				$error['message'] .= '<br />' . $row['message'];
			}
			@mssql_free_result($result_id);
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
				@mssql_query('SET SHOWPLAN_TEXT ON;', $this->db_connect_id);
				if ($result = @mssql_query($query, $this->db_connect_id))
				{
					@mssql_next_result($result);
					while ($row = @mssql_fetch_row($result))
					{
						$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
					}
				}
				@mssql_query('SET SHOWPLAN_TEXT OFF;', $this->db_connect_id);
				@mssql_free_result($result);

				if ($html_table)
				{
					$this->html_hold .= '</table>';
				}
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @mssql_query($query, $this->db_connect_id);
				while ($void = @mssql_fetch_assoc($result))
				{
					// Take the time spent on parsing rows into account
				}
				@mssql_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>