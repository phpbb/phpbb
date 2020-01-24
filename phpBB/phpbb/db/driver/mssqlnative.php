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
 * This is the MS SQL Server Native database abstraction layer.
 * PHP mssql native driver required.
 * @author Chris Pucci
 *
 * @deprecated 4.0.0-dev The driver interface is deprecated, please use Doctrine DBAL directly instead.
 */
class mssqlnative extends doctrine
{
	/**
	 * {@inheritdoc}
	 */
	public function get_sql_layer()
	{
		return 'mssqlnative';
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
