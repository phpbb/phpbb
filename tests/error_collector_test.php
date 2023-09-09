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

class phpbb_error_collector_test extends phpbb_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_filesystem;

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();
	}

	public function test_collection()
	{
		$collector = new \phpbb\error_collector(E_ALL | E_STRICT); // php set_error_handler() default
		$collector->install();

		// Cause a warning
		// Division by zero was promoted to fatal error and throws DivisionByZeroError exception in PHP 8+
		version_compare(PHP_VERSION, '8', '>=') ? '1b'['0xFF'] : 1/0; $line = __LINE__;

		$collector->uninstall();

		list($errno, $msg_text, $errfile, $errline) = $collector->errors[0];
		$error_contents = $collector->format_errors();

		$this->assertEquals($errno, 2);

		// Unfortunately $error_contents will contain the full path here,
		// because the tests directory is outside of phpbb root path.
		$this->assertStringStartsWith(version_compare(PHP_VERSION, '8', '>=') ? 'Errno 2: Illegal string offset "0xFF" at ' : 'Errno 2: Division by zero at ', $error_contents);
		$this->assertStringEndsWith(" line $line", $error_contents);
	}

	public function test_collection_with_mask()
	{
		$collector = new \phpbb\error_collector(E_ALL & ~E_NOTICE); // not collecting notices
		$collector->install();

		// Cause a warning
		// Division by zero was promoted to fatal error and throws DivisionByZeroError exception in PHP 8+
		version_compare(PHP_VERSION, '8', '>=') ? '1b'['0xFF'] : 1/0; $line = __LINE__;

		// Cause a "Notice: date_default_timezone_set(): Timezone ID 'ThisTimeZoneDoesNotExist' is invalid"
		// https://github.com/php/php-src/blob/880faa39e8c648bdc3aad7aeca170755c6557831/ext/date/php_date.c#L5205
		date_default_timezone_set('ThisTimeZoneDoesNotExist'); $line2 = __LINE__;

		$collector->uninstall();

		// The notice should not be collected
		$this->assertFalse(isset($collector->errors[1]), 'Notice should not be added to errors');
		$this->assertEquals(count($collector->errors), 1);

		list($errno, $msg_text, $errfile, $errline) = $collector->errors[0];
		$error_contents = $collector->format_errors();

		$this->assertEquals($errno, 2);

		// Unfortunately $error_contents will contain the full path here,
		// because the tests directory is outside of phpbb root path.
		$this->assertStringStartsWith(version_compare(PHP_VERSION, '8', '>=') ? 'Errno 2: Illegal string offset "0xFF" at ' : 'Errno 2: Division by zero at ', $error_contents);
		$this->assertStringEndsWith(" line $line", $error_contents);
	}
}
