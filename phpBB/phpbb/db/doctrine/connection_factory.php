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
use phpbb\db\middleware\mysql\middleware as mysql_middleware;
use phpbb\db\middleware\oracle\middleware as oracle_middleware;
use phpbb\db\middleware\postgresql\middleware as postgresql_middleware;
use phpbb\db\middleware\sqlsrv\middleware as sqlsrv_middleware;
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
			$driver = self::select_doctrine_driver($driver, $available_drivers);
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
			'pdo_mysql', 'mysqli'	=> [new mysql_middleware()],
			'pdo_oci', 'oci8'		=> [new oracle_middleware()],
			'pdo_pgsql', 'pgsql'	=> [new postgresql_middleware()],
			'pdo_sqlsrv', 'sqlsrv'	=> [new sqlsrv_middleware()],
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
			$previous = $e instanceof \Exception
				? $e
				: new \RuntimeException($e->getMessage(), $e->getCode(), $e);
			throw new runtime_exception('DB_CONNECTION_FAILED', [], $previous);
		}
	}

	/**
	 * Selects a Doctrine DBAL driver for a phpBB database driver.
	 *
	 * Prefers the PDO variant and falls back to the corresponding native
	 * driver when the PDO variant is not available. This allows phpBB to
	 * run without the PDO extension as long as a native driver is loaded.
	 *
	 * @param string	$driver_name		phpBB database driver name.
	 * @param array		$available_drivers	Doctrine DBAL drivers available in the current PHP build.
	 *
	 * @return string Doctrine DBAL's driver name.
	 *
	 * @throws InvalidArgumentException If $driver_name is not a valid phpBB database driver.
	 */
	private static function select_doctrine_driver(string $driver_name, array $available_drivers): string
	{
		// Normalize driver name.
		$name = str_replace('phpbb\db\driver', '', $driver_name);
		$name = preg_replace('/mysql$/i', 'mysqli', $name);
		$name = trim($name, '\\');

		// List Doctrine drivers in order of preference (PDO variant first,
		// native driver as fallback).
		$candidates = match ($name)
		{
			'mssql_odbc', 'mssqlnative'	=> ['pdo_sqlsrv', 'sqlsrv'],
			'mysqli'					=> ['pdo_mysql', 'mysqli'],
			'oracle'					=> ['pdo_oci', 'oci8'],
			'postgres'					=> ['pdo_pgsql', 'pgsql'],
			'sqlite3'					=> ['pdo_sqlite', 'sqlite3'],
			default						=> throw new InvalidArgumentException('Invalid phpBB database driver provided: ' . $driver_name),
		};

		foreach ($candidates as $candidate)
		{
			if (in_array($candidate, $available_drivers, true))
			{
				return $candidate;
			}
		}

		// No driver is available. Return the preferred one and let the
		// connection attempt report the error.
		return $candidates[0];
	}

	/*
	 * Disable constructor.
	 */
	private function __construct()
	{
	}
}
