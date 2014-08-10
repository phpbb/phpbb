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
* Abstract MySQL Database Base Abstraction Layer
*/
abstract class mysql_base extends \phpbb\db\driver\driver
{
	/**
	* {@inheritDoc}
	*/
	public function sql_concatenate($expr1, $expr2)
	{
		return 'CONCAT(' . $expr1 . ', ' . $expr2 . ')';
	}

	/**
	* Build LIMIT query
	*/
	function _sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$this->query_result = false;

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
	* {@inheritDoc}
	*/
	function get_estimated_row_count($table_name)
	{
		$table_status = $this->get_table_status($table_name);

		if (isset($table_status['Engine']))
		{
			if ($table_status['Engine'] === 'MyISAM')
			{
				return $table_status['Rows'];
			}
			else if ($table_status['Engine'] === 'InnoDB' && $table_status['Rows'] > 100000)
			{
				return '~' . $table_status['Rows'];
			}
		}

		return parent::get_row_count($table_name);
	}

	/**
	* {@inheritDoc}
	*/
	function get_row_count($table_name)
	{
		$table_status = $this->get_table_status($table_name);

		if (isset($table_status['Engine']) && $table_status['Engine'] === 'MyISAM')
		{
			return $table_status['Rows'];
		}

		return parent::get_row_count($table_name);
	}

	/**
	* Gets some information about the specified table.
	*
	* @param string $table_name		Table name
	*
	* @return array
	*
	* @access protected
	*/
	function get_table_status($table_name)
	{
		$sql = "SHOW TABLE STATUS
			LIKE '" . $this->sql_escape($table_name) . "'";
		$result = $this->sql_query($sql);
		$table_status = $this->sql_fetchrow($result);
		$this->sql_freeresult($result);

		return $table_status;
	}

	/**
	* Build LIKE expression
	* @access private
	*/
	function _sql_like_expression($expression)
	{
		return $expression;
	}

	/**
	* Build NOT LIKE expression
	* @access private
	*/
	function _sql_not_like_expression($expression)
	{
		return $expression;
	}

	/**
	* Build db-specific query data
	* @access private
	*/
	function _sql_custom_build($stage, $data)
	{
		switch ($stage)
		{
			case 'FROM':
				$data = '(' . $data . ')';
			break;
		}

		return $data;
	}
}
