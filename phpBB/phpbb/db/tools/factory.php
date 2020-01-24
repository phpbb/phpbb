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

namespace phpbb\db\tools;

/**
 * A factory which serves the suitable tools instance for the given dbal
 */
class factory
{
	/**
	 * @param mixed $db_driver
	 * @param bool $return_statements
	 * @return \phpbb\db\tools\tools_interface
	 */
	public function get($db_driver, $return_statements = false)
	{
		$platform = $db_driver->get_sql_layer();
		switch ($platform)
		{
			case 'mssqlnative':
				return new \phpbb\db\tools\mssql($db_driver, $return_statements);
			case 'postgres':
				return new \phpbb\db\tools\postgres($db_driver, $return_statements);
			case 'mysqli':
			case 'oracle':
			case 'sqlite3':
				return new \phpbb\db\tools\tools($db_driver, $return_statements);
			default:
				throw new \InvalidArgumentException('Invalid database driver given');
		}
	}
}
