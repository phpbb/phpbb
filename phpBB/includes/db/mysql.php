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
* MySQL Database Abstraction Layer
* Compatible with:
* MySQL 4.1+
* MySQL 5.0+
* @package dbal
*/
class phpbb_dbal_mysql extends phpbb_dbal
{
	/**
	* @var string Database type. No distinction between versions or used extensions.
	*/
	public $dbms_type = 'mysql';

	/**
	* @var array Database type map, column layout information
	*/
	public $dbms_type_map = array(
		'INT:'		=> 'int(%d)',
		'BINT'		=> 'bigint(20)',
		'UINT'		=> 'mediumint(8) UNSIGNED',
		'UINT:'		=> 'int(%d) UNSIGNED',
		'TINT:'		=> 'tinyint(%d)',
		'USINT'		=> 'smallint(4) UNSIGNED',
		'BOOL'		=> 'tinyint(1) UNSIGNED',
		'VCHAR'		=> 'varchar(255)',
		'VCHAR:'	=> 'varchar(%d)',
		'CHAR:'		=> 'char(%d)',
		'XSTEXT'	=> 'text',
		'XSTEXT_UNI'=> 'varchar(100)',
		'STEXT'		=> 'text',
		'STEXT_UNI'	=> 'varchar(255)',
		'TEXT'		=> 'text',
		'TEXT_UNI'	=> 'text',
		'MTEXT'		=> 'mediumtext',
		'MTEXT_UNI'	=> 'mediumtext',
		'TIMESTAMP'	=> 'int(11) UNSIGNED',
		'DECIMAL'	=> 'decimal(5,2)',
		'DECIMAL:'	=> 'decimal(%d,2)',
		'PDECIMAL'	=> 'decimal(6,3)',
		'PDECIMAL:'	=> 'decimal(%d,3)',
		'VCHAR_UNI'	=> 'varchar(255)',
		'VCHAR_UNI:'=> 'varchar(%d)',
		'VARBINARY'	=> 'varbinary(255)',
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

		$this->db_connect_id = ($this->persistency) ? @mysql_pconnect($this->server, $this->user, $password, $new_link) : @mysql_connect($this->server, $this->user, $password, $new_link);

		if (!$this->db_connect_id || !$this->dbname)
		{
			return $this->sql_error('');
		}

		if (!@mysql_select_db($this->dbname, $this->db_connect_id))
		{
			return $this->sql_error('');
		}

		@mysql_query("SET NAMES 'utf8'", $this->db_connect_id);

		// enforce strict mode on databases that support it
		if (version_compare($this->sql_server_info(true), '5.0.2', '>='))
		{
			$result = @mysql_query('SELECT @@session.sql_mode AS sql_mode', $this->db_connect_id);
			$row = @mysql_fetch_assoc($result);
			@mysql_free_result($result);

			$modes = array_map('trim', explode(',', $row['sql_mode']));

			// TRADITIONAL includes STRICT_ALL_TABLES and STRICT_TRANS_TABLES
			if (!in_array('TRADITIONAL', $modes))
			{
				if (!in_array('STRICT_ALL_TABLES', $modes))
				{
					$modes[] = 'STRICT_ALL_TABLES';
				}

				if (!in_array('STRICT_TRANS_TABLES', $modes))
				{
					$modes[] = 'STRICT_TRANS_TABLES';
				}
			}

			$mode = implode(',', $modes);
			@mysql_query("SET SESSION sql_mode='{$mode}'", $this->db_connect_id);
		}

		return $this->db_connect_id;
	}

	/**
	* Version information about used database. See {@link phpbb_dbal::sql_server_info() sql_server_info()} for details.
	*/
	public function sql_server_info($raw = false)
	{
		if (!phpbb::registered('acm') || ($this->sql_server_version = phpbb::$acm->get('#mysql_version')) === false)
		{
			$result = @mysql_query('SELECT VERSION() AS version', $this->db_connect_id);
			$row = @mysql_fetch_assoc($result);
			@mysql_free_result($result);

			$this->sql_server_version = trim($row['version']);

			if (phpbb::registered('acm'))
			{
				phpbb::$acm->put('#mysql_version', $this->sql_server_version);
			}
		}

		return ($raw) ? $this->sql_server_version : 'MySQL ' . $this->sql_server_version;
	}

	/**
	* DB-specific base query method. See {@link phpbb_dbal::_sql_query() _sql_query()} for details.
	*/
	protected function _sql_query($query)
	{
		return @mysql_query($query, $this->db_connect_id);
	}

	/**
	* Build LIMIT query and run it. See {@link phpbb_dbal::_sql_query_limit() _sql_query_limit()} for details.
	*/
	protected function _sql_query_limit($query, $total, $offset, $cache_ttl)
	{
		// if $total is set to 0 we do not want to limit the number of rows
		if ($total == 0)
		{
			// MySQL 4.1+ no longer supports -1 in limit queries
			$total = '18446744073709551615';
		}

		$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);
		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* Close sql connection. See {@link phpbb_dbal::_sql_close() _sql_close()} for details.
	*/
	protected function _sql_close()
	{
		return @mysql_close($this->db_connect_id);
	}

	/**
	* SQL Transaction. See {@link phpbb_dbal::_sql_transaction() _sql_transaction()} for details.
	*/
	protected function _sql_transaction($status)
	{
		switch ($status)
		{
			case 'begin':
				return @mysql_query('BEGIN', $this->db_connect_id);
			break;

			case 'commit':
				return @mysql_query('COMMIT', $this->db_connect_id);
			break;

			case 'rollback':
				return @mysql_query('ROLLBACK', $this->db_connect_id);
			break;
		}

		return true;
	}

	/**
	* Return number of affected rows. See {@link phpbb_dbal::sql_affectedrows() sql_affectedrows()} for details.
	*/
	public function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @mysql_affected_rows($this->db_connect_id) : false;
	}

	/**
	* Get last inserted id after insert statement. See {@link phpbb_dbal::sql_nextid() sql_nextid()} for details.
	*/
	public function sql_nextid()
	{
		return ($this->db_connect_id) ? @mysql_insert_id($this->db_connect_id) : false;
	}

	/**
	* Fetch current row. See {@link phpbb_dbal::_sql_fetchrow() _sql_fetchrow()} for details.
	*/
	protected function _sql_fetchrow($query_id)
	{
		return @mysql_fetch_assoc($query_id);
	}

	/**
	* Free query result. See {@link phpbb_dbal::_sql_freeresult() _sql_freeresult()} for details.
	*/
	protected function _sql_freeresult($query_id)
	{
		return @mysql_free_result($query_id);
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
	*/
	public function sql_escape($msg)
	{
		if (!$this->db_connect_id)
		{
			return @mysql_real_escape_string($msg);
		}

		return @mysql_real_escape_string($msg, $this->db_connect_id);
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
	}
	*/

	/**
	* Build DB-specific query bits. See {@link phpbb_dbal::_sql_custom_build() _sql_custom_build()} for details.
	*/
	protected function _sql_custom_build($stage, $data)
	{
		switch ($stage)
		{
			case 'FROM':
				$data = '(' . $data . ')';
			break;
		}

		return $data;
	}

	/**
	* return sql error array. See {@link phpbb_dbal::_sql_error() _sql_error()} for details.
	*/
	protected function _sql_error()
	{
		if (!$this->db_connect_id)
		{
			return array(
				'message'	=> @mysql_error(),
				'code'		=> @mysql_errno()
			);
		}

		return array(
			'message'	=> @mysql_error($this->db_connect_id),
			'code'		=> @mysql_errno($this->db_connect_id)
		);
	}

	/**
	* Run DB-specific code to build SQL Report to explain queries, show statistics and runtime information. See {@link phpbb_dbal::_sql_report() _sql_report()} for details.
	*/
	protected function _sql_report($mode, $query = '')
	{
		static $test_prof;
		static $test_extend;

		// current detection method, might just switch to see the existance of INFORMATION_SCHEMA.PROFILING
		if ($test_prof === null)
		{
			$test_prof = $test_extend = false;
			if (version_compare($this->sql_server_info(true), '5.0.37', '>=') && version_compare($this->sql_server_info(true), '5.1', '<'))
			{
				$test_prof = true;
			}

			if (version_compare($this->sql_server_info(true), '4.1.1', '>='))
			{
				$test_extend = true;
			}
		}

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

					// begin profiling
					if ($test_prof)
					{
						@mysql_query('SET profiling = 1;', $this->db_connect_id);
					}

					if ($result = @mysql_query('EXPLAIN ' . (($test_extend) ? 'EXTENDED ' : '') . "$explain_query", $this->db_connect_id))
					{
						while ($row = @mysql_fetch_assoc($result))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
					}
					@mysql_free_result($result);

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}

					if ($test_extend)
					{
						$html_table = false;

						if ($result = @mysql_query('SHOW WARNINGS', $this->db_connect_id))
						{
							$this->html_hold .= '<br />';
							while ($row = @mysql_fetch_assoc($result))
							{
								$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
							}
						}
						@mysql_free_result($result);

						if ($html_table)
						{
							$this->html_hold .= '</table>';
						}
					}

					if ($test_prof)
					{
						$html_table = false;

						// get the last profile
						if ($result = @mysql_query('SHOW PROFILE ALL;', $this->db_connect_id))
						{
							$this->html_hold .= '<br />';
							while ($row = @mysql_fetch_assoc($result))
							{
								// make <unknown> HTML safe
								if (!empty($row['Source_function']))
								{
									$row['Source_function'] = str_replace(array('<', '>'), array('&lt;', '&gt;'), $row['Source_function']);
								}

								// remove unsupported features
								foreach ($row as $key => $val)
								{
									if ($val === null)
									{
										unset($row[$key]);
									}
								}
								$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
							}
						}
						@mysql_free_result($result);

						if ($html_table)
						{
							$this->html_hold .= '</table>';
						}

						@mysql_query('SET profiling = 0;', $this->db_connect_id);
					}
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @mysql_query($query, $this->db_connect_id);
				while ($void = @mysql_fetch_assoc($result))
				{
					// Take the time spent on parsing rows into account
				}
				@mysql_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}

?>