<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/testable_factory.php';

class phpbb_session_creation_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_empty.xml');
	}

	// also see security/extract_current_page.php

	public function test_login_session_create()
	{
		global $phpbb_container, $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();
		$config = new phpbb_config(array());
		$request = $this->getMock('phpbb_request');
		$user = $this->getMock('phpbb_user');

		$auth_provider = new phpbb_auth_provider_db($db, $config, $request, $user, $phpbb_root_path, $phpEx);
		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$phpbb_container->expects($this->any())
			->method('get')
			->with('auth.provider.db')
			->will($this->returnValue($auth_provider));

		$session_factory = new phpbb_session_testable_factory;

		$session = $session_factory->get_session($db);
		$session->page = array('page' => 'page', 'forum' => 0);

		$session->session_create(3);

		$sql = 'SELECT session_user_id
			FROM phpbb_sessions';

		$this->assertSqlResultEquals(
			array(array('session_user_id' => 3)),
			$sql,
			'Check if exactly one session for user id 3 was created'
		);

		$one_year_in_seconds = 365 * 24 * 60 * 60;
		$cookie_expire = $session->time_now + $one_year_in_seconds;

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

