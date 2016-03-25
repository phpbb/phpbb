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
