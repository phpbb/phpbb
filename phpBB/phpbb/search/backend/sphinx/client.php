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

namespace phpbb\search\backend\sphinx;

use phpbb\config\config;

/**
 * Native PDO (pdo_mysql) / mysqli client for Sphinx's mysql41 protocol.
 */
class client implements client_interface
{
	public const DEFAULT_HOST = '127.0.0.1';
	public const DEFAULT_PORT = 9306;

	protected const CONNECT_TIMEOUT = 3;
	protected const CONNECT_RETRIES = 3;
	protected const CONNECT_WAIT_TIME = 300;

	/** @var config */
	protected $config;

	/** @var string|null */
	protected $host_override = null;

	/** @var int|null */
	protected $port_override = null;

	/** @var \PDO|\mysqli|null */
	protected $connection = null;

	/** @var string */
	protected $connected_endpoint = '';

	/**
	 * Constructor
	 *
	 * @param config $config
	 */
	public function __construct(config $config)
	{
		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_driver_available(): bool
	{
		return extension_loaded('pdo_mysql') || extension_loaded('mysqli');
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_server(string $host, int $port): void
	{
		$this->host_override = $host;
		$this->port_override = $port;
		$this->disconnect();
	}

	/**
	 * Normalizes a configured host so MySQL client drivers use TCP rather than local mysqld.sock.
	 *
	 * @param string $host
	 * @return string
	 */
	public static function normalize_host(string $host): string
	{
		$host = trim($host);
		if ($host === '' || strtolower($host) === 'localhost')
		{
			return self::DEFAULT_HOST;
		}

		return $host;
	}

	/**
	 * Normalizes a configured port, defaulting to 9306.
	 *
	 * @param int|string $port
	 * @return int
	 */
	public static function normalize_port(int|string $port): int
	{
		$port_int = (int) $port;

		return ($port_int > 0) ? $port_int : self::DEFAULT_PORT;
	}

	/**
	 * Returns the effective normalized host.
	 *
	 * @return string
	 */
	public function get_host(): string
	{
		$raw_host = $this->host_override ?? (string) $this->config['fulltext_sphinx_host'];

		return self::normalize_host($raw_host);
	}

	/**
	 * Returns the effective normalized port.
	 *
	 * @return int
	 */
	public function get_port(): int
	{
		$raw_port = $this->port_override ?? (string) $this->config['fulltext_sphinx_port'];

		return self::normalize_port($raw_port);
	}

	/**
	 * {@inheritdoc}
	 */
	public function quote_string(string $value): string
	{
		$escaped = strtr($value, [
			'\\'	=> '\\\\',
			"'"		=> "\\'",
			'"'		=> '\\"',
			"\0"	=> '\\0',
			"\n"	=> '\\n',
			"\r"	=> '\\r',
			"\x1a"	=> '\\Z',
		]);

		return "'" . $escaped . "'";
	}

	/**
	 * {@inheritdoc}
	 */
	public function query(string $sql): array
	{
		/** @var array<int, array<string, mixed>> $rows */
		$rows = $this->run_with_retry(function () use ($sql): array {
			return $this->raw_query($sql);
		});

		return $rows;
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(string $sql): int
	{
		/** @var int $affected */
		$affected = $this->run_with_retry(function () use ($sql): int {
			return $this->raw_execute($sql);
		});

		return $affected;
	}

	/**
	 * {@inheritdoc}
	 */
	public function select_with_meta(string $sql): array
	{
		/** @var array{rows: array<int, array<string, mixed>>, meta: array<string, string>} $result */
		$result = $this->run_with_retry(function () use ($sql): array {
			$rows = $this->raw_query($sql);
			$meta_rows = $this->raw_query('SHOW META');

			$meta = [];
			foreach ($meta_rows as $meta_row)
			{
				if (isset($meta_row['Variable_name'], $meta_row['Value']))
				{
					$meta[(string) $meta_row['Variable_name']] = (string) $meta_row['Value'];
				}
				else
				{
					$values = array_values($meta_row);
					if (count($values) >= 2)
					{
						$meta[(string) $values[0]] = (string) $values[1];
					}
				}
			}

			return [
				'rows'	=> $rows,
				'meta'	=> $meta,
			];
		});

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function index_exists(string $index_name): bool
	{
		try
		{
			$tables = $this->query('SHOW TABLES');
		}
		catch (exception $e)
		{
			return false;
		}

		foreach ($tables as $row)
		{
			$name = isset($row['Index']) ? (string) $row['Index'] : (string) reset($row);
			if ($name === $index_name)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Closes the active SphinxQL connection, if any.
	 *
	 * @return void
	 */
	public function disconnect(): void
	{
		if ($this->connection instanceof \mysqli)
		{
			@$this->connection->close();
		}

		$this->connection = null;
		$this->connected_endpoint = '';
	}

	/**
	 * Ensures an active connection to the configured searchd endpoint exists.
	 *
	 * @return \PDO|\mysqli
	 * @throws exception
	 */
	protected function ensure_connected(): \PDO|\mysqli
	{
		$host = $this->get_host();
		$port = $this->get_port();
		$endpoint = $host . ':' . $port;

		if ($this->connection !== null && $this->connected_endpoint === $endpoint)
		{
			return $this->connection;
		}

		$this->disconnect();

		if (extension_loaded('pdo_mysql'))
		{
			$dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
			$this->connection = new \PDO($dsn, '', '', [
				\PDO::ATTR_ERRMODE				=> \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE	=> \PDO::FETCH_ASSOC,
				\PDO::ATTR_EMULATE_PREPARES		=> true,
				\PDO::ATTR_TIMEOUT				=> self::CONNECT_TIMEOUT,
			]);
			$this->connected_endpoint = $endpoint;

			return $this->connection;
		}

		if (extension_loaded('mysqli'))
		{
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
			$mysqli = mysqli_init();
			if ($mysqli === false)
			{
				throw new exception('Failed to initialize mysqli for SphinxQL connection.');
			}

			$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, self::CONNECT_TIMEOUT);
			$mysqli->real_connect($host, '', '', '', $port);
			$mysqli->set_charset('utf8mb4');

			$this->connection = $mysqli;
			$this->connected_endpoint = $endpoint;

			return $this->connection;
		}

		throw new exception('FULLTEXT_SPHINX_DRIVER_NOT_AVAILABLE');
	}

	/**
	 * Executes a read query on the active connection.
	 *
	 * @param string $sql
	 * @return array<int, array<string, mixed>>
	 * @throws exception
	 */
	protected function raw_query(string $sql): array
	{
		$conn = $this->ensure_connected();

		if ($conn instanceof \PDO)
		{
			$stmt = $conn->query($sql);
			if ($stmt === false)
			{
				throw new exception('SphinxQL query failed.');
			}

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		$result = $conn->query($sql);
		if ($result instanceof \mysqli_result)
		{
			$rows = $result->fetch_all(MYSQLI_ASSOC);
			$result->free();

			return $rows;
		}

		return [];
	}

	/**
	 * Executes a write statement on the active connection.
	 *
	 * @param string $sql
	 * @return int
	 * @throws exception
	 */
	protected function raw_execute(string $sql): int
	{
		$conn = $this->ensure_connected();

		if ($conn instanceof \PDO)
		{
			$affected = $conn->exec($sql);
			if ($affected === false)
			{
				throw new exception('SphinxQL statement execution failed.');
			}

			return $affected;
		}

		$result = $conn->query($sql);
		if ($result instanceof \mysqli_result)
		{
			$result->free();
		}

		return (int) $conn->affected_rows;
	}

	/**
	 * Runs a SphinxQL callback with transient connection retry handling.
	 *
	 * @param callable $callback
	 * @return mixed
	 * @throws exception
	 */
	protected function run_with_retry(callable $callback): mixed
	{
		$retries = self::CONNECT_RETRIES;

		while (true)
		{
			try
			{
				return $callback();
			}
			catch (exception $e)
			{
				throw $e;
			}
			catch (\Throwable $e)
			{
				if ($retries > 0 && $this->is_transient_connection_error($e))
				{
					$retries--;
					$this->disconnect();
					usleep(self::CONNECT_WAIT_TIME);
					continue;
				}

				$this->disconnect();
				throw new exception($e->getMessage(), [], $e, (int) $e->getCode());
			}
		}
	}

	/**
	 * Determines whether an exception represents a transient connection failure worth retrying.
	 *
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function is_transient_connection_error(\Throwable $e): bool
	{
		$message = $e->getMessage();
		$code = (int) $e->getCode();

		if (in_array($code, [2002, 2006, 2013], true))
		{
			return true;
		}

		return str_contains($message, 'errno=111')
			|| str_contains($message, 'Connection refused')
			|| str_contains($message, 'server has gone away')
			|| str_contains($message, 'Lost connection');
	}
}
