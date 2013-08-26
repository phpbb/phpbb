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

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../fixtures/sessions_full.xml');
	}

	public function setUp()
	{
		parent::setUp();
		$this->session = new phpbb_session();
		$this->session->db_session->set_db($this->new_dbal());
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

	function test_get_session()
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
	{}

	function test_num_active_sessions()
	{
		// 0 Because the mock data doesn't include session times.
		$this->assertEquals(
		0,
		$this->session->db_session->num_active_sessions(60)
	);
	}

	function test_obtain_users_online()
	{}

	function test_obtain_guest_count()
	{}

	function test_get_users_online()
	{}

	function test_map_users_online()
	{}

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
		// 0 Because the mock data doesn't include session times.
		$this->assertEquals(
			0,
			$this->session->db_session->num_sessions(ANONYMOUS, 60)
		);
	}

	function test_get_with_user_id()
	{}

	function test_set_viewonline()
	{}

	function test_cleanup_guest_sessions()
	{}

	function test_cleanup_expired_sessions()
	{}

	function test_map_recently_expired()
	{}
}
