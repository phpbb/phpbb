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
* Unified ODBC functions
* Unified ODBC functions support any database having ODBC driver, for example Adabas D, IBM DB2, iODBC, Solid, Sybase SQL Anywhere...
* Here we only support MSSQL Server 2000+ because of the provided schema
*
* @note number of bytes returned for returning data depends on odbc.defaultlrl php.ini setting.
* If it is limited to 4K for example only 4K of data is returned max, resulting in incomplete theme data for example.
* @note odbc.defaultbinmode may affect UTF8 characters
*
* @deprecated 4.0.0-dev The driver interface is deprecated, please use Doctrine DBAL directly instead.
*/
class mssql_odbc extends doctrine
{
	/**
	 * {@inheritdoc}
	 */
	public function get_sql_layer()
	{
		return 'mssqlnative';
	}
}
