<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

class phpbb_storage_db_session extends phpbb_database_test_case
{
	var $session;
	const annon_id = 'anon_session00000000000000000000';
	const bar_id = 'bar_session000000000000000000000';
	var $set_time = 0;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../fixtures/sessions_full.xml');
	}

	public function setUp()
	{
		parent::setUp();
		$this->session = new phpbb_session();
		$this->session->db_session->set_db($this->new_dbal());
		$this->set_time = time();
		// Update time stamps on all sessions
		foreach(array(self::annon_id, self::bar_id) as $session_id)
		{
			$this->session->db_session->update(
				$session_id,
				array('session_time' => $this->set_time)
			);
		}
	}

	function test_create_session()
	{
		$test_session_data = array(
			'session_id' => 'anon_session00000000000000000001',
			'session_user_id' => '1',
			'session_ip' => '127.0.0.1',
			'session_browser' => 'anonymous user agent',
			'session_admin' => '0',
		);
		$this->session->db_session->create($test_session_data);
		$result = $this->session->db_session->get($test_session_data['session_id']);
		foreach(array_keys($test_session_data) as $key)
		{
			$this->assertEquals($test_session_data[$key], $result[$key]);
		}
	}

	function test_update_session()
	{
		$data = array(
			'session_admin' => 1
		);
		$this->session->db_session->update(self::annon_id, $data);
		$results = $this->session->db_session->get(self::annon_id);
		$this->assertEquals(1, $results['session_admin']);
	}

	function test_get()
	{
		$results = $this->session->db_session->get(self::annon_id);
		$this->assertEquals(0, $results['session_admin']);
	}

	function test_delete()
	{
		$this->session->db_session->delete(self::annon_id);
		$results = $this->session->db_session->get(self::annon_id);
		$this->assertFalse($results);
	}

	function test_delete_all_sessions()
	{
		$this->session->db_session->delete_all_sessions();
		$results = $this->session->db_session->get(self::annon_id);
		$this->assertFalse($results);
	}

	function test_get_user_ip_from_session()
	{
		$this->assertEquals(
			'127.0.0.1',
			$this->session->db_session->get_user_ip_from_session(self::annon_id)
		);
	}

	function test_get_newest_session()
	{
		$session = $this->session->db_session->get(self::annon_id);
		$newest = $this->session->db_session->get_newest_session(ANONYMOUS);
		$this->assertEquals(
			$session['session_id'],
			$newest['session_id']
		);
	}

	function test_get_user_online_time()
	{
		$online_time = $this->session->db_session->get_user_online_time(ANONYMOUS);
		$this->assert_array_content_equals(
			array
			(
				'session_user_id' => ANONYMOUS,
				'online_time' => $this->set_time,
				'viewonline' => 1,
			),
			$online_time
		);
	}

	function test_num_active_sessions()
	{
		$this->assertEquals(
		2,
		$this->session->db_session->num_active_sessions(60)
	);
	}

	function test_get_users_online_totals()
	{
		$sessions = $this->session->db_session->get_users_online_totals();
		$this->assertEquals(
			array
			(
				'online_users' => array('4' => 4),
				'hidden_users' => array(),
				'total_online' => 1,
				'visible_online' => 1,
				'hidden_online' => 0,
				'guests_online' => 0,
			),
			$sessions
		);
	}

	function test_obtain_guest_count()
	{
		$this->assertEquals(
			1,
			$this->session->db_session->obtain_guest_count()
		);
	}

	function test_get_user_list()
	{
		global $phpbb_dispatcher;
		$sessions =
			$this->session->db_session->get_user_list(true, 60, 'session_time', $phpbb_dispatcher);
		$this->assertEquals(2, count($sessions));
		$this->assert_array_content_equals(
			array('user_id', 'username', 'username_clean',
				'user_type', 'user_colour', 'session_id',
				'session_time', 'session_page', 'session_ip',
				'session_browser', 'session_viewonline',
				'session_forum_id',),
			array_keys($sessions[0])
		);
	}

	function test_map_users_online()
	{
		$this->assert_array_content_equals(
			array(),
			$this->session->db_session->map_users_online(
				array(self::annon_id), 60, function ($s) {return $s;})
		);
	}

	function test_map_certain_users_with_time()
	{}

	function test_unset_admin()
	{
		$data = array(
			'session_admin' => 1
		);
		$this->session->db_session->update(self::annon_id, $data);
		$results = $this->session->db_session->get(self::annon_id);
		$this->assertEquals(
			1,
			$results['session_admin'],
			'should be an admin before test starts'
		);
		$this->session->db_session->unset_admin(self::annon_id);
		$results = $this->session->db_session->get(self::annon_id);
		$this->assertEquals(0, $results['session_admin']);
	}

	function test_delete_by_user_id()
	{
		$this->session->db_session->delete_by_user_id(ANONYMOUS);
		$results = $this->session->db_session->get(self::annon_id);
		$this->assertFalse($results);
	}

	function test_num_sessions()
	{
		$this->assertEquals(
			1,
			$this->session->db_session->num_sessions(ANONYMOUS, 60)
		);
	}

	function test_get_with_user_id()
	{
		$session =
			$this->session->db_session->get_with_user_id(ANONYMOUS);
		$this->assertEquals(
			self::annon_id,
			$session['session_id']
		);
		$number_of_keys = 88;
		$this->assertEquals($number_of_keys, count(array_keys($session)));
	}

	function test_set_viewonline()
	{
		$session = $this->session->db_session->get(self::annon_id);
		$this->assertEquals(1, $session['session_viewonline']);
		$this->session->db_session->set_viewonline(ANONYMOUS, 0);
		$session = $this->session->db_session->get(self::annon_id);
		$this->assertEquals(0, $session['session_viewonline']);
	}

	function test_cleanup_guest_sessions()
	{
		$year_in_seconds = 60 * 60 * 24 * 365;
		$this->session->db_session->update(
			self::annon_id,
			array('session_time' => time() - $year_in_seconds)
		);
		$this->session->db_session->cleanup_guest_sessions(60);
		$this->assertEquals(
			0,
			$this->session->db_session->obtain_guest_count()
		);
	}

	function test_cleanup_expired_sessions()
	{
		$year_in_seconds = 60 * 60 * 24 * 365;
		$this->session->db_session->update(
			self::annon_id,
			array('session_time' => time() - $year_in_seconds)
		);
		$this->session->db_session->cleanup_expired_sessions(array(ANONYMOUS), 60);
		$this->assertEquals(
			0,
			$this->session->db_session->obtain_guest_count()
		);
	}

	function test_map_recently_expired()
	{
		$year_in_seconds = 60 * 60 * 24 * 365;
		$this->session->db_session->update(
			self::annon_id,
			array('session_time' => time() - $year_in_seconds)
		);
		$this->assert_array_content_equals(
			array(ANONYMOUS),
			$this->session->db_session->map_recently_expired(
				60,
				function ($s) {return $s['session_user_id'];},
				25
			)
		);
	}
}
