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
* DBAL Factory
* Only used to instantiate a new DB object
*
* @package dbal
* @static
*/
abstract class phpbb_db_dbal
{
	/**
	* Get new database object
	*
	* @param string	$dbms	Database module to call
	* @return object	Database object
	* @access public
	*/
	public static function new_instance($dbms)
	{
		$class = 'phpbb_dbal_' . $dbms;

		if (!class_exists($class))
		{
			include PHPBB_ROOT_PATH . 'includes/db/' . $dbms . '.' . PHP_EXT;
		}

		// Instantiate class
		$db = new $class();

		// Fill default sql layer
		$db->sql_layer = $dbms;

		return $db;
	}

	/**
	* Get new database object and connect to database
	*
	* @param string	$dbms			Database module to call
	* @param string	$server			DB server address to connect to
	* @param string	$user			DB user name
	* @param string	$password		DB password to use
	* @param string	$database		Database name to connect to
	* @param int	$port			DB Port
	* @param bool	$persistency	Open persistent DB connection if true
	* @param bool	$new_link		If set to true a new connection is opened instead of re-using old connections
	*
	* @return object	Database object
	* @access public
	*/
	public static function connect($dbms, $server, $user, $password, $database, $port = false, $persistency = false, $new_link = false)
	{
		$class = 'phpbb_dbal_' . $dbms;

		if (!class_exists($class))
		{
			include PHPBB_ROOT_PATH . 'includes/db/' . $dbms . '.' . PHP_EXT;
		}

		// Instantiate class
		$db = new $class();

		// Fill default sql layer
		$db->sql_layer = $dbms;

		// Connect to DB
		$db->sql_connect($server, $user, $password, $database, $port, $persistency, $new_link);

		// Return db object
		return $db;
	}
}

/**
* Database Abstraction Layer
* @package dbal
*/
abstract class phpbb_dbal
{
	/**
	* @var array required phpBB objects
	*/
	public $phpbb_required = array('config');

	/**
	* @var array Optional phpBB objects
	*/
	public $phpbb_optional = array('acm', 'user', 'acl');

	/**
	* @var string Current sql layer name
	*/
	public $sql_layer = '';

	/**
	* @var string Exact version of the DBAL, directly queried
	*/
	public $sql_server_version = false;

	/**
	* @var mixed Database connection id/resource
	*/
	public $db_connect_id;

	/**
	* @var mixed Database query result id/resource
	*/
	public $query_result;

	/**
	* @var bool Persistent connection
	*/
	public $persistency = false;

	/**
	* @var string DB user name
	*/
	public $user = '';

	/**
	* @var string DB server address connected to
	*/
	public $server = '';

	/**
	* @var string Database name connected to
	*/
	public $dbname = '';

	/**
	* @var int Database port used
	*/
	public $port = 0;

	/**
	* @var bool Is true if in transaction
	*/
	public $transaction = false;

	/**
	* @var int Holding transaction count
	*/
	public $transactions = 0;

	/**
	* Stores number of queries
	*
	* Keys are:
	* <ul>
	* <li>cached: Number of cached queries executed</li>
	* <li>normal: Number of non-cached queries executed</li>
	* <li>total: Total number of queries executed</li>
	* </ul>
	*
	* @var array
	*/
	public $num_queries = array();

	/**
	* Stores opened queries.
	*
	* The key is returned by {@link phpbb_dbal::sql_get_result_key() sql_get_result_key()}.
	* The value is the {@link phpbb_dbal::$query_result Database query result id/resource}.
	*
	* @var array
	*/
	public $open_queries = array();

	/**
	* @var string Wildcard for matching any (%) character within LIKE expressions
	*/
	public $any_char;

	/**
	* @var string Wildcard for matching exactly one (_) character within LIKE expressions
	*/
	public $one_char;

	/**
	* @var array Storing cached result rowset
	*/
	protected $cache_rowset = array();

	/**
	* @var int Storing cached result rowset index
	*/
	protected $cache_index = 0;

	/**
	* @var bool If true then methods do not call {@link phpbb_dbal::sql_error() sql_error()} on SQL error, but return silently.
	*/
	public $return_on_error = false;

	/**
	* @var bool This is set to true if an error had been triggered.
	*/
	public $sql_error_triggered = false;

	/**
	* @var string Holds the last sql query on triggered sql error.
	*/
	public $sql_error_sql = '';

	/**
	* @var array Holds the SQL error information - only populated if {@link phpbb_dbal::$sql_error_triggered sql_error_triggered} is set to true.
	*/
	public $sql_error_returned = array();

	/**
	* Database features
	*
	* <ul>
	* <li>multi_insert: Supports multi inserts</li>
	* <li>count_distinct: Supports COUNT(DISTINGT ...)</li>
	* <li>multi_table_deletion: Supports multiple table deletion</li>
	* <li>truncate: Supports table truncation</li>
	* </ul>
	*
	* @var array
	*/
	public $features = array(
		'multi_insert'			=> true,
		'count_distinct'		=> true,
		'multi_table_deletion'	=> true,
		'truncate'				=> true,
	);

	/**
	* @var int Passed time for executing SQL queries
	*/
	public $sql_time = 0;

	/**
	* @var int Current timestamp
	*/
	public $curtime = 0;

	/**#@+
	* @var string String to hold information for {@link phpbb_dbal::sql_report() SQL report}.
	*/
	protected $query_hold = '';
	protected $html_hold = '';
	protected $sql_report = '';
	/**#@-*/

	/**
	* Constructor. Set default values.
	* @access public
	*/
	public function __construct()
	{
		$this->num_queries = array(
			'cached'		=> 0,
			'normal'		=> 0,
			'total'			=> 0,
		);

		// Do not change this please! This variable is used to easy the use of it - and is hardcoded.
		$this->any_char = chr(0) . '%';
		$this->one_char = chr(0) . '_';

		$this->cache_rowset = array();
		$this->cache_index = 0;
	}

	/**
	* Connect to SQL Server.
	*
	* @param string	$server			DB server address to connect to
	* @param string	$user			DB user name
	* @param string	$password		DB password to use
	* @param string	$database		Database name to connect to
	* @param int	$port			DB Port
	* @param bool	$persistency	Open persistent DB connection if true
	* @param bool	$new_link		If set to true a new connection is opened instead of re-using old connections
	*
	* @return mixed	Database connection id/resource
	* @access public
	*/
	abstract public function sql_connect($server, $user, $password, $database, $port = false, $persistency = false , $new_link = false);

	/**
	* Version information about used database
	*
	* @param bool	$raw	If true, only return the fetched sql_server_version without any additional strings
	*
	* @return string	Sql server version
	* @access public
	*/
	abstract public function sql_server_info($raw = false);

	/**
	* Return number of affected rows.
	*
	* @return int	Number of affected rows. False if there is no valid database connection id.
	* @access public
	*/
	abstract public function sql_affectedrows();

	/**
	* Get last inserted id after insert statement
	*
	* @return int	Last inserted id. False if there is no valid database connection id.
	* @access public
	*/
	abstract public function sql_nextid();

	/**
	* Escape string used in sql query.
	*
	* @param string	$msg	Text to escape
	*
	* @return string	Escaped text
	* @access public
	*/
	abstract public function sql_escape($msg);

	/**
	* Expose a DBMS specific function.
	*
	* Supported types are:
	* <ul>
	* <li>length_varchar: Get expression to return length of VARCHAR</li>
	* <li>length_text: Get expression to return length of TEXT</li>
	* </ul>
	*
	* @param string	$type	Type to return DB-specific code for
	* @param string	$col	Column name to operate on
	*
	* @return string	DB-specific code able to be used in SQL query
	* @access public
	*/
	abstract public function sql_function($type, $col);

	/**
	* Handle data by using prepared statements.
	*
	* @param string	$type	The type to handle. Possible values are: INSERT, UPDATE
	* @param string	$table	The table to use insert or update
	* @param mixed	$data	The data to insert/update in an array (key == column, value == value)
	* @param string $where	An optional where-statement
	* @access public
	* @todo implement correctly by using types and only overwrite if DB supports prepared statements
	*/
	public function sql_handle_data($type, $table, $data, $where = '')
	{
		if ($type === 'UPDATE')
		{
			$where = ($where) ? ' WHERE ' . $where : '';
			$this->sql_query('UPDATE ' . $table . ' SET ' . $db->sql_build_array('UPDATE', $data) . $where);
		}
		else
		{
			$this->sql_query('INSERT INTO ' . $table . ' ' . $this->sql_build_array('INSERT', $data));
		}
	}

	/**
	* DB-specific base query method. Called by {@link phpbb_dbal::sql_query() sql_query()}.
	*
	* @param string	$query		Contains the SQL query which shall be executed
	*
	* @return mixed	Returns the query result resource. When casted to bool the returned value returns true on success and false on failure
	* @access protected
	*/
	abstract protected function _sql_query($query);

	/**
	* DB-specific method to Build LIMIT query and run it. Called by {@link phpbb_dbal::sql_query_limit() sql_query_limit}.
	*
	* @param string	$query	SQL query LIMIT should be applied to
	* @param int	$total	Total number of rows returned
	* @param int	$offset	Offset to read from
	* @param int	$cache_ttl	Either 0 to avoid caching or the time in seconds which the result shall be kept in cache
	*
	* @return mixed	Returns the query result resource. When casted to bool the returned value returns true on success and false on failure
	* @access protected
	*/
	abstract protected function _sql_query_limit($query, $total, $offset, $cache_ttl);

	/**
	* DB-specific method to close sql connection. Called by {@link phpbb_dbal::sql_close() sql_close()}.
	* @access protected
	*/
	abstract protected function _sql_close();

	/**
	* DB-specific SQL Transaction. Called by {@link phpbb_dbal::sql_transaction() sql_transaction()}.
	*
	* @param string	$status	The status code. See {@link phpbb_dbal::sql_transaction() sql_transaction()} for status codes.
	*
	* @return mixed	The result returned by the DB
	* @access protected
	*/
	abstract protected function _sql_transaction($status);

	/**
	* Fetch current row. Called by {@link phpbb_dbal::sql_fetchrow() sql_fetchrow()}.
	*
	* @param mixed	$query_id	Query result resource
	*
	* @return array|bool	The current row or false if an error occurred
	* @access protected
	*/
	abstract protected function _sql_fetchrow($query_id);

	/**
	* Free query result. Called by {@link phpbb_dbal::sql_freeresult() sql_freeresult()}.
	*
	* @param mixed	$query_id	Query result resource
	*
	* @return mixed	The DB result
	* @access protected
	*/
	abstract protected function _sql_freeresult($query_id);

	/**
	* Correctly adjust LIKE expression for special characters. Called by {@link phpbb_dbal::sql_like_expression() sql_like_expression()}.
	*
	* @param string	$expression	The expression to use. Every wildcard is escaped, except {@link phpbb_dbal::$any_char $any_char} and {@link phpbb_dbal::$one_char $one_char}
	*
	* @return string	LIKE expression including the keyword!
	* @access protected
	*/
	abstract protected function _sql_like_expression($expression);

	/**
	* Build DB-specific query bits for {@link phpbb_dbal::sql_build_query() sql_build_query()}.
	*
	* Currently used stages are ($stage: $data)
	* <ul>
	* <li>FROM: implode(', ', $table_array)</li>
	* <li>WHERE: Full WHERE-Statement without WHERE keyword</li>
	* </ul>
	*
	* @param string	$stage	The current stage build_query needs db-specific data for. Currently used are: FROM and WHERE.
	* @param string	$data	Data to operate with
	*
	* @return string	$data in it's original form or adjusted to meet DB-specific standard
	* @access protected
	*/
	abstract protected function _sql_custom_build($stage, $data);

	/**
	* Return sql error array. Called by {@link phpbb_dbal::sql_error() sql_error()}.
	*
	* @return array	Array with two keys. 'code' for the error code and 'message' for the error message.
	* @access protected
	*/
	abstract protected function _sql_error();

	/**
	* Run DB-specific code to build SQL Report to explain queries, show statistics and runtime information. Called by {@link phpbb_dbal::sql_report() sql_report()}.
	*
	* This function only executes if the GET parameter 'explain' is true and phpbb::$base_config['debug_extra'] enabled.
	*
	* @param string	$mode	The mode to handle. 'display' is used for displaying the report, all other modes are internal.
	* @param string $query	Query to document/explain. Only used internally to build the plan.
	*
	* @access protected
	*/
	abstract protected function _sql_report($mode, $query = '');

	/**
	* Base query method
	*
	* @param string	$query		Contains the SQL query which shall be executed
	* @param int	$cache_ttl	Either 0 to avoid caching or the time in seconds which the result shall be kept in cache
	*
	* @return mixed	Returns the query result resource. When casted to bool the returned value returns true on success and false on failure
	* @access public
	*/
	public function sql_query($query = '', $cache_ttl = 0)
	{
		if (empty($query))
		{
			return false;
		}

		// EXPLAIN only in extra debug mode
		if (phpbb::$base_config['debug_extra'])
		{
			$this->sql_report('start', $query);
		}

		$this->query_result = false;

		if ($cache_ttl)
		{
			$this->sql_get_cache($query);
		}

		$this->sql_add_num_queries($this->query_result);

		if ($this->query_result !== false)
		{
			if (phpbb::$base_config['debug_extra'])
			{
				$this->sql_report('fromcache', $query);
			}

			return $this->query_result;
		}

		if (($this->query_result = $this->_sql_query($query)) === false)
		{
			$this->sql_error($query);
		}

		if (phpbb::$base_config['debug_extra'])
		{
			$this->sql_report('stop', $query);
		}

		if ($cache_ttl)
		{
			$this->sql_put_cache($query, $cache_ttl);
		}

		if ($cache_ttl || strpos($query, 'SELECT') === 0)
		{
			if (($key = $this->sql_get_result_key($this->query_result)) !== false)
			{
				$this->open_queries[$key] = $this->query_result;
			}
		}

		return $this->query_result;
	}

	/**
	* Build LIMIT query and run it.
	*
	* @param string	$query	SQL query LIMIT should be applied to
	* @param int	$total	Total number of rows returned
	* @param int	$offset	Offset to read from
	* @param int	$cache_ttl	Either 0 to avoid caching or the time in seconds which the result shall be kept in cache
	*
	* @return mixed	Returns the query result resource. When casted to bool the returned value returns true on success and false on failure
	* @access public
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

		return $this->_sql_query_limit($query, $total, $offset, $cache_ttl);
	}

	/**
	* Switch for "return on error" or "display error message". Affects {@link phpbb_dbal::$return_on_error $return_on_error}.
	*
	* @param bool	$fail	True to return on SQL error. False to display error message on SQL error.
	* @access public
	*/
	public function sql_return_on_error($fail = false)
	{
		$this->sql_error_triggered = false;
		$this->sql_error_sql = '';

		$this->return_on_error = $fail;
	}

	/**
	* Return number of sql queries and cached sql queries used.
	*
	* @param bool	$cached	True to return cached queries executed. False to return non-cached queries executed.
	*
	* @return int	Number of queries executed
	* @access public
	*/
	public function sql_num_queries($cached = false)
	{
		return ($cached) ? $this->num_queries['cached'] : $this->num_queries['normal'];
	}

	/**
	* DBAL garbage collection, close sql connection.
	*
	* Iterates through {@link phpbb_dbal::$open_queries open queries} and closes them.
	* For connection close {@link phpbb_dbal::_sql_close() the DB-specific method} is called.
	*
	* @return bool	False if there was no db connection to close or an error occurred, else true
	* @access public
	*/
	public function sql_close()
	{
		if (!$this->db_connect_id)
		{
			return false;
		}

		if ($this->transaction)
		{
			do
			{
				$this->sql_transaction('commit');
			}
			while ($this->transaction);
		}

		foreach ($this->open_queries as $key => $query_result)
		{
			$this->sql_freeresult($query_result);
		}

		// Connection closed correctly. Set db_connect_id to false to prevent errors
		if ($result = $this->_sql_close())
		{
			$this->db_connect_id = false;
		}

		return $result;
	}

	/**
	* SQL Transaction.
	*
	* Standard status codes are:
	* <ul>
	* <li>begin: Begin transaction</li>
	* <li>commit: Commit/end transaction</li>
	* <li>rollback: Rollback transaction</li>
	* </ul>
	*
	* @param string	$status	The status code.
	*
	* @return mixed	The result returned by the DB
	* @access public
	*/
	public function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				// If we are within a transaction we will not open another one, but enclose the current one to not loose data (prevening auto commit)
				if ($this->transaction)
				{
					$this->transactions++;
					return true;
				}

				$result = $this->_sql_transaction('begin');

				if (!$result)
				{
					$this->sql_error();
				}

				$this->transaction = true;
			break;

			case 'commit':
				// If there was a previously opened transaction we do not commit yet... but count back the number of inner transactions
				if ($this->transaction && $this->transactions)
				{
					$this->transactions--;
					return true;
				}

				// Check if there is a transaction (no transaction can happen if there was an error, with a combined rollback and error returning enabled)
				// This implies we have transaction always set for autocommit db's
				if (!$this->transaction)
				{
					return false;
				}

				$result = $this->_sql_transaction('commit');

				if (!$result)
				{
					$this->sql_error();
				}

				$this->transaction = false;
				$this->transactions = 0;
			break;

			case 'rollback':
				$result = $this->_sql_transaction('rollback');
				$this->transaction = false;
				$this->transactions = 0;
			break;

			default:
				$result = $this->_sql_transaction($status);
			break;
		}

		return $result;
	}

	/**
	* Fetch current row.
	*
	* @param mixed	$query_id	Query result resource
	*
	* @return array|bool	The current row or false if an error occurred
	* @access public
	*/
	public function sql_fetchrow($query_id = false)
	{
		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($this->sql_cache_exists($query_id))
		{
			return $this->sql_cache_fetchrow($query_id);
		}

		return ($query_id !== false) ? $this->_sql_fetchrow($query_id) : false;
	}

	/**
	* Fetch rowset (all rows).
	*
	* @param mixed	$query_id	Query result resource
	*
	* @return array|bool	The complete rowset or false if an error occurred
	* @access public
	*/
	public function sql_fetchrowset($query_id = false)
	{
		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($query_id !== false)
		{
			$result = array();
			while ($row = $this->sql_fetchrow($query_id))
			{
				$result[] = $row;
			}

			return $result;
		}

		return false;
	}

	/**
	* Fetch field from current row.
	*
	* @param string	$field	Field/Column name to fetch data from.
	* @param mixed	$query_id	Query result resource
	*
	* @return mixed	The fields value
	* @access public
	*/
	public function sql_fetchfield($field, $query_id = false)
	{
		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($query_id !== false)
		{
			if ($this->sql_cache_exists($query_id))
			{
				return $this->sql_cache_fetchfield($query_id, $field);
			}

			$row = $this->sql_fetchrow($query_id);
			return (isset($row[$field])) ? $row[$field] : false;
		}

		return false;
	}

	/**
	* Free query result.
	*
	* @param mixed	$query_id	Query result resource
	*
	* @return mixed	The DB result
	* @access public
	*/
	public function sql_freeresult($query_id)
	{
		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($this->sql_cache_exists($query_id))
		{
			return $this->sql_cache_freeresult($query_id);
		}

		if (($key = $this->sql_get_result_key($query_id)) !== false)
		{
			unset($this->open_queries[$key]);
		}

		return $this->_sql_freeresult($query_id);
	}

	/**
	* Correctly adjust LIKE expression for special characters, some DBMS are handling them in a different way.
	*
	* @param string	$expression	The expression to use. Every wildcard is escaped, except {@link phpbb_dbal::$any_char $any_char} and {@link phpbb_dbal::$one_char $one_char}
	*
	* @return string	LIKE expression including the keyword!
	* @access public
	*/
	public function sql_like_expression($expression)
	{
		$expression = str_replace(array('_', '%'), array("\_", "\%"), $expression);
		$expression = str_replace(array(chr(0) . "\_", chr(0) . "\%"), array('_', '%'), $expression);

		return $this->_sql_like_expression('LIKE \'' . $this->sql_escape($expression) . '\'');
	}

	/**
	* Build sql statement from array for insert/update/select statements.
	*
	* Idea for this from Ikonboard
	* Possible query values: INSERT, INSERT_SELECT, UPDATE, SELECT
	*
	* @param string	$mode		The mode to handle
	* @param array	$assoc_ary	The SQL array to insert/update/select (key == column, value == data)
	*
	* @return string	Query able to be used in SQL queries
	* @access public
	*/
	public function sql_build_array($mode, $assoc_ary = false)
	{
		if (!is_array($assoc_ary))
		{
			return false;
		}

		$fields = $values = array();
		$query = '';

		if ($mode == 'INSERT' || $mode == 'INSERT_SELECT')
		{
			foreach ($assoc_ary as $key => $var)
			{
				$fields[] = $key;

				if (is_array($var) && is_string($var[0]))
				{
					// This is used for INSERT_SELECT(s)
					$values[] = $var[0];
				}
				else
				{
					$values[] = $this->_sql_validate_value($var);
				}
			}

			$query = ($mode == 'INSERT') ? ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')' : ' (' . implode(', ', $fields) . ') SELECT ' . implode(', ', $values) . ' ';
		}
		else if ($mode == 'MULTI_INSERT')
		{
			trigger_error('The MULTI_INSERT query value is no longer supported. Please use sql_multi_insert() instead.', E_USER_ERROR);
		}
		else if ($mode == 'UPDATE' || $mode == 'SELECT')
		{
			foreach ($assoc_ary as $key => $var)
			{
				$values[] = "$key = " . $this->_sql_validate_value($var);
			}
			$query = implode(($mode == 'UPDATE') ? ', ' : ' AND ', $values);
		}

		return $query;
	}

	/**
	* Build IN or NOT IN sql comparison string, uses <> or = on single element to improve comparison speed
	*
	* @param string	$field				Name of the sql column that shall be compared
	* @param array	$array				Array of values that are allowed (IN) or not allowed (NOT IN)
	* @param bool	$negate				True for NOT IN (), false for IN () (default)
	* @param bool	$allow_empty_set	If true, allow $array to be empty - this function will return 1=1 or 1=0 then.
	*
	* @return string	SQL statement able to be used in SQL queries
	* @access public
	*/
	public function sql_in_set($field, $array, $negate = false, $allow_empty_set = false)
	{
		if (!sizeof($array))
		{
			if (!$allow_empty_set)
			{
				// Print the backtrace to help identifying the location of the problematic code
				$this->sql_error('No values specified for SQL IN comparison');
			}
			else
			{
				// NOT IN () actually means everything so use a tautology
				if ($negate)
				{
					return '1=1';
				}
				// IN () actually means nothing so use a contradiction
				else
				{
					return '1=0';
				}
			}
		}

		if (!is_array($array))
		{
			$array = array($array);
		}

		if (sizeof($array) == 1)
		{
			@reset($array);
			$var = current($array);

			return $field . ($negate ? ' <> ' : ' = ') . $this->_sql_validate_value($var);
		}
		else
		{
			return $field . ($negate ? ' NOT IN ' : ' IN ') . '(' . implode(', ', array_map(array($this, '_sql_validate_value'), $array)) . ')';
		}
	}

	/**
	* Run more than one insert statement.
	*
	* @param string	$table		Table name to run the statements on
	* @param array	&$sql_ary	Multi-dimensional array holding the statement data.
	*
	* @return bool	False if no statements were executed.
	* @access public
	* @todo use sql_prepare_data()
	*/
	public function sql_multi_insert($table, &$sql_ary)
	{
		if (!sizeof($sql_ary))
		{
			return false;
		}

		if ($this->features['multi_insert'])
		{
			$ary = array();
			foreach ($sql_ary as $id => $_sql_ary)
			{
				// If by accident the sql array is only one-dimensional we build a normal insert statement
				if (!is_array($_sql_ary))
				{
					$this->sql_query('INSERT INTO ' . $table . ' ' . $this->sql_build_array('INSERT', $sql_ary));
					return true;
				}

				$values = array();
				foreach ($_sql_ary as $key => $var)
				{
					$values[] = $this->_sql_validate_value($var);
				}
				$ary[] = '(' . implode(', ', $values) . ')';
			}

			$this->sql_query('INSERT INTO ' . $table . ' ' . ' (' . implode(', ', array_keys($sql_ary[0])) . ') VALUES ' . implode(', ', $ary));
		}
		else
		{
			foreach ($sql_ary as $ary)
			{
				if (!is_array($ary))
				{
					return false;
				}

				$this->sql_query('INSERT INTO ' . $table . ' ' . $this->sql_build_array('INSERT', $ary));
			}
		}

		return true;
	}

	/**
	* Build sql statement from array for select and select distinct statements
	*
	* @todo add more in-depth explanation about all possible array keys and their effects.
	*
	* @param string	$query	Query value. Possible query values: SELECT, SELECT_DISTINCT
	* @param string	$array	Array to build statement from
	*
	* @return string	SQL Statement
	* @access public
	*/
	public function sql_build_query($query, $array)
	{
		$sql = '';
		switch ($query)
		{
			case 'SELECT':
			case 'SELECT_DISTINCT';

				$sql = str_replace('_', ' ', $query) . ' ' . $array['SELECT'] . ' FROM ';

				$table_array = array();
				foreach ($array['FROM'] as $table_name => $alias)
				{
					if (is_array($alias))
					{
						foreach ($alias as $multi_alias)
						{
							$table_array[] = $table_name . ' ' . $multi_alias;
						}
					}
					else
					{
						$table_array[] = $table_name . ' ' . $alias;
					}
				}

				$sql .= $this->_sql_custom_build('FROM', implode(', ', $table_array));

				if (!empty($array['LEFT_JOIN']))
				{
					foreach ($array['LEFT_JOIN'] as $join)
					{
						$sql .= ' LEFT JOIN ' . key($join['FROM']) . ' ' . current($join['FROM']) . ' ON (' . $join['ON'] . ')';
					}
				}

				if (!empty($array['WHERE']))
				{
					$sql .= ' WHERE ' . $this->_sql_custom_build('WHERE', $array['WHERE']);
				}

				if (!empty($array['GROUP_BY']))
				{
					$sql .= ' GROUP BY ' . $array['GROUP_BY'];
				}

				if (!empty($array['ORDER_BY']))
				{
					$sql .= ' ORDER BY ' . $array['ORDER_BY'];
				}

			break;
		}

		return $sql;
	}

	/**
	* Display SQL Error message.
	*
	* The DB-specific information is retrieved by {@link phpbb_dbal::_sql_error() _sql_error()}.
	*
	* @param string	$sql	SQL statement which triggered the error
	*
	* @return mixed	Returns sql error array if {@link phpbb_dbal::$return_on_error $return_on_error} is true. Else script is halted.
	* @access public
	*/
	public function sql_error($sql = '')
	{
		// Set var to retrieve errored status
		$this->sql_error_triggered = true;
		$this->sql_error_sql = $sql;

		$this->sql_error_returned = $this->_sql_error();

		if (!$this->return_on_error)
		{
			$sql_message = $this->sql_error_returned['message'];
			$sql_code = $this->sql_error_returned['code'];

			$message = 'SQL ERROR [ ' . $this->sql_layer . ' ]' . (($sql_message) ? '<br /><br />' . $sql_message : '') . (($sql_code) ? ' [' . $sql_code . ']' : '');

			// Show complete SQL error and path to administrators only
			// Additionally show complete error on installation or if extended debug mode is enabled
			// The phpbb::$base_config['debug_extra'] variable is for development only!
			if ((phpbb::registered('acl') && phpbb::$acl->acl_get('a_')) || defined('IN_INSTALL') || phpbb::$base_config['debug_extra'])
			{
				$message .= ($sql) ? '<br /><br />SQL<br /><br />' . htmlspecialchars($sql) : '';
				$message .= '<br />';
			}
			else
			{
				// If error occurs in initiating the session we need to use a pre-defined language string
				// This could happen if the connection could not be established for example (then we are not able to grab the default language)
				if (!phpbb::registered('user'))
				{
					$message .= '<br /><br />An sql error occurred while fetching this page. Please contact an administrator if this problem persists.';
				}
				else
				{
					if (!empty(phpbb::$config['board_contact']))
					{
						$message .= '<br /><br />' . phpbb::$user->lang('SQL_ERROR_OCCURRED', '<a href="mailto:' . htmlspecialchars(phpbb::$config['board_contact']) . '">', '</a>');
					}
					else
					{
						$message .= '<br /><br />' . phpbb::$user->lang('SQL_ERROR_OCCURRED', '', '');
					}
				}
			}

			if ($this->transaction)
			{
				$this->sql_transaction('rollback');
			}

			if (strlen($message) > 1024)
			{
				// We need to define $msg_long_text here to circumvent text stripping.
				global $msg_long_text;
				$msg_long_text = $message;

				trigger_error(false, E_USER_ERROR);
			}

			trigger_error($message, E_USER_ERROR);
		}

		if ($this->transaction)
		{
			$this->sql_transaction('rollback');
		}

		return $this->sql_error_returned;
	}

	/**
	* Build SQL Report to explain queries, show statistics and runtime information.
	*
	* This function only executes if the GET parameter 'explain' is true and phpbb::$base_config['debug_extra'] enabled.
	*
	* @param string	$mode	The mode to handle. 'display' is used for displaying the report, all other modes are internal.
	* @param string $query	Query to document/explain. Only used internally to build the plan.
	*
	* @access public
	*/
	public function sql_report($mode, $query = '')
	{
		global $starttime;

		if (!phpbb_request::variable('explain', false))
		{
			return false;
		}

		if (!$query && $this->query_hold != '')
		{
			$query = $this->query_hold;
		}

		switch ($mode)
		{
			case 'display':
				$this->sql_close();

				if (phpbb::registered('acm'))
				{
					phpbb::$acm->unload();
				}

				$mtime = explode(' ', microtime());
				$totaltime = $mtime[0] + $mtime[1] - $starttime;

				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
						<meta http-equiv="Content-Style-Type" content="text/css" />
						<meta http-equiv="imagetoolbar" content="no" />
						<title>SQL Report</title>
						<link href="' . PHPBB_ROOT_PATH . 'adm/style/admin.css" rel="stylesheet" type="text/css" media="screen" />
					</head>
					<body id="errorpage">
					<div id="wrap">
						<div id="page-header">
							<a href="' . phpbb::$url->build_url('explain') . '">Return to previous page</a>
						</div>
						<div id="page-body">
							<div id="acp">
							<div class="panel">
								<span class="corners-top"><span></span></span>
								<div id="content">
									<h1>SQL Report</h1>
									<br />
									<p><b>Page generated in ' . round($totaltime, 4) . " seconds with {$this->num_queries['normal']} queries" . (($this->num_queries['cached']) ? " + {$this->num_queries['cached']} " . (($this->num_queries['cached'] == 1) ? 'query' : 'queries') . ' returning data from cache' : '') . '</b></p>

									<p>Time spent on ' . $this->sql_layer . ' queries: <b>' . round($this->sql_time, 5) . 's</b> | Time spent on PHP: <b>' . round($totaltime - $this->sql_time, 5) . 's</b></p>

									<br /><br />
									' . $this->sql_report . '
								</div>
								<span class="corners-bottom"><span></span></span>
							</div>
							</div>
						</div>
						<div id="page-footer">
							Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>
						</div>
					</div>
					</body>
					</html>';

				exit_handler();

			break;

			case 'stop':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$this->sql_report .= '

					<table cellspacing="1">
					<thead>
					<tr>
						<th>Query #' . $this->num_queries['total'] . '</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td class="row3"><textarea style="font-family:\'Courier New\',monospace;width:99%" rows="5" cols="10">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td>
					</tr>
					</tbody>
					</table>

					' . $this->html_hold . '

					<p style="text-align: center;">
				';

				if ($this->query_result)
				{
					if (preg_match('/^(UPDATE|DELETE|REPLACE)/', $query))
					{
						$this->sql_report .= 'Affected rows: <b>' . $this->sql_affectedrows($this->query_result) . '</b> | ';
					}
					$this->sql_report .= 'Before: ' . sprintf('%.5f', $this->curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed: <b>' . sprintf('%.5f', $endtime - $this->curtime) . 's</b>';
				}
				else
				{
					$error = $this->sql_error();
					$this->sql_report .= '<b style="color: red">FAILED</b> - ' . $this->sql_layer . ' Error ' . $error['code'] . ': ' . htmlspecialchars($error['message']);
				}

				$this->sql_report .= '</p><br /><br />';

				$this->sql_time += $endtime - $this->curtime;
			break;

			case 'start':
				$this->query_hold = $query;
				$this->html_hold = '';

				$this->_sql_report($mode, $query);

				$this->curtime = explode(' ', microtime());
				$this->curtime = $this->curtime[0] + $this->curtime[1];

			break;

			case 'add_select_row':

				$html_table = func_get_arg(2);
				$row = func_get_arg(3);

				if (!$html_table && sizeof($row))
				{
					$html_table = true;
					$this->html_hold .= '<table cellspacing="1"><tr>';

					foreach (array_keys($row) as $val)
					{
						$this->html_hold .= '<th>' . (($val) ? ucwords(str_replace('_', ' ', $val)) : '&nbsp;') . '</th>';
					}
					$this->html_hold .= '</tr>';
				}
				$this->html_hold .= '<tr>';

				$class = 'row1';
				foreach (array_values($row) as $val)
				{
					$class = ($class == 'row1') ? 'row2' : 'row1';
					$this->html_hold .= '<td class="' . $class . '">' . (($val) ? $val : '&nbsp;') . '</td>';
				}
				$this->html_hold .= '</tr>';

				return $html_table;

			break;

			case 'fromcache':

				$this->_sql_report($mode, $query);

			break;

			case 'record_fromcache':

				$endtime = func_get_arg(2);
				$splittime = func_get_arg(3);

				$time_cache = $endtime - $this->curtime;
				$time_db = $splittime - $endtime;
				$color = ($time_db > $time_cache) ? 'green' : 'red';

				$this->sql_report .= '<table cellspacing="1"><thead><tr><th>Query results obtained from the cache</th></tr></thead><tbody><tr>';
				$this->sql_report .= '<td class="row3"><textarea style="font-family:\'Courier New\',monospace;width:99%" rows="5" cols="10">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td></tr></tbody></table>';
				$this->sql_report .= '<p style="text-align: center;">';
				$this->sql_report .= 'Before: ' . sprintf('%.5f', $this->curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed [cache]: <b style="color: ' . $color . '">' . sprintf('%.5f', ($time_cache)) . 's</b> | Elapsed [db]: <b>' . sprintf('%.5f', $time_db) . 's</b></p><br /><br />';

				// Pad the start time to not interfere with page timing
				$starttime += $time_db;

			break;

			default:

				$this->_sql_report($mode, $query);

			break;
		}

		return true;
	}

	/**
	* Get stored data from SQL cache and fill the relevant cach rowset.
	*
	* @param string	$query	The query cached.
	*
	* @return bool	True if the caching was successful, else false
	* @access private
	*/
	private function sql_get_cache($query)
	{
		if (!phpbb::registered('acm') || !phpbb::$acm->supported('sql'))
		{
			return false;
		}

		// Remove extra spaces and tabs
		$var_name = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$var_name = md5($this->sql_layer . '_' . $var_name);

		$data = phpbb::$acm->get_sql($var_name);

		if ($data !== false)
		{
			$this->query_result = ++$this->cache_index;
			$this->cache_rowset[$this->query_result] = $data['rowset'];

			return true;
		}

		return false;
	}

	/**
	* Put query to cache.
	*
	* @param string	$query		The query cached.
	* @param int	$cache_ttl	Cache lifetime in seconds.
	*
	* @return bool	True if the caching was successful, else false
	* @access private
	*/
	private function sql_put_cache($query, $cache_ttl)
	{
		if (!phpbb::registered('acm') || !phpbb::$acm->supported('sql'))
		{
			return false;
		}

		// Prepare the data
		$var_name = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$var_name = md5($this->sql_layer . '_' . $var_name);

		$data = array(
			'query'		=> $query,
			'rowset'	=> array(),
		);

		while ($row = $this->sql_fetchrow($this->query_result))
		{
			$data['rowset'][] = $row;
		}
		$this->sql_freeresult($this->query_result);

		phpbb::$acm->put_sql($var_name, $data, $cache_ttl);

		$this->query_result = ++$this->cache_index;
		$this->cache_rowset[$this->query_result] = $data['rowset'];
		@reset($this->cache_rowset[$this->query_result]);

		return true;
	}

	/**
	* Check if an sql cache exist for a specific query id.
	*
	* @param int	$query_id	The query_id to check (int)
	*
	* @return bool	True if an cache entry exists.
	* @access private
	*/
	private function sql_cache_exists($query_id)
	{
		return is_int($query_id) && isset($this->cache_rowset[$query_id]);
	}

	/**
	* Fetch row from cache (database). Used in {@link phpbb_dbal::sql_fetchrow() sql_fetchrow()}.
	*
	* @param int	$query_id	The query_id to fetch from.
	*
	* @return array	The result row
	* @access private
	*/
	private function sql_cache_fetchrow($query_id)
	{
		list(, $row) = each($this->cache_rowset[$query_id]);
		return ($row !== NULL) ? $row : false;
	}

	/**
	* Fetch a field from the current row of a cached database result (database). Used in {@link phpbb_dbal::sql_fetchfield() sql_fetchfield()}.
	*
	* @param int	$query_id	The query_id to fetch from.
	* @param string	$field		The column name.
	*
	* @return array	The field data
	* @access private
	*/
	private function sql_cache_fetchfield($query_id, $field)
	{
		$row = current($this->cache_rowset[$query_id]);
		return ($row !== false && isset($row[$field])) ? $row[$field] : false;
	}

	/**
	* Free memory used for a cached database result (database). Used in {@link phpbb_dbal::sql_freeresult() sql_freeresult()}.
	*
	* @param int	$query_id	The query_id.
	*
	* @return bool	True on success
	* @access private
	*/
	private function sql_cache_freeresult($query_id)
	{
		if (!isset($this->cache_rowset[$query_id]))
		{
			return false;
		}

		if (($key = $this->sql_get_result_key($query_id)) !== false)
		{
			unset($this->open_queries[$key]);
		}

		unset($this->cache_rowset[$query_id]);
		return true;
	}

	/**
	* Function for validating SQL values
	*
	* @param mixed	Value. Typecasted to it's type.
	*
	* @return mixed	Typecasted value.
	* @access private
	*/
	private function _sql_validate_value($var)
	{
		if (is_null($var))
		{
			return 'NULL';
		}
		else if (is_string($var))
		{
			return "'" . $this->sql_escape($var) . "'";
		}
		else
		{
			return (is_bool($var)) ? intval($var) : $var;
		}
	}

	/**
	* Add "one" to query count.
	*
	* @param bool	$cached	If true, add one to cached query count. Otherwise to non-cached query count
	* @access private
	*/
	private function sql_add_num_queries($cached = false)
	{
		$this->num_queries['cached'] += ($cached !== false) ? 1 : 0;
		$this->num_queries['normal'] += ($cached !== false) ? 0 : 1;
		$this->num_queries['total'] += 1;
	}

	/**
	* Get SQL result key for storing open connection
	*
	* @param string	$query_result	Query result id/resource/object
	*
	* @return mixed	Key usable as array key. False if storing is not possible.
	* @access private
	*/
	private function sql_get_result_key($query_result)
	{
		$key = $query_result;

		if (is_object($query_result))
		{
			$key = false;
		}
		else if (is_resource($query_result))
		{
			$key = (int) $query_result;
		}

		return $key;
	}
}

?>