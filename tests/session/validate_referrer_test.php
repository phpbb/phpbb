<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/testable_facade.php';

class phpbb_session_validate_referrer_test extends phpbb_database_test_case
{
	public $session_factory;
	public $db;
	public $session_facade;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_empty.xml');
	}

	public function setUp()
	{
		$this->session_factory = new phpbb_session_testable_factory;
		$this->db = $this->new_dbal();
		$this->session_facade =
			new phpbb_session_testable_facade($this->db, $this->session_factory);
	}

	static function referrer_inputs() {
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

	/** @dataProvider  referrer_inputs */
	function test_referrer_inputs (
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
		$referrer = $referrer ? 'http://'.$referrer : '';
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
		), "referrer should" . ($pass_or_fail? '' : "n't") . " be validated");
	}
}
