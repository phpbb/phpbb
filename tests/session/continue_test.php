<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/cache.php';
require_once dirname(__FILE__) . '/testable_factory.php';

class phpbb_session_continue_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_full.xml');
	}

	static public function session_begin_attempts()
	{
		global $_SID;
		return array(
			array(
				'bar_session', '4', 'user agent', '127.0.0.1',
				array(
					array('session_id' => 'anon_session', 'session_user_id' => 1),
					array('session_id' => 'bar_session', 'session_user_id' => 4)
				),
				array(),
				'If a request comes with a valid session id with matching user agent and IP, no new session should be created.',
			),
			array(
				'anon_session', '4', 'user agent', '127.0.0.1',
				array(
					array('session_id' => 'bar_session', 'session_user_id' => 4),
					array('session_id' => null, 'session_user_id' => 1) // use generated SID
				),
				array(
					'u' => array('1', null),
					'k' => array(null, null),
					'sid' => array($_SID, null),
				),
				'If a request comes with a valid session id and IP but different user id and user agent, a new anonymous session is created and the session matching the supplied session id is deleted.',
			),
		);
	}

	/**
	* @dataProvider session_begin_attempts
	*/
	public function test_session_begin_valid_session($session_id, $user_id, $user_agent, $ip, $expected_sessions, $expected_cookies, $message)
	{
		$db = $this->new_dbal();
		$session_factory = new phpbb_session_testable_factory;
		$session_factory->set_cookies(array(
			'_sid' => $session_id,
			'_u' => $user_id,
		));
		$session_factory->merge_config_data(array(
			'session_length' => time(), // need to do this to allow sessions started at time 0
		));
		$session_factory->merge_server_data(array(
			'HTTP_USER_AGENT' => $user_agent,
			'REMOTE_ADDR' => $ip,
		));

		$session = $session_factory->get_session($db);
		$session->page = array('page' => 'page', 'forum' => 0);

		$session->session_begin();

		$sql = 'SELECT session_id, session_user_id
			FROM phpbb_sessions';

		// little tickery to allow using a dataProvider with dynamic expected result
		foreach ($expected_sessions as $i => $s)
		{
			if (is_null($s['session_id']))
			{
				$expected_sessions[$i]['session_id'] = $session->session_id;
			}
		}

		$this->assertSqlResultEquals(
			$expected_sessions,
			$sql,
			'Check if no new session was created'
		);

		$session->check_cookies($this, $expected_cookies);

		$session_factory->check($this);
	}
}

