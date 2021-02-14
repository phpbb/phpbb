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

namespace phpbb\db\doctrine;

use InvalidArgumentException;

/**
 * Driver convertor utility for Doctrine DBAL.
 */
trait driver_convertor
{
	/**
	 * Converts phpBB driver names to Doctrine's equivalent.
	 *
	 * @param string $driver_name phpBB database driver name.
	 *
	 * @return string Doctrine DBAL's driver name.
	 *
	 * @throws InvalidArgumentException If $driver_name is not a valid phpBB database driver.
	 */
	public static function to_doctrine_driver(string $driver_name) : string
	{
		// Normalize driver name.
		$name = str_replace('phpbb\db\driver', '', $driver_name);
		$name = preg_replace('/mysql$/i', 'mysqli', $name);
		$name = trim($name, '\\');

		switch ($name)
		{
			case 'mssql_odbc':
			case 'mssqlnative':
				$name = 'pdo_sqlsrv';
			break;

			case 'mysqli':
				$name = 'pdo_mysql';
			break;

			case 'oracle':
				$name = 'oci8';
			break;

			case 'postgres':
				$name = 'pdo_pgsql';
			break;

			case 'sqlite3':
				$name = 'pdo_sqlite';
			break;

			default:
				throw new InvalidArgumentException('Invalid phpBB database driver provided: ' . $driver_name);
		}

		return $name;
	}
}
