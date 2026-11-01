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

use InvalidArgumentException;

/**
 * Encryption settings for MySQL connections, shared by the mysqli driver and the Doctrine pdo_mysql connection.
 *
 * $dbsslmode in config.php takes MySQL's ssl-mode values:
 * - disabled (default): no TLS.
 * - preferred: TLS when the server offers it, otherwise unencrypted. The certificate is not checked.
 * - required: TLS. The certificate is not checked.
 * - verify_identity: TLS, with the certificate checked against the CA file in $dbsslca, or against the system's trusted
 *   CAs when $dbsslca is empty, and against the host name in $dbhost.
 */
class mysql_ssl
{
	public const DISABLED = 'disabled';
	public const PREFERRED = 'preferred';
	public const REQUIRED = 'required';
	public const VERIFY_IDENTITY = 'verify_identity';

	/**
	 * Normalises an ssl-mode setting from config.php.
	 *
	 * @param string|null $mode The setting; empty means disabled.
	 *
	 * @return string One of the mode constants.
	 *
	 * @throws InvalidArgumentException If the mode is not one of MySQL's ssl-mode values that phpBB supports.
	 */
	public static function get_mode(string|null $mode): string
	{
		$mode = strtolower(trim((string) $mode));
		if ($mode === '')
		{
			return self::DISABLED;
		}

		if (!in_array($mode, [self::DISABLED, self::PREFERRED, self::REQUIRED, self::VERIFY_IDENTITY], true))
		{
			throw new InvalidArgumentException("Unknown database ssl mode \"$mode\". Use disabled, preferred, required or verify_identity.");
		}

		return $mode;
	}

	/**
	 * Returns where the trusted CAs are, for verifying the server's certificate.
	 *
	 * mysqlnd only checks the certificate, and only starts TLS from PDO, when it is given a CA file or directory.
	 *
	 * @param string|null $ca_file CA file from config.php; empty means the system's trusted CAs.
	 *
	 * @return array{0: string|null, 1: string|null} CA file and CA directory, at least one of them set.
	 *
	 * @throws InvalidArgumentException If no trusted CAs can be found.
	 */
	public static function get_ca_locations(string|null $ca_file): array
	{
		if (!empty($ca_file))
		{
			if (!is_readable($ca_file))
			{
				throw new InvalidArgumentException("The database ssl CA file \"$ca_file\" cannot be read.");
			}

			return [$ca_file, null];
		}

		$locations = function_exists('openssl_get_cert_locations') ? openssl_get_cert_locations() : [];
		$file = ($locations['ini_cafile'] ?? '') ?: ($locations['default_cert_file'] ?? '');
		$path = ($locations['ini_capath'] ?? '') ?: ($locations['default_cert_dir'] ?? '');
		$file = ($file !== '' && is_file($file)) ? $file : null;
		$path = ($path !== '' && is_dir($path)) ? $path : null;

		if ($file === null && $path === null)
		{
			throw new InvalidArgumentException('No trusted CAs were found for the database connection. Set $dbsslca in config.php.');
		}

		return [$file, $path];
	}

	/**
	 * Returns a pdo_mysql attribute, from Pdo\Mysql where PHP has it (8.4+), as the PDO::MYSQL_ATTR_* constants are
	 * deprecated since 8.5.
	 *
	 * @param string $name Attribute name without its prefix, e.g. SSL_CA.
	 *
	 * @return int The attribute.
	 */
	public static function get_pdo_attribute(string $name): int
	{
		return class_exists('\Pdo\Mysql') ? constant('\Pdo\Mysql::ATTR_' . $name) : constant('\PDO::MYSQL_ATTR_' . $name);
	}

	/**
	 * Returns the pdo_mysql attributes that encrypt a connection.
	 *
	 * @return int[]
	 */
	public static function get_pdo_attributes(): array
	{
		return array_map([self::class, 'get_pdo_attribute'], ['SSL_CA', 'SSL_CAPATH', 'SSL_VERIFY_SERVER_CERT']);
	}

	/**
	 * Returns whether a failed connection failed because TLS could not be set up, so a preferred connection can retry
	 * without it.
	 *
	 * @param int		$code		MySQL client error code.
	 * @param string	$message	Error message.
	 *
	 * @return bool
	 */
	public static function is_unavailable(int $code, string $message): bool
	{
		// 2006: the server has no TLS. 2002 also means the server is unreachable, so only a failed handshake counts.
		return $code === 2006 || ($code === 2002 && str_contains($message, 'Cannot connect to MySQL using SSL'));
	}
}
