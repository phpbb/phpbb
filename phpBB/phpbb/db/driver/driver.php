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

/**
* Database Abstraction Layer
*/
abstract class driver implements driver_interface
{
	var $db_connect_id;
	var $query_result;
	var $return_on_error = false;
	var $transaction = false;
	var $sql_time = 0;
	var $num_queries = array();
	var $open_queries = array();

	var $curtime = 0;
	var $query_hold = '';
	var $html_hold = '';
	var $sql_report = '';

	var $persistency = false;
	var $user = '';
	var $server = '';
	var $dbname = '';

	// Set to true if error triggered
	var $sql_error_triggered = false;

	// Holding the last sql query on sql error
	var $sql_error_sql = '';
	// Holding the error information - only populated if sql_error_triggered is set
	var $sql_error_returned = array();

	// Holding transaction count
	var $transactions = 0;

	// Supports multi inserts?
	var $multi_insert = false;

	/**
	* Current sql layer
	*/
	var $sql_layer = '';

	/**
	* Wildcards for matching any (%) or exactly one (_) character within LIKE expressions
	*/
	var $any_char;
	var $one_char;

	/**
	* Exact version of the DBAL, directly queried
	*/
	var $sql_server_version = false;

	const LOGICAL_OP = 0;
	const STATEMENTS = 1;
	const LEFT_STMT = 0;
	const COMPARE_OP = 1;
	const RIGHT_STMT = 2;
	const SUBQUERY_OP = 3;
	const SUBQUERY_SELECT_TYPE = 4;
	const SUBQUERY_BUILD = 5;

	/**
	* Constructor
	*/
	function __construct()
	{
		$this->num_queries = array(
			'cached'	=> 0,
			'normal'	=> 0,
			'total'		=> 0,
		);

		// Fill default sql layer based on the class being called.
		// This can be changed by the specified layer itself later if needed.
		$this->sql_layer = substr(get_class($this), strlen('phpbb\db\driver\\'));

		// Do not change this please! This variable is used to easy the use of it - and is hardcoded.
		$this->any_char = chr(0) . '%';
		$this->one_char = chr(0) . '_';
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_layer()
	{
		return $this->sql_layer;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_db_name()
	{
		return $this->dbname;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_any_char()
	{
		return $this->any_char;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_one_char()
	{
		return $this->one_char;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_db_connect_id()
	{
		return $this->db_connect_id;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_error_triggered()
	{
		return $this->sql_error_triggered;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_error_sql()
	{
		return $this->sql_error_sql;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_transaction()
	{
		return $this->transaction;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_time()
	{
		return $this->sql_time;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_error_returned()
	{
		return $this->sql_error_returned;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_multi_insert()
	{
		return $this->multi_insert;
	}

	/**
	* {@inheritdoc}
	*/
	public function set_multi_insert($multi_insert)
	{
		$this->multi_insert = $multi_insert;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_return_on_error($fail = false)
	{
		$this->sql_error_triggered = false;
		$this->sql_error_sql = '';

		$this->return_on_error = $fail;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_num_queries($cached = false)
	{
		return ($cached) ? $this->num_queries['cached'] : $this->num_queries['normal'];
	}

	/**
	* {@inheritDoc}
	*/
	function sql_add_num_queries($cached = false)
	{
		$this->num_queries['cached'] += ($cached !== false) ? 1 : 0;
		$this->num_queries['normal'] += ($cached !== false) ? 0 : 1;
		$this->num_queries['total'] += 1;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_close()
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

		foreach ($this->open_queries as $query_id)
		{
			$this->sql_freeresult($query_id);
		}

		// Connection closed correctly. Set db_connect_id to false to prevent errors
		if ($result = $this->_sql_close())
		{
			$this->db_connect_id = false;
		}

		return $result;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
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
	* {@inheritDoc}
	*/
	function sql_fetchrowset($query_id = false)
	{
		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
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
	* {@inheritDoc}
	*/
	function sql_rowseek($rownum, &$query_id)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($cache && $cache->sql_exists($query_id))
		{
			return $cache->sql_rowseek($rownum, $query_id);
		}

		if (!$query_id)
		{
			return false;
		}

		$this->sql_freeresult($query_id);
		$query_id = $this->sql_query($this->last_query_text);

		if (!$query_id)
		{
			return false;
		}

		// We do not fetch the row for rownum == 0 because then the next resultset would be the second row
		for ($i = 0; $i < $rownum; $i++)
		{
			if (!$this->sql_fetchrow($query_id))
			{
				return false;
			}
		}

		return true;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_fetchfield($field, $rownum = false, $query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($query_id)
		{
			if ($rownum !== false)
			{
				$this->sql_rowseek($rownum, $query_id);
			}

			if ($cache && !is_object($query_id) && $cache->sql_exists($query_id))
			{
				return $cache->sql_fetchfield($query_id, $field);
			}

			$row = $this->sql_fetchrow($query_id);
			return (isset($row[$field])) ? $row[$field] : false;
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_like_expression($expression)
	{
		$expression = str_replace(array('_', '%'), array("\_", "\%"), $expression);
		$expression = str_replace(array(chr(0) . "\_", chr(0) . "\%"), array('_', '%'), $expression);

		return $this->_sql_like_expression('LIKE \'' . $this->sql_escape($expression) . '\'');
	}

	/**
	* {@inheritDoc}
	*/
	function sql_not_like_expression($expression)
	{
		$expression = str_replace(array('_', '%'), array("\_", "\%"), $expression);
		$expression = str_replace(array(chr(0) . "\_", chr(0) . "\%"), array('_', '%'), $expression);

		return $this->_sql_not_like_expression('NOT LIKE \'' . $this->sql_escape($expression) . '\'');
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_case($condition, $action_true, $action_false = false)
	{
		$sql_case = 'CASE WHEN ' . $condition;
		$sql_case .= ' THEN ' . $action_true;
		$sql_case .= ($action_false !== false) ? ' ELSE ' . $action_false : '';
		$sql_case .= ' END';
		return $sql_case;
	}

	/**
	* {@inheritDoc}
	*/
	public function sql_concatenate($expr1, $expr2)
	{
		return $expr1 . ' || ' . $expr2;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_buffer_nested_transactions()
	{
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				// If we are within a transaction we will not open another one, but enclose the current one to not loose data (preventing auto commit)
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
				// If there was a previously opened transaction we do not commit yet...
				// but count back the number of inner transactions
				if ($this->transaction && $this->transactions)
				{
					$this->transactions--;
					return true;
				}

				// Check if there is a transaction (no transaction can happen if
				// there was an error, with a combined rollback and error returning enabled)
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
	* {@inheritDoc}
	*/
	function sql_build_array($query, $assoc_ary = false)
	{
		if (!is_array($assoc_ary))
		{
			return false;
		}

		$fields = $values = array();

		if ($query == 'INSERT' || $query == 'INSERT_SELECT')
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

			$query = ($query == 'INSERT') ? ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')' : ' (' . implode(', ', $fields) . ') SELECT ' . implode(', ', $values) . ' ';
		}
		else if ($query == 'MULTI_INSERT')
		{
			trigger_error('The MULTI_INSERT query value is no longer supported. Please use sql_multi_insert() instead.', E_USER_ERROR);
		}
		else if ($query == 'UPDATE' || $query == 'SELECT' || $query == 'DELETE')
		{
			$values = array();
			foreach ($assoc_ary as $key => $var)
			{
				$values[] = "$key = " . $this->_sql_validate_value($var);
			}
			$query = implode(($query == 'UPDATE') ? ', ' : ' AND ', $values);
		}

		return $query;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_in_set($field, $array, $negate = false, $allow_empty_set = false)
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
	* {@inheritDoc}
	*/
	function sql_bit_and($column_name, $bit, $compare = '')
	{
		if (method_exists($this, '_sql_bit_and'))
		{
			return $this->_sql_bit_and($column_name, $bit, $compare);
		}

		return $column_name . ' & ' . (1 << $bit) . (($compare) ? ' ' . $compare : '');
	}

	/**
	* {@inheritDoc}
	*/
	function sql_bit_or($column_name, $bit, $compare = '')
	{
		if (method_exists($this, '_sql_bit_or'))
		{
			return $this->_sql_bit_or($column_name, $bit, $compare);
		}

		return $column_name . ' | ' . (1 << $bit) . (($compare) ? ' ' . $compare : '');
	}

	/**
	* {@inheritDoc}
	*/
	function cast_expr_to_bigint($expression)
	{
		return $expression;
	}

	/**
	* {@inheritDoc}
	*/
	function cast_expr_to_string($expression)
	{
		return $expression;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_lower_text($column_name)
	{
		return "LOWER($column_name)";
	}

	/**
	* {@inheritDoc}
	*/
	function sql_multi_insert($table, $sql_ary)
	{
		if (!sizeof($sql_ary))
		{
			return false;
		}

		if ($this->multi_insert)
		{
			$ary = array();
			foreach ($sql_ary as $id => $_sql_ary)
			{
				// If by accident the sql array is only one-dimensional we build a normal insert statement
				if (!is_array($_sql_ary))
				{
					return $this->sql_query('INSERT INTO ' . $table . ' ' . $this->sql_build_array('INSERT', $sql_ary));
				}

				$values = array();
				foreach ($_sql_ary as $key => $var)
				{
					$values[] = $this->_sql_validate_value($var);
				}
				$ary[] = '(' . implode(', ', $values) . ')';
			}

			return $this->sql_query('INSERT INTO ' . $table . ' ' . ' (' . implode(', ', array_keys($sql_ary[0])) . ') VALUES ' . implode(', ', $ary));
		}
		else
		{
			foreach ($sql_ary as $ary)
			{
				if (!is_array($ary))
				{
					return false;
				}

				$result = $this->sql_query('INSERT INTO ' . $table . ' ' . $this->sql_build_array('INSERT', $ary));

				if (!$result)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	* Function for validating values
	* @access private
	*/
	function _sql_validate_value($var)
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
	* {@inheritDoc}
	*/
	function sql_build_query($query, $array)
	{
		$sql = '';
		switch ($query)
		{
			case 'SELECT':
			case 'SELECT_DISTINCT';

				$sql = str_replace('_', ' ', $query) . ' ' . $array['SELECT'] . ' FROM ';

				// Build table array. We also build an alias array for later checks.
				$table_array = $aliases = array();
				$used_multi_alias = false;

				foreach ($array['FROM'] as $table_name => $alias)
				{
					if (is_array($alias))
					{
						$used_multi_alias = true;

						foreach ($alias as $multi_alias)
						{
							$table_array[] = $table_name . ' ' . $multi_alias;
							$aliases[] = $multi_alias;
						}
					}
					else
					{
						$table_array[] = $table_name . ' ' . $alias;
						$aliases[] = $alias;
					}
				}

				// We run the following code to determine if we need to re-order the table array. ;)
				// The reason for this is that for multi-aliased tables (two equal tables) in the FROM statement the last table need to match the first comparison.
				// DBMS who rely on this: Oracle, PostgreSQL and MSSQL. For all other DBMS it makes absolutely no difference in which order the table is.
				if (!empty($array['LEFT_JOIN']) && sizeof($array['FROM']) > 1 && $used_multi_alias !== false)
				{
					// Take first LEFT JOIN
					$join = current($array['LEFT_JOIN']);

					// Determine the table used there (even if there are more than one used, we only want to have one
					preg_match('/(' . implode('|', $aliases) . ')\.[^\s]+/U', str_replace(array('(', ')', 'AND', 'OR', ' '), '', $join['ON']), $matches);

					// If there is a first join match, we need to make sure the table order is correct
					if (!empty($matches[1]))
					{
						$first_join_match = trim($matches[1]);
						$table_array = $last = array();

						foreach ($array['FROM'] as $table_name => $alias)
						{
							if (is_array($alias))
							{
								foreach ($alias as $multi_alias)
								{
									($multi_alias === $first_join_match) ? $last[] = $table_name . ' ' . $multi_alias : $table_array[] = $table_name . ' ' . $multi_alias;
								}
							}
							else
							{
								($alias === $first_join_match) ? $last[] = $table_name . ' ' . $alias : $table_array[] = $table_name . ' ' . $alias;
							}
						}

						$table_array = array_merge($table_array, $last);
					}
				}

				$sql .= $this->_sql_custom_build('FROM', implode(' CROSS JOIN ', $table_array));

				if (!empty($array['LEFT_JOIN']))
				{
					foreach ($array['LEFT_JOIN'] as $join)
					{
						$sql .= ' LEFT JOIN ' . key($join['FROM']) . ' ' . current($join['FROM']) . ' ON (' . $join['ON'] . ')';
					}
				}

				if (!empty($array['WHERE']))
				{
					$sql .= ' WHERE ';

					if (is_array($array['WHERE']))
					{
						$sql_where = $this->_process_boolean_tree_first($array['WHERE']);
					}
					else
					{
						$sql_where = $array['WHERE'];
					}

					$sql .= $this->_sql_custom_build('WHERE', $sql_where);
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


	protected function _process_boolean_tree_first($operations_ary)
	{
		// In cases where an array exists but there is no head condition,
		// it should be because there's only 1 WHERE clause. This seems the best way to deal with it.
		if ($operations_ary[self::LOGICAL_OP] !== 'AND' &&
			$operations_ary[self::LOGICAL_OP] !== 'OR')
		{
			$operations_ary = array('AND', array($operations_ary));
		}
		return $this->_process_boolean_tree($operations_ary) . "\n";
	}

	protected function _process_boolean_tree($operations_ary)
	{
		$operation = $operations_ary[self::LOGICAL_OP];

		foreach ($operations_ary[self::STATEMENTS] as &$condition)
		{
			switch ($condition[self::LOGICAL_OP])
			{
				case 'AND':
				case 'OR':

					$condition = ' ( ' . $this->_process_boolean_tree($condition) . ') ';

				break;
				case 'NOT':

					$condition = ' NOT (' . $this->_process_boolean_tree($condition) . ') ';

				break;

				default:

					switch (sizeof($condition))
					{
						case 3:

							// Typical 3 element clause with {left hand} {operator} {right hand}
							switch ($condition[self::COMPARE_OP])
							{
								case 'IN':
								case 'NOT_IN':

									// As this is used with an IN, assume it is a set of elements for sql_in_set()
									$condition = $this->sql_in_set($condition[self::LEFT_STMT], $condition[self::RIGHT_STMT], $condition[self::COMPARE_OP] === 'NOT_IN', true);

								break;

								case 'LIKE':

									$condition = $condition[self::LEFT_STMT] . ' ' . $this->sql_like_expression($condition[self::RIGHT_STMT]) . ' ';

								break;

								case 'NOT_LIKE':

									$condition = $condition[self::LEFT_STMT] . ' ' . $this->sql_not_like_expression($condition[self::RIGHT_STMT]) . ' ';

								break;

								case 'IS_NOT':

									$condition[self::COMPARE_OP] = 'IS NOT';

								// no break
								case 'IS':

									// If the value is NULL, the string of it is the empty string ('') which is not the intended result.
									// this should solve that
									if ($condition[self::RIGHT_STMT] === null)
									{
										$condition[self::RIGHT_STMT] = 'NULL';
									}

									$condition = implode(' ', $condition);

								break;

								default:

									$condition = implode(' ', $condition);

								break;
							}

						break;

						case 5:

							// Subquery with {left hand} {operator} {compare kind} {SELECT Kind } {Sub Query}

							$condition = $condition[self::LEFT_STMT] . ' ' . $condition[self::COMPARE_OP] . ' ' . $condition[self::SUBQUERY_OP] . ' ( ';
							$condition .= $this->sql_build_query($condition[self::SUBQUERY_SELECT_TYPE], $condition[self::SUBQUERY_BUILD]);
							$condition .= ' )';

						break;

						default:
							// This is an unpredicted clause setup. Just join all elements.
							$condition = implode(' ', $condition);

						break;
					}

				break;
			}

		}

		if ($operation === 'NOT')
		{
			$operations_ary =  implode("", $operations_ary[self::STATEMENTS]);
		}
		else
		{
			$operations_ary = implode(" \n	$operation ", $operations_ary[self::STATEMENTS]);
		}

		return $operations_ary;
	}


	/**
	* {@inheritDoc}
	*/
	function sql_error($sql = '')
	{
		global $auth, $user, $config;

		// Set var to retrieve errored status
		$this->sql_error_triggered = true;
		$this->sql_error_sql = $sql;

		$this->sql_error_returned = $this->_sql_error();

		if (!$this->return_on_error)
		{
			$message = 'SQL ERROR [ ' . $this->sql_layer . ' ]<br /><br />' . $this->sql_error_returned['message'] . ' [' . $this->sql_error_returned['code'] . ']';

			// Show complete SQL error and path to administrators only
			// Additionally show complete error on installation or if extended debug mode is enabled
			// The DEBUG constant is for development only!
			if ((isset($auth) && $auth->acl_get('a_')) || defined('IN_INSTALL') || defined('DEBUG'))
			{
				$message .= ($sql) ? '<br /><br />SQL<br /><br />' . htmlspecialchars($sql) : '';
			}
			else
			{
				// If error occurs in initiating the session we need to use a pre-defined language string
				// This could happen if the connection could not be established for example (then we are not able to grab the default language)
				if (!isset($user->lang['SQL_ERROR_OCCURRED']))
				{
					$message .= '<br /><br />An sql error occurred while fetching this page. Please contact an administrator if this problem persists.';
				}
				else
				{
					if (!empty($config['board_contact']))
					{
						$message .= '<br /><br />' . sprintf($user->lang['SQL_ERROR_OCCURRED'], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>');
					}
					else
					{
						$message .= '<br /><br />' . sprintf($user->lang['SQL_ERROR_OCCURRED'], '', '');
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
	* {@inheritDoc}
	*/
	function sql_report($mode, $query = '')
	{
		global $cache, $starttime, $phpbb_root_path, $phpbb_path_helper;
		global $request;

		if (is_object($request) && !$request->variable('explain', false))
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
				if (!empty($cache))
				{
					$cache->unload();
				}
				$this->sql_close();

				$mtime = explode(' ', microtime());
				$totaltime = $mtime[0] + $mtime[1] - $starttime;

				echo '<!DOCTYPE html>
					<html dir="ltr">
					<head>
						<meta charset="utf-8">
						<meta http-equiv="X-UA-Compatible" content="IE=edge">
						<title>SQL Report</title>
						<link href="' . htmlspecialchars($phpbb_path_helper->update_web_root_path($phpbb_root_path) . $phpbb_path_helper->get_adm_relative_path()) . 'style/admin.css" rel="stylesheet" type="text/css" media="screen" />
					</head>
					<body id="errorpage">
					<div id="wrap">
						<div id="page-header">
							<a href="' . build_url('explain') . '">Return to previous page</a>
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
							Powered by <a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited
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
						$this->sql_report .= 'Affected rows: <b>' . $this->sql_affectedrows() . '</b> | ';
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
	* {@inheritDoc}
	*/
	function get_estimated_row_count($table_name)
	{
		return $this->get_row_count($table_name);
	}

	/**
	* {@inheritDoc}
	*/
	function get_row_count($table_name)
	{
		$sql = 'SELECT COUNT(*) AS rows_total
			FROM ' . $this->sql_escape($table_name);
		$result = $this->sql_query($sql);
		$rows_total = $this->sql_fetchfield('rows_total');
		$this->sql_freeresult($result);

		return $rows_total;
	}
}
