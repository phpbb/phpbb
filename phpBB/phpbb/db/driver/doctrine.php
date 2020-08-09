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

namespace phpbb\db\driver;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use phpbb\cache\doctrine_bridge;
use phpbb\db\connection_factory;
use phpbb\db\result_iterator;
use phpbb\db\result_iterator_interface;

/**
 * Wrapper class for Doctrine DBAL.
 *
 * @deprecated 4.0.0-dev The driver interface is deprecated, please use Doctrine DBAL directly instead.
 */
class doctrine extends driver implements driver_interface
{
	/**
	 * @var Connection|null
	 */
	private $connection;

	/**
	 * @var AbstractPlatform|null
	 */
	private $platform = null;

	/**
	 * @var int
	 */
	private $affected_rows = 0;

	/**
	 * @var string
	 */
	private $insert_table_name = '';

	/**
	 * @var bool
	 */
	private $enable_caching;

	/**
	 * Database driver constructor.
	 *
	 * @param Connection|null $connection Doctrine connection object.
	 */
	public function __construct(?Connection $connection = null)
	{
		parent::__construct();

		$this->connection = $connection;
		$this->enable_caching = !is_null($connection);

		$this->detect_platform();
		$this->db_connect_id = false;
	}

	/**
	 * Sets a cache instance to the database driver.
	 *
	 * @param \phpbb\cache\driver\driver_interface $cache
	 */
	public function set_cache(\phpbb\cache\driver\driver_interface $cache)
	{
		$cache_driver = new doctrine_bridge($cache);
		$config = $this->connection->getConfiguration();
		$config->setResultCacheImpl($cache_driver);
		$this->enable_caching = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_sql_layer()
	{
		$platform_name = $this->platform->getName();

		switch ($platform_name)
		{
			case 'sqlite':
				return 'sqlite3';
			case 'oracle':
				return 'oracle';
			case 'postgresql':
				return 'postgres';
			case 'mysql':
				return 'mysqli';
			case 'mssql':
				return 'mssqlnative';
		}

		return $platform_name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_db_name()
	{
		return $this->connection->getDatabase();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_transaction()
	{
		return $this->connection->isTransactionActive();
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_close()
	{
		if ($this->connection === null)
		{
			return false;
		}

		if ($this->connection->isTransactionActive())
		{
			do
			{
				$this->sql_transaction('commit');
			}
			while ($this->connection->isTransactionActive());
		}

		foreach ($this->open_queries as $query_id)
		{
			$this->sql_freeresult($query_id);
		}

		// Connection closed correctly. Set db_connect_id to false to prevent errors
		$this->connection->close();
		$this->connection = null;
		$this->db_connect_id = false;

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		if (empty($query))
		{
			return false;
		}

		// Never use a negative total or offset
		$total = ($total < 0) ? 0 : $total;
		$offset = ($offset < 0) ? 0 : $offset;

		if ($total !== 0 || $offset !== 0)
		{
			try
			{
				$total = ($total === 0) ? null : $total;
				$query = $this->platform->modifyLimitQuery($query, $total, $offset);
			}
			catch (DBALException $e)
			{
				return false;
			}
		}

		return $this->sql_query($query, $cache_ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_fetchrowset($query_id = false)
	{
		if (!($query_id instanceof result_iterator_interface))
		{
			return false;
		}

		return $query_id->fetch_all();
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_rowseek($rownum, &$query_id)
	{
		if (!($query_id instanceof result_iterator_interface))
		{
			return false;
		}

		$query_id->seek($rownum);
		return $query_id->valid();
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_fetchfield($field, $rownum = false, $query_id = false)
	{
		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (!($query_id instanceof result_iterator_interface))
		{
			return false;
		}

		if ($rownum !== false)
		{
			$query_id->seek($rownum);
		}

		$row = $this->sql_fetchrow($query_id);
		if ($row === false)
		{
			return false;
		}

		return array_key_exists($field, $row) ? $row[$field] : false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_like_expression($expression)
	{
		$expression = str_replace(['_', '%'], ["\_", "\%"], $expression);
		$expression = str_replace([chr(0) . "\_", chr(0) . "\%"], ['_', '%'], $expression);

		$platform = $this->platform->getName();
		switch ($platform)
		{
			case 'mssql':
			case 'oracle':
				return 'LIKE \'' . $this->sql_escape($expression) . '\'' . " ESCAPE '\\'";
				break;
			case 'sqlite':
				$expression = str_replace(['_', '%'], ['?', '*'], $expression);
				return 'GLOB \'' . $this->sql_escape($expression) . '\'';
				break;
			default:
				return 'LIKE \'' . $this->sql_escape($expression) . '\'';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_not_like_expression($expression)
	{
		$expression = str_replace(['_', '%'], ["\_", "\%"], $expression);
		$expression = str_replace([chr(0) . "\_", chr(0) . "\%"], ['_', '%'], $expression);

		$platform = $this->platform->getName();
		switch ($platform)
		{
			case 'mssql':
			case 'oracle':
				return 'NOT LIKE \'' . $this->sql_escape($expression) . '\'' . " ESCAPE '\\'";
				break;
			case 'sqlite':
				$expression = str_replace(['_', '%'], ['?', '*'], $expression);
				return 'NOT GLOB \'' . $this->sql_escape($expression) . '\'';
				break;
			default:
				return 'NOT LIKE \'' . $this->sql_escape($expression) . '\'';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_concatenate($expr1, $expr2)
	{
		return $this->platform->getConcatExpression($expr1, $expr2);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				$this->connection->beginTransaction();
				break;

			case 'commit':
				try
				{
					$this->connection->commit();
				}
				catch (ConnectionException $e)
				{
					$this->sql_error();
					return false;
				}
				break;

			case 'rollback':
				try
				{
					$this->connection->rollBack();
				}
				catch (ConnectionException $e)
				{
					$this->sql_error();
					return false;
				}
				break;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_bit_and($column_name, $bit, $compare = '')
	{
		$bitand = $this->platform->getBitAndComparisonExpression($column_name, (1 << $bit));
		$bitand .= (empty($compare)) ? '' : ' ' . $compare;
		return $bitand;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_bit_or($column_name, $bit, $compare = '')
	{
		$bitor = $this->platform->getBitOrComparisonExpression($column_name, (1 << $bit));
		$bitor .= (empty($compare)) ? '' : ' ' . $compare;
		return $bitor;
	}

	/**
	 * {@inheritDoc}
	 */
	public function cast_expr_to_bigint($expression)
	{
		$platform = $this->platform->getName();
		switch ($platform)
		{
			case 'mssql':
				return 'CONVERT(BIGINT, ' . $expression . ')';
				break;
			case 'postgresql':
				return 'CAST(' . $expression . ' as DECIMAL(255, 0))';
				break;
			default:
				return $expression;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function cast_expr_to_string($expression)
	{
		$platform = $this->platform->getName();
		switch ($platform)
		{
			case 'postgresql':
				return 'CAST(' . $expression . ' as VARCHAR(255))';
				break;
			default:
				return $expression;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_lower_text($column_name)
	{
		return $this->platform->getLowerExpression($column_name);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_escape($msg)
	{
		$quote = $this->connection->quote($msg);
		if (strpos($quote, "'") === 0)
		{
			return substr($quote, 1, -1);
		}

		return str_replace(array("'", "\0"), array("''", ''), $msg);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		if ($this->connection !== null && !$new_link)
		{
			return $this->connection->isConnected();
		}

		try
		{
			$this->connection = connection_factory::get_connection_from_params(
				get_class($this),
				$sqlserver,
				($port !== false) ? $port : '',
				$sqluser,
				$sqlpassword,
				$database
			);

			if (!$this->connection->isConnected())
			{
				$this->connection->connect();
			}
		}
		catch (\Throwable $e)
		{
			return false;
		}

		$this->detect_platform();

		return $this->connection->isConnected();
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_server_info($raw = false, $use_cache = true)
	{
		$sql = $this->get_version_query_sql();
		if (!$sql)
		{
			return $this->platform->getName();
		}

		$result = $this->sql_query($sql);
		$version = $this->sql_fetchfield('version');
		$this->sql_freeresult($result);

		return ($raw) ? $version : $this->platform->getName() . ': ' . $version;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_query($query = '', $cache_ttl = 0)
	{
		if (empty($query))
		{
			return false;
		}

		$query_type = strtolower(substr($query, 0, 6));
		if ($query_type === 'update' || $query_type === 'insert' || $query_type === 'delete')
		{
			$result = $this->execute($query);
		}
		else
		{
			$result = $this->query($query, $cache_ttl);
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_affectedrows()
	{
		return $this->affected_rows;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_fetchrow($query_id = false)
	{
		if (!($query_id instanceof result_iterator_interface))
		{
			return false;
		}

		if (!$query_id->valid())
		{
			return false;
		}

		$row = $query_id->current();
		$query_id->next();
		return $row;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_nextid()
	{
		$platform = $this->platform->getName();
		switch ($platform)
		{
			case 'postgresql':
			case 'oracle':
				$seq_name = $this->insert_table_name . '_seq';
			break;
			default:
				$seq_name = null;
		}

		return $this->connection->lastInsertId($seq_name);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sql_freeresult($query_id = false)
	{
		if ($query_id === false)
		{
			$query_id = $this->query_result;
			$this->query_result = null;
		}

		if (!($query_id instanceof result_iterator_interface))
		{
			return false;
		}

		$query_id->invalidate();

		if (array_key_exists($query_id->get_id(), $this->open_queries))
		{
			//unset($this->open_queries[$query_id->get_id()]);
		}

		return true;
	}

	/**
	 * Build db-specific query data
	 *
	 * @param	string	$stage		Available stages: FROM, WHERE
	 * @param	mixed	$data		A string containing the CROSS JOIN query or an array of WHERE clauses
	 *
	 * @return	string	The db-specific query fragment
	 */
	public function _sql_custom_build($stage, $data)
	{
		if ($stage === 'FROM' && $this->platform->getName() === 'mysql')
		{
			return '(' . $data . ')';
		}

		return $data;
	}

	public function _sql_error()
	{
		$error = $this->connection->errorInfo();
		return [
			'message'	=> $error[1],
			'code'		=> $error[2],
		];
	}

	public function _sql_report($mode, $query = '')
	{
		return null;
	}

	/**
	 * Retrieves the underlying database platform.
	 *
	 * @return void
	 */
	private function detect_platform()
	{
		if ($this->connection === null)
		{
			return;
		}

		try
		{
			$this->platform = $this->connection->getDatabasePlatform();
		}
		catch (DBALException $e)
		{
			return;
		}

		$platform = $this->platform->getName();
		if ($platform === 'mysql' || $platform === 'postgresql')
		{
			$this->multi_insert = true;
		}
	}

	/**
	 * Executes a SQL query and returns the number of affected rows.
	 *
	 * @param string $sql The SQL query.
	 *
	 * @return bool|int Number of affected rows or false on failure.
	 */
	private function execute($sql)
	{
		$this->start_query_timer($sql);

		try
		{
			$this->affected_rows = $this->connection->exec($sql);

			$table = [];
			if (preg_match('#^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)#is', $sql, $table))
			{
				$this->insert_table_name = $table[1];
			}

			$result = $this->affected_rows;
		}
		catch (DBALException $e)
		{
			$this->sql_error($sql);
			$result = false;
		}

		$this->stop_query_timer($sql);
		return $result;
	}

	/**
	 * Executes a SQL query.
	 *
	 * @param string	$sql		The query SQL.
	 * @param int		$cache_ttl	Cache time-to-live.
	 *
	 * @return false|result_iterator
	 */
	private function query($sql, $cache_ttl = 0)
	{
		$this->start_query_timer($sql);
		$this->sql_add_num_queries(false);

		try
		{
			$cache_config = null;
			if ($cache_ttl !== 0 && $this->enable_caching)
			{
				$cache_config = new QueryCacheProfile($cache_ttl, $sql);
			}

			$this->query_result = new result_iterator($this->connection->executeQuery($sql, [], [], $cache_config), $sql, $this);
		}
		catch (DBALException $e)
		{
			$this->sql_error($sql);
			$this->query_result = false;
		}

		$this->stop_query_timer($sql);

		if (!$this->query_result)
		{
			return false;
		}

		if ($cache_ttl !== 0 && $this->enable_caching)
		{
			$this->query_result->fetch_all();
		}

		$this->open_queries[$this->query_result->get_id()] = $this->query_result;

		return $this->query_result;
	}

	/**
	 * Starts the query timer.
	 *
	 * @param string $query The SQL query.
	 */
	private function start_query_timer($query)
	{
		if ($this->debug_sql_explain)
		{
			$this->sql_report('start', $query);
		}
		else if ($this->debug_load_time)
		{
			$this->curtime = microtime(true);
		}
	}

	/**
	 * Stops the query timer.
	 *
	 * @param string $query The SQL query.
	 */
	private function stop_query_timer($query)
	{
		if ($this->debug_sql_explain)
		{
			$this->sql_report('stop', $query);
		}
		else if ($this->debug_load_time)
		{
			$this->sql_time += microtime(true) - $this->curtime;
		}
	}

	/**
	 * Returns a query string to query the version information.
	 *
	 * @return string The version number.
	 */
	private function get_version_query_sql()
	{
		$platform = $this->platform->getName();
		switch ($platform)
		{
			case 'mssql':
				return "SELECT CONCAT(CAST(SERVERPROPERTY('productversion') as varchar(100)), ' ', CAST(SERVERPROPERTY('productlevel') as varchar(100)), ' ', CAST(SERVERPROPERTY('edition') as varchar(100))) AS version";
			case 'mysql':
			case 'postgresql':
				return 'SELECT VERSION() AS version';
			case 'oracle':
				return 'SELECT banner as version FROM v$version WHERE banner LIKE \'Oracle%\'';
			case 'sqlite':
				return 'SELECT sqlite_version() AS version';
			default:
				return false;
		}
	}
}
