<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* This is the MS SQL Server Native database abstraction layer.
* PHP mssql native driver required.
* @author Chris Pucci
*
*/

namespace phpbb\db\driver;

class mssqlnative extends \phpbb\db\driver\mssql_base
{
	var $m_insert_id = null;
	var $query_options = array();
	var $connect_error = '';

	/** @var string|false Last error result or false if no last error set */
	private $last_error_result = false;

	/**
	* {@inheritDoc}
	*/
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		// Test for driver support, to avoid suppressed fatal error
		if (!function_exists('sqlsrv_connect'))
		{
			$this->connect_error = 'Native MS SQL Server driver for PHP is missing or needs to be updated. Version 1.1 or later is required to install phpBB. You can download the driver from: http://www.microsoft.com/sqlserver/2005/en/us/PHP-Driver.aspx';
			return $this->sql_error('');
		}

		//set up connection variables
		$this->persistency = $persistency;
		$this->user = $sqluser;
		$this->dbname = $database;
		$port_delimiter = (defined('PHP_OS') && substr(PHP_OS, 0, 3) === 'WIN') ? ',' : ':';
		$this->server = $sqlserver . (($port) ? $port_delimiter . $port : '');

		//connect to database
		$this->db_connect_id = sqlsrv_connect($this->server, array(
			'Database' => $this->dbname,
			'UID' => $this->user,
			'PWD' => $sqlpassword,
			'CharacterSet' => 'UTF-8'
		));

		return ($this->db_connect_id) ? $this->db_connect_id : $this->sql_error('');
	}

	/**
	* {@inheritDoc}
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		global $cache;

		if (!$use_cache || empty($cache) || ($this->sql_server_version = $cache->get('mssql_version')) === false)
		{
			$arr_server_info = sqlsrv_server_info($this->db_connect_id);
			$this->sql_server_version = $arr_server_info['SQLServerVersion'];

			if (!empty($cache) && $use_cache)
			{
				$cache->put('mssql_version', $this->sql_server_version);
			}
		}

		if ($raw)
		{
			return $this->sql_server_version;
		}

		return ($this->sql_server_version) ? 'MSSQL<br />' . $this->sql_server_version : 'MSSQL';
	}

	/**
	* {@inheritDoc}
	*/
	function sql_buffer_nested_transactions()
	{
		return true;
	}

	/**
	* {@inheritDoc}
	*/
	protected function _sql_transaction(string $status = 'begin'): bool
	{
		switch ($status)
		{
			case 'begin':
				return sqlsrv_begin_transaction($this->db_connect_id);

			case 'commit':
				return sqlsrv_commit($this->db_connect_id);

			case 'rollback':
				return sqlsrv_rollback($this->db_connect_id);
		}
		return true;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_query($query = '', $cache_ttl = 0)
	{
		if ($query != '')
		{
			global $cache;

			if ($this->debug_sql_explain)
			{
				$this->sql_report('start', $query);
			}
			else if ($this->debug_load_time)
			{
				$this->curtime = microtime(true);
			}

			$this->last_query_text = $query;
			$this->query_result = ($cache && $cache_ttl) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if ($this->query_result === false)
			{
				try
				{
					$this->query_result = @sqlsrv_query($this->db_connect_id, $query, array(), $this->query_options);
				}
				catch (\Error $e)
				{
					// Do nothing as SQL driver will report the error
				}

				if ($this->query_result === false)
				{
					$this->sql_error($query);
				}

				// Reset options for the next query
				$this->query_options = [];

				if ($this->debug_sql_explain)
				{
					$this->sql_report('stop', $query);
				}
				else if ($this->debug_load_time)
				{
					$this->sql_time += microtime(true) - $this->curtime;
				}

				if (!$this->query_result)
				{
					return false;
				}

				$safe_query_id = $this->clean_query_id($this->query_result);

				if ($cache && $cache_ttl)
				{
					$this->open_queries[$safe_query_id] = $this->query_result;
					$this->query_result = $cache->sql_save($this, $query, $this->query_result, $cache_ttl);
				}
				else if (strpos($query, 'SELECT') === 0)
				{
					$this->open_queries[$safe_query_id] = $this->query_result;
				}
			}
			else if ($this->debug_sql_explain)
			{
				$this->sql_report('fromcache', $query);
			}
		}
		else
		{
			return false;
		}
		return $this->query_result;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _sql_query_limit(string $query, int $total, int $offset = 0, int $cache_ttl = 0)
	{
		$this->query_result = false;

		// total == 0 means all results - not zero results
		if ($offset == 0 && $total !== 0)
		{
			if (strpos($query, "SELECT") === false)
			{
				$query = "TOP {$total} " . $query;
			}
			else
			{
				$query = preg_replace('/SELECT(\s*DISTINCT)?/Dsi', 'SELECT$1 TOP '.$total, $query);
			}
		}
		else if ($offset > 0)
		{
			$query = preg_replace('/SELECT(\s*DISTINCT)?/Dsi', 'SELECT$1 TOP(10000000) ', $query);
			$query = 'SELECT *
					FROM (SELECT sub2.*, ROW_NUMBER() OVER(ORDER BY sub2.line2) AS line3
					FROM (SELECT 1 AS line2, sub1.* FROM (' . $query . ') AS sub1) as sub2) AS sub3';

			if ($total > 0)
			{
				$query .= ' WHERE line3 BETWEEN ' . ($offset+1) . ' AND ' . ($offset + $total);
			}
			else
			{
				$query .= ' WHERE line3 > ' . $offset;
			}
		}

		$result = $this->sql_query($query, $cache_ttl);

		return $result;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @sqlsrv_rows_affected($this->query_result) : false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		$safe_query_id = $this->clean_query_id($query_id);
		if ($cache && $cache->sql_exists($safe_query_id))
		{
			return $cache->sql_fetchrow($safe_query_id);
		}

		if (!$query_id)
		{
			return false;
		}

		$row = sqlsrv_fetch_array($query_id, SQLSRV_FETCH_ASSOC);

		if ($row)
		{
			foreach ($row as $key => $value)
			{
				$row[$key] = ($value === ' ' || $value === null) ? '' : $value;
			}

			// remove helper values from LIMIT queries
			if (isset($row['line2']))
			{
				unset($row['line2'], $row['line3']);
			}
		}
		return ($row !== null) ? $row : false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function sql_last_inserted_id()
	{
		$result_id = @sqlsrv_query($this->db_connect_id, 'SELECT @@IDENTITY');

		if ($result_id)
		{
			$row = sqlsrv_fetch_array($result_id);
			$id = isset($row[0]) ? (int) $row[0] : false;
			sqlsrv_free_stmt($result_id);
			return $id;
		}
		else
		{
			return false;
		}
	}

	/**
	* {@inheritDoc}
	*/
	function sql_freeresult($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		$safe_query_id = $this->clean_query_id($query_id);
		if ($cache && $cache->sql_exists($safe_query_id))
		{
			$cache->sql_freeresult($safe_query_id);
		}
		else if (isset($this->open_queries[$safe_query_id]))
		{
			unset($this->open_queries[$safe_query_id]);
			sqlsrv_free_stmt($query_id);
		}
	}

	/**
	* {@inheritDoc}
	*/
	protected function _sql_error(): array
	{
		if (function_exists('sqlsrv_errors'))
		{
			$errors = @sqlsrv_errors(SQLSRV_ERR_ERRORS);
			$error_message = '';
			$code = 0;

			if ($errors != null)
			{
				foreach ($errors as $error)
				{
					$error_message .= "SQLSTATE: " . $error['SQLSTATE'] . "\n";
					$error_message .= "code: " . $error['code'] . "\n";
					$code = $error['code'];
					$error_message .= "message: " . $error['message'] . "\n";
				}
				$this->last_error_result = $error_message;
				$error = $this->last_error_result;
			}
			else
			{
				$error = $this->last_error_result ?: '';
			}

			$error = array(
				'message'	=> $error,
				'code'		=> $code,
			);
		}
		else
		{
			$error = array(
				'message'	=> $this->connect_error,
				'code'		=> '',
			);
		}

		return $error;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _sql_close(): bool
	{
		return @sqlsrv_close($this->db_connect_id);
	}

	/**
	* {@inheritDoc}
	*/
	protected function _sql_report(string $mode, string $query = ''): void
	{
		switch ($mode)
		{
			case 'start':
				$html_table = false;
				@sqlsrv_query($this->db_connect_id, 'SET SHOWPLAN_TEXT ON;');
				if ($result = @sqlsrv_query($this->db_connect_id, $query))
				{
					sqlsrv_next_result($result);
					while ($row = sqlsrv_fetch_array($result))
					{
						$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
					}
					sqlsrv_free_stmt($result);
				}
				@sqlsrv_query($this->db_connect_id, 'SET SHOWPLAN_TEXT OFF;');

				if ($html_table)
				{
					$this->html_hold .= '</table>';
				}
			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @sqlsrv_query($this->db_connect_id, $query);
				if ($result)
				{
					while ($void = sqlsrv_fetch_array($result))
					{
						// Take the time spent on parsing rows into account
					}
					sqlsrv_free_stmt($result);
				}

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}

	/**
	* Utility method used to retrieve number of rows
	* Emulates mysql_num_rows
	* Used in acp_database.php -> write_data_mssqlnative()
	* Requires a static or keyset cursor to be definde via
	* mssqlnative_set_query_options()
	*/
	function mssqlnative_num_rows($res)
	{
		if ($res !== false)
		{
			return sqlsrv_num_rows($res);
		}
		else
		{
			return false;
		}
	}

	/**
	* Allows setting mssqlnative specific query options passed to sqlsrv_query as 4th parameter.
	*/
	function mssqlnative_set_query_options($options)
	{
		$this->query_options = $options;
	}
}
