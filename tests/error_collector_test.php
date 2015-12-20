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

require_once dirname(__FILE__) . '/../phpBB/includes/functions.php';

class phpbb_error_collector_test extends phpbb_test_case
{
	public function setUp()
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
		1/0; $line = __LINE__;

		$collector->uninstall();

		list($errno, $msg_text, $errfile, $errline) = $collector->errors[0];
		$error_contents = $collector->format_errors();

		$this->assertEquals($errno, 2);

		// Unfortunately $error_contents will contain the full path here,
		// because the tests directory is outside of phpbb root path.
		$this->assertStringStartsWith('Errno 2: Division by zero at ', $error_contents);
		$this->assertStringEndsWith(" line $line", $error_contents);
	}

	public function test_collection_with_mask()
	{
		$collector = new \phpbb\error_collector(E_ALL & ~E_NOTICE); // not collecting notices
		$collector->install();

		// Cause a warning
		1/0; $line = __LINE__;

		// Cause a notice
		$array = array('ITEM' => 'value');
		$value = $array[ITEM]; $line2 = __LINE__;

		$collector->uninstall();

		// The notice should not be collected
		$this->assertEmpty($collector->errors[1]);

		list($errno, $msg_text, $errfile, $errline) = $collector->errors[0];
		$error_contents = $collector->format_errors();

		$this->assertEquals($errno, 2);

		// Unfortunately $error_contents will contain the full path here,
		// because the tests directory is outside of phpbb root path.
		$this->assertStringStartsWith('Errno 2: Division by zero at ', $error_contents);
		$this->assertStringEndsWith(" line $line", $error_contents);
	}
}
