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

	public function test_session_begin_valid_session()
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

		$_COOKIE['_sid'] = 'bar_session';
		$_COOKIE['_u'] = '4';
		$_SERVER['HTTP_USER_AGENT'] = 'user agent';

		$config['session_length'] = time(); // need to do this to allow sessions started at time 0
		$session->session_begin();

		$sql = 'SELECT session_id, session_user_id
			FROM phpbb_sessions';

		$this->assertResultEquals(
			$sql,
			array(
				array('session_id' => 'anon_session', 'session_user_id' => 1),
				array('session_id' => 'bar_session', 'session_user_id' => 4)
			),
			'Check if no new session was created'
		);

		$cookie_expire = $session->time_now + (($config['max_autologin_time']) ? 86400 * (int) $config['max_autologin_time'] : 31536000);

		$session->check_cookies($this, array());

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

