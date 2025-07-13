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

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use phpbb\config_php_file;
use phpbb\db\middleware\mysql\phpbb_mysql_middleware;
use phpbb\db\middleware\oracle\phpbb_oracle_middleware;
use phpbb\db\middleware\postgresql\phpbb_postgresql_middleware;
use phpbb\db\middleware\sqlsrv\phpbb_sqlsrv_middleware;
use phpbb\exception\runtime_exception;

/**
 * Doctrine DBAL connection factory.
 */
class connection_factory
{
	/**
	 * Creates a Doctrine DBAL connection from phpBB configuration.
	 *
	 * @param config_php_file $config Config PHP file wrapper.
	 *
	 * @return Connection Doctrine DBAL connection.
	 *
	 * @throws runtime_exception        If the database connection could not be established.
	 * @throws InvalidArgumentException    If the provided driver name is not a valid phpBB database driver.
	 */
	public static function get_connection(config_php_file $config): Connection
	{
		$driver = $config->get('dbms');
		$host = $config->get('dbhost');
		$user = $config->get('dbuser');
		$pass = $config->get('dbpasswd');
		$name = $config->get('dbname');
		$port = $config->get('dbport');

		return self::get_connection_from_params(
			$driver,
			$host,
			$user,
			$pass,
			$name,
			$port
		);
	}

	/**
	 * Creates a database connection from the specified parameters.
	 *
	 * @param string      $driver   Driver name.
	 * @param string      $host     Hostname.
	 * @param string|null $user     Username.
	 * @param string|null $password Password.
	 * @param string|null $name     Database name.
	 * @param string|null $port     Database port.
	 *
	 * @return Connection Doctrine DBAL connection.
	 *
	 * @throws runtime_exception        If the database connection could not be established.
	 * @throws InvalidArgumentException    If $driver is not a valid phpBB database driver.
	 */
	public static function get_connection_from_params(
		string $driver,
		string $host,
		string|null $user = null,
		string|null $password = null,
		string|null $name = null,
		string|null $port = null): Connection
	{
		$available_drivers = DriverManager::getAvailableDrivers();
		if (!in_array($driver, $available_drivers))
		{
			$driver = config_php_file::convert_30_dbms_to_31($driver);
			$driver = self::to_doctrine_driver($driver);
		}

		$params = connection_parameter_factory::get_configuration(
			$driver,
			$host,
			$user,
			$password,
			$name,
			$port
		);

		$middleware = match($driver)
		{
			'pdo_mysql', 'mysqli'	=> [new phpbb_mysql_middleware()],
			'pdo_oci', 'oci8'		=> [new phpbb_oracle_middleware()],
			'pdo_pgsql', 'pgsql'	=> [new phpbb_postgresql_middleware()],
			'pdo_sqlsrv', 'sqlsrv'	=> [new phpbb_sqlsrv_middleware()],
			default	=> [],
		};

		try
		{
			$connection_config = new Configuration();
			$connection_config->setMiddlewares($middleware);

			$connection = DriverManager::getConnection($params, $connection_config);
			if (!Type::hasType(case_insensitive_string::CASE_INSENSITIVE_STRING))
			{
				Type::addType(case_insensitive_string::CASE_INSENSITIVE_STRING, case_insensitive_string::class);
			}
			$connection->getDatabasePlatform()->registerDoctrineTypeMapping('varchar_ci', case_insensitive_string::CASE_INSENSITIVE_STRING);
			return $connection;
		}
		catch (Exception $e)
		{
			throw new runtime_exception('DB_CONNECTION_FAILED', [], $e);
		}
	}

	/**
	 * Converts phpBB driver names to Doctrine's equivalent.
	 *
	 * @param string $driver_name phpBB database driver name.
	 *
	 * @return string Doctrine DBAL's driver name.
	 *
	 * @throws InvalidArgumentException If $driver_name is not a valid phpBB database driver.
	 */
	private static function to_doctrine_driver(string $driver_name): string
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

	/*
	 * Disable constructor.
	 */
	private function __construct()
	{
	}
}
