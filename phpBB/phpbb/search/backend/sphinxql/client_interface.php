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

namespace phpbb\search\backend\sphinxql;

/**
 * Interface for executing SphinxQL queries over Sphinx's mysql41 wire protocol.
 */
interface client_interface
{
	/**
	 * Returns whether a compatible MySQL wire-protocol PHP extension is loaded.
	 *
	 * @return bool
	 */
	public function is_driver_available(): bool;

	/**
	 * Overrides the target Sphinx searchd host and port.
	 *
	 * @param string $host
	 * @param int $port
	 * @return void
	 */
	public function set_server(string $host, int $port): void;

	/**
	 * Executes a read SphinxQL statement and returns all rows as associative arrays.
	 *
	 * @param string $sql
	 * @return array<int, array<string, mixed>>
	 * @throws exception
	 */
	public function query(string $sql): array;

	/**
	 * Executes a mutating SphinxQL statement (REPLACE, UPDATE, DELETE, TRUNCATE) and returns affected rows.
	 *
	 * @param string $sql
	 * @return int
	 * @throws exception
	 */
	public function execute(string $sql): int;

	/**
	 * Executes a SELECT statement followed immediately by SHOW META on the same connection.
	 *
	 * @param string $sql
	 * @return array{rows: array<int, array<string, mixed>>, meta: array<string, string>}
	 * @throws exception
	 */
	public function select_with_meta(string $sql): array;

	/**
	 * Quotes and escapes a string literal for safe inclusion in a SphinxQL statement.
	 *
	 * @param string $value
	 * @return string
	 */
	public function quote_string(string $value): string;

	/**
	 * Checks whether the given index exists in searchd via SHOW TABLES.
	 * Returns false gracefully if searchd is unreachable.
	 *
	 * @param string $index_name
	 * @return bool
	 */
	public function index_exists(string $index_name): bool;
}
