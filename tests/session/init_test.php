<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/cache.php';
require_once dirname(__FILE__) . '/../mock/session_testable.php';

class phpbb_session_init_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_empty.xml');
	}

	// also see security/extract_current_page.php

	public function test_login_session_create()
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

		$session->session_create(3);

		$sql = 'SELECT session_user_id
			FROM phpbb_sessions';

		$this->assertResultEquals(
			$sql,
			array(array('session_user_id' => 3)),
			'Check if exacly one session for user id 3 was created'
		);

		$cookie_expire = $session->time_now + (($config['max_autologin_time']) ? 86400 * (int) $config['max_autologin_time'] : 31536000);

		$session->check_cookies($this, array(
			'u' => array(null, $cookie_expire),
			'k' => array(null, $cookie_expire),
			'sid' => array($_SID, $cookie_expire),
		));

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
		);
	}
}

