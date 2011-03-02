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

class phpbb_session_init_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_empty.xml');
	}

	// also see security/extract_current_page.php

	public function test_login_session_create()
	{
		$db = $this->new_dbal();
		$session_factory = new phpbb_session_testable_factory;

		$session = $session_factory->get_session($db);
		$session->page = array('page' => 'page', 'forum' => 0);

		$session->session_create(3);

		$sql = 'SELECT session_user_id
			FROM phpbb_sessions';

		$this->assertSqlResultEquals(
			array(array('session_user_id' => 3)),
			$sql,
			'Check if exacly one session for user id 3 was created'
		);

		$cookie_expire = $session->time_now + 31536000; // default is one year

		$session->check_cookies($this, array(
			'u' => array(null, $cookie_expire),
			'k' => array(null, $cookie_expire),
			'sid' => array($session->session_id, $cookie_expire),
		));

		global $SID, $_SID;
		$this->assertEquals($session->session_id, $_SID);
		$this->assertEquals('?sid=' . $session->session_id, $SID);

		$session_factory->check($this);
	}
}

