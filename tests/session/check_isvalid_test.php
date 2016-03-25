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

class phpbb_session_check_isvalid_test extends phpbb_session_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_full.xml');
	}

	protected function access_with($session_id, $user_id, $user_agent, $ip)
	{
		$this->session_factory->merge_test_data($session_id, $user_id, $user_agent, $ip);

		$session = $this->session_factory->get_session($this->db);
		$session->page = array('page' => 'page', 'forum' => 0);

		$session->session_begin();
		$this->session_factory->check($this);
		return $session;
	}

	public function test_session_valid_session_exists()
	{
		$session = $this->access_with('bar_session000000000000000000000', '4', 'user agent', '127.0.0.1');
		$session->check_cookies($this, array());

		$this->check_sessions_equals(array(
				array('session_id' => 'anon_session00000000000000000000', 'session_user_id' => 1),
				array('session_id' => 'bar_session000000000000000000000', 'session_user_id' => 4),
			),
			'If a request comes with a valid session id with matching user agent and IP, no new session should be created.'
		);
	}

	public function test_session_invalid_make_new_annon_session()
	{
		$session = $this->access_with('anon_session00000000000000000000', '4', 'user agent', '127.0.0.1');
		$session->check_cookies($this, array(
			'u' => array('1', null),
			'k' => array(null, null),
			'sid' => array($session->session_id, null),
		));

		$this->check_sessions_equals(array(
				array('session_id' => $session->session_id, 'session_user_id' => 1), // use generated SID
				array('session_id' => 'bar_session000000000000000000000', 'session_user_id' => 4),
			),
			'If a request comes with a valid session id and IP but different user id and user agent,
				 a new anonymous session is created and the session matching the supplied session id is deleted.'
		);
	}
}
