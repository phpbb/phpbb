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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use phpbb\config_php_file;
use phpbb\exception\runtime_exception;

/**
 * Doctrine DBAL connection factory.
 */
class connection_factory
{
	use driver_convertor;

	/**
	 * Creates a Doctrine DBAL connection from phpBB configuration.
	 *
	 * @param config_php_file $config Config PHP file wrapper.
	 *
	 * @return Connection Doctrine DBAL connection.
	 *
	 * @throws runtime_exception		If the database connection could not be established.
	 * @throws InvalidArgumentException	If the provided driver name is not a valid phpBB database driver.
	 */
	public static function get_connection(config_php_file $config) : Connection
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
	 * @param string		$driver		Driver name.
	 * @param string		$host		Hostname.
	 * @param string|null	$user		Username.
	 * @param string|null	$password	Password.
	 * @param string|null	$name		Database name.
	 * @param string|null	$port		Database port.
	 *
	 * @return Connection Doctrine DBAL connection.
	 *
	 * @throws runtime_exception		If the database connection could not be established.
	 * @throws InvalidArgumentException	If $driver is not a valid phpBB database driver.
	 */
	public static function get_connection_from_params(
		string $driver,
		string $host,
		?string $user = null,
		?string $password = null,
		?string $name = null,
		?string $port = null) : Connection
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

		try
		{
			return DriverManager::getConnection($params);
		}
		catch (Exception $e)
		{
			throw new runtime_exception('DB_CONNECTION_FAILED');
		}
	}

	/*
	 * Disable constructor.
	 */
	private function __construct()
	{
	}
}
