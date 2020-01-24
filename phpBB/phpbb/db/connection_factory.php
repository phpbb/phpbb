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

namespace phpbb\db;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Mysqli\MysqliConnection;
use Doctrine\DBAL\DriverManager;
use PDO;
use phpbb\config_php_file;
use phpbb\db\exception\connection_failed;
use phpbb\db\exception\invalid_database_type;

/**
 * Database connection factory.
 */
class connection_factory
{
	/**
	 * Returns a Doctrine DBAL connection.
	 *
	 * @param config_php_file $config Configuration parameters.
	 *
	 * @return Connection Database connection object.
	 *
	 * @throws invalid_database_type If the specified DB type is not supported.
	 * @throws connection_failed If the database connection could not be established.
	 */
	public static function get_connection(config_php_file $config)
	{
		$dbms = self::get_driver_name($config->convert_30_dbms_to_31($config->get('dbms')));
		$db_host = $config->get('dbhost');
		$db_user = $config->get('dbuser');
		$db_pass = $config->get('dbpasswd');
		$db_name = $config->get('dbname');
		$db_port = $config->get('dbport');

		return self::get_connection_from_params($dbms, $db_host, $db_port, $db_user, $db_pass, $db_name);
	}

	/**
	 * Returns a Doctrine DBAL connection.
	 *
	 * @param string $dbms		DBMS name.
	 * @param string $db_host	DBMS host address.
	 * @param string $db_port	DBMS port.
	 * @param string $db_user	DBMS username.
	 * @param string $db_pass	DBMS password.
	 * @param string $db_name	DBMS database name.
	 *
	 * @return Connection Database connection object.
	 *
	 * @throws invalid_database_type If the specified DB type is not supported.
	 * @throws connection_failed If the database connection could not be established.
	 */
	public static function get_connection_from_params($dbms, $db_host, $db_port, $db_user, $db_pass, $db_name)
	{
		$dbms = self::get_driver_name($dbms);
		$params = self::get_connection_params($dbms, $db_host, $db_port, $db_user, $db_pass, $db_name);

		if (strpos($dbms, 'mysql') !== false)
		{
			if ($dbms === 'mysqli')
			{
				$params['driverOptions'][MysqliConnection::OPTION_FLAGS] = MYSQLI_CLIENT_FOUND_ROWS;
			}
			else
			{
				$params['driverOptions'] = [
					PDO::MYSQL_ATTR_FOUND_ROWS => true,
				];
			}
		}

		try
		{
			return DriverManager::getConnection($params);
		}
		catch (DBALException $e)
		{
			throw new connection_failed($e);
		}
	}

	/**
	 * Builds connection parameters for Doctrine.
	 *
	 * @param string $dbms		DBMS name.
	 * @param string $db_host	DBMS host address.
	 * @param string $db_port	DBMS port.
	 * @param string $db_user	DBMS username.
	 * @param string $db_pass	DBMS password.
	 * @param string $db_name	DBMS database name.
	 *
	 * @return array An array of connection parameters.
	 *
	 * @throws invalid_database_type If the specified DB type is not supported.
	 */
	private static function get_connection_params($dbms, $db_host, $db_port, $db_user, $db_pass, $db_name)
	{
		$params = [
			'driver' => $dbms,
		];

		if ($dbms === 'pdo_sqlite')
		{
			$params['path'] = $db_host;

			if (!empty($db_user))
			{
				$params['user'] = $db_user;
			}

			if (!empty($db_pass))
			{
				$params['password'] = $db_pass;
			}

			return $params;
		}

		$doctrine_drivers = DriverManager::getAvailableDrivers();
		if (!in_array($dbms, $doctrine_drivers))
		{
			throw new invalid_database_type();
		}

		$params = array_merge($params, [
			'dbname'	=> $db_name,
			'user'		=> $db_user,
			'password'	=> $db_pass,
			'host'		=> $db_host,
		]);

		if (!empty($db_port))
		{
			$params['port'] = (int) $db_port;
		}

		return $params;
	}

	/**
	 * Converts legacy driver definitions to driver names.
	 *
	 * @param string $dbms	The driver name in the config file.
	 *
	 * @return string The driver to use.
	 *
	 * @throws invalid_database_type If the specified DB type is not supported.
	 */
	private static function get_driver_name(string $dbms)
	{
		$doctrine_drivers = DriverManager::getAvailableDrivers();
		if (in_array($dbms, $doctrine_drivers))
		{
			return $dbms;
		}

		$dbms = str_replace('phpbb\db\driver', '', $dbms);
		$dbms = preg_replace('/mysql$/i', 'mysqli', $dbms);
		$dbms = trim($dbms, '\\');

		switch ($dbms)
		{
			case 'mssql_odbc':
			case 'mssqlnative':
				$dbms = (extension_loaded('sqlsrv')) ? 'sqlsrv' : 'pdo_sqlsrv';
			break;
			case 'mysqli':
				$dbms = (extension_loaded('pdo_mysql')) ? 'pdo_mysql' : 'mysqli';
			break;
			case 'oracle':
				$dbms = (extension_loaded('oci8')) ? 'oci8' : 'pdo_oci';
			break;
			case 'postgres':
				$dbms = 'pdo_pgsql';
			break;
			case 'sqlite3':
				$dbms = 'pdo_sqlite';
			break;
			default:
				throw new invalid_database_type();
		}

		return $dbms;
	}

	/**
	 * Static class: disable the constructor.
	 */
	private function __construct()
	{
	}
}
