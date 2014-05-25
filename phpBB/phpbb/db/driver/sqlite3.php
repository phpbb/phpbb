<?php
/**
*
* @package dbal
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\driver;

/**
* SQLite3 Database Abstraction Layer
* Minimum Requirement: 3.6.15+
* @package dbal
*/
class sqlite3 extends \phpbb\db\driver\driver
{
	/**
	* @var	string		Stores errors during connection setup in case the driver is not available
	*/
	protected $connect_error = '';

	/**
	* @var	\SQLite3	The SQLite3 database object to operate against
	*/
	protected $dbo = null;

	/**
	* {@inheritDoc}
	*/
	public function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		$this->persistency = false;
		$this->user = $sqluser;
		$this->server = $sqlserver . (($port) ? ':' . $port : '');
		$this->dbname = $database;

		if (!class_exists('SQLite3', false))
		{
			$this->connect_error = 'SQLite3 not found, is the extension installed?';
			return $this->sql_error('');
		}

		try
		{
			$this->dbo = new \SQLite3($this->server, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
			$this->db_connect_id = true;
		}
		catch (Exception $e)
		{
			return array('message' => $e->getMessage());
		}

		return true;
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_server_info($raw = false, $use_cache = true)
	{
		global $cache;

		if (!$use_cache || empty($cache) || ($this->sql_server_version = $cache->get('sqlite_version')) === false)
		{
			$version = \SQLite3::version();

			$this->sql_server_version = $version['versionString'];

			if (!empty($cache) && $use_cache)
			{
				$cache->put('sqlite_version', $this->sql_server_version);
			}
		}

		return ($raw) ? $this->sql_server_version : 'SQLite ' . $this->sql_server_version;
	}

	/**
	* SQL Transaction
	*
	* @param	string	$status		Should be one of the following strings:
	*								begin, commit, rollback
	* @return	bool	Success/failure of the transaction query
	*/
	protected function _sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				return $this->dbo->exec('BEGIN IMMEDIATE');
			break;

			case 'commit':
				return $this->dbo->exec('COMMIT');
			break;

			case 'rollback':
				return $this->dbo->exec('ROLLBACK');
			break;
		}

		return true;
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_query($query = '', $cache_ttl = 0)
	{
		if ($query != '')
		{
			global $cache;

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG'))
			{
				$this->sql_report('start', $query);
			}

			$this->last_query_text = $query;
			$this->query_result = ($cache && $cache_ttl) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if ($this->query_result === false)
			{
				if (($this->query_result = @$this->dbo->query($query)) === false)
				{
					$this->sql_error($query);
				}

				if (defined('DEBUG'))
				{
					$this->sql_report('stop', $query);
				}

				if ($cache && $cache_ttl)
				{
					$this->query_result = $cache->sql_save($this, $query, $this->query_result, $cache_ttl);
				}
			}
			else if (defined('DEBUG'))
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
	* Build LIMIT query
	*
	* @param	string	$query		The SQL query to execute
	* @param	int		$total		The number of rows to select
	* @param	int		$offset
	* @param	int		$cache_ttl	Either 0 to avoid caching or
	*				the time in seconds which the result shall be kept in cache
	* @return	mixed	Buffered, seekable result handle, false on error
	*/
	protected function _sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$this->query_result = false;

		// if $total is set to 0 we do not want to limit the number of rows
		if ($total == 0)
		{
			$total = -1;
		}

		$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);

		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_affectedrows()
	{
		return ($this->db_connect_id) ? $this->dbo->changes() : false;
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_fetchrow($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($cache && !is_object($query_id) && $cache->sql_exists($query_id))
		{
			return $cache->sql_fetchrow($query_id);
		}

		return is_object($query_id) ? $query_id->fetchArray(SQLITE3_ASSOC) : false;
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_nextid()
	{
		return ($this->db_connect_id) ? $this->dbo->lastInsertRowID() : false;
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_freeresult($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($cache && !is_object($query_id) && $cache->sql_exists($query_id))
		{
			return $cache->sql_freeresult($query_id);
		}

		if ($query_id)
		{
			return @$query_id->finalize();
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_escape($msg)
	{
		return \SQLite3::escapeString($msg);
	}

	/**
	* {@inheritDoc}
	*
	* For SQLite an underscore is a not-known character...
	*/
	public function sql_like_expression($expression)
	{
		// Unlike LIKE, GLOB is case sensitive (unfortunatly). SQLite users need to live with it!
		// We only catch * and ? here, not the character map possible on file globbing.
		$expression = str_replace(array(chr(0) . '_', chr(0) . '%'), array(chr(0) . '?', chr(0) . '*'), $expression);

		$expression = str_replace(array('?', '*'), array("\?", "\*"), $expression);
		$expression = str_replace(array(chr(0) . "\?", chr(0) . "\*"), array('?', '*'), $expression);

		return 'GLOB \'' . $this->sql_escape($expression) . '\'';
	}

	/**
	* return sql error array
	*
	* @return array
	*/
	protected function _sql_error()
	{
		if (class_exists('SQLite3', false))
		{
			$error = array(
				'message'	=> $this->dbo->lastErrorMsg(),
				'code'		=> $this->dbo->lastErrorCode(),
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
	* Build db-specific query data
	*
	* @param	string	$stage		Available stages: FROM, WHERE
	* @param	mixed	$data		A string containing the CROSS JOIN query or an array of WHERE clauses
	*
	* @return	string	The db-specific query fragment
	*/
	protected function _sql_custom_build($stage, $data)
	{
		return $data;
	}

	/**
	* Close sql connection
	*
	* @return	bool		False if failure
	*/
	protected function _sql_close()
	{
		return $this->dbo->close();
	}

	/**
	* Build db-specific report
	*
	* @param	string	$mode		Available modes: display, start, stop,
	*								add_select_row, fromcache, record_fromcache
	* @param	string	$query		The Query that should be explained
	* @return	mixed		Either a full HTML page, boolean or null
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

					if ($result = $this->dbo->query("EXPLAIN QUERY PLAN $explain_query"))
					{
						while ($row = $result->fetchArray(SQLITE3_ASSOC))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
					}

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = $this->dbo->query($query);
				while ($void = $result->fetchArray(SQLITE3_ASSOC))
				{
					// Take the time spent on parsing rows into account
				}

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}
}
