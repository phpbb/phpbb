<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_unset_admin_test extends phpbb_session_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_full.xml');
	}

	function get_test_session()
	{
		return $this->session_facade->session_begin(
			true,
			// Config
			array(
				'session_length' => time(), // need to do this to allow sessions started at time 0
			),
			// Server
			array(
				'HTTP_USER_AGENT' => "user agent",
				'REMOTE_ADDR' => "127.0.0.1",
			),
			// Cookies
			array(
				'_sid' => 'bar_session000000000000000000000',
				'_u' => 4,
			)
		);
	}

	public function test_unset_admin()
	{
		$session = $this->get_test_session();
		$this->assertEquals(1, $session->data['session_admin'], 'should be an admin before test starts');
		$session->unset_admin();
		$session = $this->get_test_session();
		$this->assertEquals(0, $session->data['session_admin'], 'should be not be an admin after unset_admin');
	}
}
