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
* MSSQL Database Base Abstraction Layer
 */
abstract class mssql_base extends \phpbb\db\driver\driver
{
	/**
	* {@inheritDoc}
	*/
	public function sql_concatenate($expr1, $expr2)
	{
		return $expr1 . ' + ' . $expr2;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_escape($msg)
	{
		return str_replace(array("'", "\0"), array("''", ''), $msg);
	}

	/**
	* {@inheritDoc}
	*/
	function sql_lower_text($column_name)
	{
		return "LOWER(SUBSTRING($column_name, 1, DATALENGTH($column_name)))";
	}

	/**
	* {@inheritDoc}
	*/
	protected function _sql_like_expression(string $expression): string
	{
		return $expression . " ESCAPE '\\'";
	}

	/**
	* {@inheritDoc}
	*/
	protected function _sql_not_like_expression(string $expression): string
	{
		return $expression . " ESCAPE '\\'";
	}

	/**
	* {@inheritDoc}
	*/
	function cast_expr_to_bigint($expression)
	{
		return 'CONVERT(BIGINT, ' . $expression . ')';
	}

	/**
	* {@inheritDoc}
	*/
	function sql_quote($msg)
	{
		return '"' . $msg . '"';
	}
}
