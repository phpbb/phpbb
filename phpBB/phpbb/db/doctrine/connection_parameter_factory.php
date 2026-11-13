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

use phpbb\db\mysql_ssl;

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
	 * @param string|null	$ssl_mode	MySQL ssl-mode, see \phpbb\db\mysql_ssl.
	 * @param string|null	$ssl_ca		CA file for verify_identity.
	 *
	 * @return array Doctrine DBAL connection parameters.
	 *
	 * @throws \InvalidArgumentException If a required parameter is empty or null, or the ssl settings are invalid.
	 */
	public static function get_configuration(
		string $driver,
		string|null $host = null,
		string|null $user = null,
		string|null $password = null,
		string|null $name = null,
		string|null $port = null,
		string|null $ssl_mode = null,
		string|null $ssl_ca = null) : array
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
			$port,
			$ssl_mode,
			$ssl_ca
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
	 * @param string|null	$ssl_mode	MySQL ssl-mode, see \phpbb\db\mysql_ssl.
	 * @param string|null	$ssl_ca		CA file for verify_identity.
	 *
	 * @return array Doctrine's DBAL configuration for SQLite.
	 *
	 * @throws \InvalidArgumentException If a required parameter is empty or null, or the ssl settings are invalid.
	 */
	private static function build_connection_parameters(
		array $params,
		string|null $host = null,
		string|null $user = null,
		string|null $password = null,
		string|null $name = null,
		string|null $port = null,
		string|null $ssl_mode = null,
		string|null $ssl_ca = null) : array
	{
		if (in_array($params['driver'], ['pdo_sqlite', 'sqlite3']))
		{
			return self::enrich_parameters(
				self::build_sqlite_parameters($params, $host, $user, $password)
			);
		}

		if (empty($user) || empty($name))
		{
			throw new \InvalidArgumentException('Required database parameter is not set.');
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

		if ($params['driver'] === 'pdo_mysql')
		{
			$params = self::add_mysql_ssl_parameters($params, $ssl_mode, $ssl_ca);
		}

		return self::enrich_parameters($params);
	}

	/**
	 * Adds the PDO options that encrypt a MySQL connection.
	 *
	 * @param array			$params		Parameter array.
	 * @param string|null	$ssl_mode	MySQL ssl-mode, see \phpbb\db\mysql_ssl.
	 * @param string|null	$ssl_ca		CA file for verify_identity.
	 *
	 * @return array Parameter array with the options added.
	 *
	 * @throws \InvalidArgumentException If the ssl settings are invalid.
	 */
	private static function add_mysql_ssl_parameters(array $params, string|null $ssl_mode, string|null $ssl_ca) : array
	{
		$mode = mysql_ssl::get_mode($ssl_mode);
		if ($mode === mysql_ssl::DISABLED)
		{
			return $params;
		}

		// PDO only starts TLS when it is given CAs, so they are set even when the certificate is not checked
		[$ca_file, $ca_path] = mysql_ssl::get_ca_locations($ssl_ca);

		$options = [mysql_ssl::get_pdo_attribute('SSL_VERIFY_SERVER_CERT') => $mode === mysql_ssl::VERIFY_IDENTITY];
		if ($ca_file !== null)
		{
			$options[mysql_ssl::get_pdo_attribute('SSL_CA')] = $ca_file;
		}
		if ($ca_path !== null)
		{
			$options[mysql_ssl::get_pdo_attribute('SSL_CAPATH')] = $ca_path;
		}

		$params['driverOptions'] = $options + ($params['driverOptions'] ?? []);

		return $params;
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
	private static function build_sqlite_parameters(array $params, string $path, string|null $user, string|null $password) : array
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
		if (in_array($params['driver'], ['mysqli', 'pdo_mysql', 'pgsql', 'pdo_pgsql', 'oci8', 'pdo_oci']))
		{
			$params['charset'] = 'UTF8';
		}

		if ($params['driver'] === 'pdo_mysql' && extension_loaded('pdo_mysql'))
		{
			// Constant PDO::MYSQL_ATTR_FOUND_ROWS is deprecated since 8.5, use Pdo\Mysql::ATTR_FOUND_ROWS instead
			if (class_exists('\Pdo\Mysql'))
			{
				/**
				 * @psalm-suppress UndefinedClass
				 */
				$params[\Pdo\Mysql::ATTR_FOUND_ROWS] = true;
			}
			else
			{
				$params[\PDO::MYSQL_ATTR_FOUND_ROWS] = true;
			}
		}

		return $params;
	}

	/*
	 * Disable constructing this class.
	 */
	private function __construct()
	{
	}
}
