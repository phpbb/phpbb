<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../phpBB/includes/error_collector.php';

class phpbb_error_collector_test extends phpbb_test_case
{
	public function test_collection()
	{
		$collector = new phpbb_error_collector;
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
}
