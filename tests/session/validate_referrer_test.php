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

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_validate_referrer_test extends phpbb_session_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_empty.xml');
	}

	static function referrer_inputs()
	{
		$ex = "example.org";
		$alt = "example.com";
		return array(
			// checkpath   referrer  host    forcevars    port servername   rootpath   pass?
			// 0 Referrer or host wasn't collected, therefore should validate
			array(false,  '',  $ex,  false,  80, $ex,  '', true),
			array(false,  $ex, '',   false,  80, $ex,  '', true),
			// 2 Referrer doesn't match host or server_name
			array(false,  $alt, $ex,   false,  80, $ex,  '', false),
			// 3 Everything should check out
			array(false,  $ex, $ex,  false,    80, $ex,  '', true),
			// 4 Check Script Path
			array(true,  $ex, $ex,  false,    80, $ex,  '', true),
			array(true,  "$ex/foo", $ex,  false,    80, $ex,  "/foo", true),
			array(true,  "$ex/bar", $ex,  false,    80, $ex,  "/foo", false),
			// 7 Port (This is not checked unless path is checked)
			array(true,  "$ex:80/foo", "$ex:80",  false, 80, "$ex:80",  "/foo", true),
			array(true,  "$ex:80/bar", "$ex:80",  false, 80, "$ex:80",  "/foo", false),
			array(true,  "$ex:79/foo", "$ex:81",  false, 81, "$ex:81",  "/foo", false),
		);
	}

	/** @dataProvider referrer_inputs */
	function test_referrer_inputs(
		$check_script_path,
		$referrer,
		$host,
		$force_server_vars,
		$server_port,
		$server_name,
		$root_script_path,
		$pass_or_fail
	)
	{
		// Referrer needs http:// because it's going to get stripped in function.
		$referrer = $referrer ? 'http://' . $referrer : '';
		$this->assertEquals(
			$pass_or_fail,
			$this->session_facade->validate_referer(
				$check_script_path,
				$referrer,
				$host,
				$force_server_vars,
				$server_port,
				$server_name,
				$root_script_path
			),
			"referrer should" . ($pass_or_fail ? '' : "n't") . " be validated");
	}
}
