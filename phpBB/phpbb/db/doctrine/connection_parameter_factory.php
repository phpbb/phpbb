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
use phpbb\db\doctrine\oci8\driver as oci8_driver;

/**
 * Helper class to generate Doctrine DBAL configuration.
 */
class connection_parameter_factory
{
	/**
	 * Returns configuration options for Doctrine DBAL.
	 *
	 * @param string		$driver		Driver name.
	 * @param string|null	$host		Hostname.
	 * @param string|null	$user		Username.
	 * @param string|null	$password	Password.
	 * @param string|null	$name		Database name.
	 * @param string|null	$port		Database port.
	 *
	 * @return array Doctrine DBAL connection parameters.
	 *
	 * @throws InvalidArgumentException If a required parameter is empty or null.
	 */
	public static function get_configuration(
		string $driver,
		?string $host = null,
		?string $user = null,
		?string $password = null,
		?string $name = null,
		?string $port = null) : array
	{
		$params = [
			'driver' => $driver,
		];

		return self::build_connection_parameters(
			$params,
			$host,
			$user,
			$password,
			$name,
			$port
		);
	}

	/**
	 * Build Doctrine configuration array.
	 *
	 * @param array			$params		Parameter array.
	 * @param string|null	$host		Database hostname.
	 * @param string|null	$user		Username.
	 * @param string|null	$password	Password.
	 * @param string|null	$name		Database name.
	 * @param string|null	$port		Database port.
	 *
	 * @return array Doctrine's DBAL configuration for SQLite.
	 *
	 * @throws InvalidArgumentException If a required parameter is empty or null.
	 */
	private static function build_connection_parameters(
		array $params,
		?string $host = null,
		?string $user = null,
		?string $password = null,
		?string $name = null,
		?string $port = null) : array
	{
		if ($params['driver'] === 'pdo_sqlite')
		{
			return self::enrich_parameters(
				self::build_sqlite_parameters($params, $host, $user, $password)
			);
		}

		if (empty($user) || empty($name))
		{
			throw new InvalidArgumentException('Required database parameter is not set.');
		}

		$params = array_merge($params, [
			'host'		=> $host,
			'user'		=> $user,
			'dbname'	=> $name,
		]);

		if (!empty($password))
		{
			$params['password'] = $password;
		}

		if (!empty($port))
		{
			$params['port'] = (int) $port;
		}

		return self::enrich_parameters($params);
	}

	/**
	 * Build configuration array for SQLite.
	 *
	 * @param array			$params		Parameter array.
	 * @param string		$path		Path to the database.
	 * @param string|null	$user		Username.
	 * @param string|null	$password	Password.
	 *
	 * @return array Doctrine's DBAL configuration for SQLite.
	 */
	private static function build_sqlite_parameters(array $params, string $path, ?string $user, ?string $password) : array
	{
		$params['path'] = $path;

		if (!empty($user))
		{
			$params['user'] = $user;
		}

		if (!empty($password))
		{
			$params['password'] = $password;
		}

		return $params;
	}

	/**
	 * Add additional configuration options to the parameter list.
	 *
	 * @param array $params	The parameter list to enrich.
	 *
	 * @return array The enriched parameter list.
	 */
	private static function enrich_parameters(array $params) : array
	{
		$enrichment_tags = [
			'pdo_mysql' => [
				'charset' => 'UTF8',
			],
			'oci8' => [
				'charset' => 'UTF8',
				'platform' => new oracle_platform(),
				'driverClass' => oci8_driver::class,
			],
			'pdo_pgsql' => [
				'charset' => 'UTF8',
				'platform' => new postgresql_platform(),
			],
			'pdo_sqlsrv' => [
				'platform' => new sqlsrv_platform(),
			],
		];

		if ($params['driver'] === 'pdo_mysql')
		{
			$enrichment_tags['pdo_mysql'][\PDO::MYSQL_ATTR_FOUND_ROWS] = true;
		}

		$driver = $params['driver'];
		if (!array_key_exists($driver, $enrichment_tags))
		{
			return $params;
		}

		return array_merge($params, $enrichment_tags[$driver]);
	}

	/*
	 * Disable constructing this class.
	 */
	private function __construct()
	{
	}
}
