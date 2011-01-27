<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once 'mock/cache.php';
require_once 'mock/session_testable.php';

class phpbb_session_continue_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_full.xml');
	}

	static public function session_begin_attempts()
	{
		return array(
			array(
				'bar_session', '4', 'user agent',
				array(
					array('session_id' => 'anon_session', 'session_user_id' => 1),
					array('session_id' => 'bar_session', 'session_user_id' => 4)
				),
				array(),
				'Check if no new session was created',
			),
			array(
				'anon_session', '4', 'user agent',
				array(
					array('session_id' => 'bar_session', 'session_user_id' => 4),
					array('session_id' => null, 'session_user_id' => 1) // use generated SID
				),
				array(
					'u' => array('1', null),
					'k' => array(null, null),
					'sid' => array($_SID, null),
				),
				'Check if an anonymous new session was created',
			),
		);
	}

	/**
	* @dataProvider session_begin_attempts
	*/
	public function test_session_begin_valid_session($session_id, $user_id, $user_agent, $expected_sessions, $expected_cookies, $message)
	{
		$session = new phpbb_mock_session_testable;
		$session->page = array('page' => 'page', 'forum' => 0);

		// set up all the global variables used in session_create
		global $SID, $_SID, $db, $config, $cache;

		$config = $this->get_config();
		$db = $this->new_dbal();
		$cache_data = array(
			'_bots' => array(),
		);
		$cache = new phpbb_mock_cache;
		$SID = $_SID = null;

		$_COOKIE['_sid'] = $session_id;
		$_COOKIE['_u'] = $user_id;
		$_SERVER['HTTP_USER_AGENT'] = $user_agent;

		$config['session_length'] = time(); // need to do this to allow sessions started at time 0
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

		$this->assertResultEquals(
			$sql,
			$expected_sessions,
			'Check if no new session was created'
		);

		$session->check_cookies($this, $expected_cookies);

		$cache->check($this, $cache_data);
	}
	static public function get_config()
	{
		return array(
			'allow_autologin' => false,
			'auth_method' => 'db',
			'forwarded_for_check' => true,
			'active_sessions' => 0, // disable
			'rand_seed' => 'foo',
			'rand_seed_last_update' => 0,
			'max_autologin_time' => 0,
			'session_length' => 100,
			'form_token_lifetime' => 100,
			'cookie_name' => '',
			'limit_load' => 0,
			'limit_search_load' => 0,
			'ip_check' => 3,
			'browser_check' => 1,
		);
	}
}

