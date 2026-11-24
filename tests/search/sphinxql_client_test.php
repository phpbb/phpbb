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

use phpbb\config\config;
use phpbb\search\backend\sphinxql\client;
use phpbb\search\backend\sphinxql\exception as sphinxql_exception;

class phpbb_search_sphinxql_client_test extends phpbb_test_case
{
	public static function host_normalization_data(): array
	{
		return [
			['', '127.0.0.1'],
			['   ', '127.0.0.1'],
			['localhost', '127.0.0.1'],
			['LOCALHOST', '127.0.0.1'],
			['127.0.0.1', '127.0.0.1'],
			['10.10.0.25', '10.10.0.25'],
			['sphinx.example.internal', 'sphinx.example.internal'],
		];
	}

	/**
	 * @dataProvider host_normalization_data
	 */
	public function test_normalize_host(string $input, string $expected): void
	{
		$this->assertSame($expected, client::normalize_host($input));
	}

	public static function port_normalization_data(): array
	{
		return [
			['', 9306],
			[0, 9306],
			['0', 9306],
			[-5, 9306],
			[9306, 9306],
			['9307', 9307],
		];
	}

	/**
	 * @dataProvider port_normalization_data
	 */
	public function test_normalize_port(int|string $input, int $expected): void
	{
		$this->assertSame($expected, client::normalize_port($input));
	}

	public function test_server_configuration_and_override(): void
	{
		$config = new config([
			'fulltext_sphinxql_host'	=> 'localhost',
			'fulltext_sphinxql_port'	=> '',
		]);

		$client = new client($config);
		$this->assertSame('127.0.0.1', $client->get_host());
		$this->assertSame(9306, $client->get_port());

		$client->set_server('192.168.1.50', 9316);
		$this->assertSame('192.168.1.50', $client->get_host());
		$this->assertSame(9316, $client->get_port());
	}

	public function test_quote_string_escapes_special_characters(): void
	{
		$client = new client(new config([]));

		$this->assertSame("'simple text'", $client->quote_string('simple text'));
		$this->assertSame("'it\\'s a \\\"quoted\\\" \\\\ path\\nline2\\r\\0\\Z'", $client->quote_string("it's a \"quoted\" \\ path\nline2\r\0\x1a"));
		$this->assertSame("'Ünicode 日本語'", $client->quote_string('Ünicode 日本語'));
	}

	public function test_driver_availability(): void
	{
		$client = new client(new config([]));
		$expected = extension_loaded('pdo_mysql') || extension_loaded('mysqli');
		$this->assertSame($expected, $client->is_driver_available());
	}

	public function test_unreachable_server_handling(): void
	{
		$client = new client(new config([
			'fulltext_sphinxql_host'	=> '127.0.0.1',
			'fulltext_sphinxql_port'	=> 1,
		]));

		// index_exists() must fail safe without throwing
		$this->assertFalse($client->index_exists('index_phpbb_nonexistent'));

		$this->expectException(sphinxql_exception::class);
		$client->query('SHOW TABLES');
	}
}
