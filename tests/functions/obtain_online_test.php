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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';

class phpbb_functions_obtain_online_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/obtain_online.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		global $config, $db, $user;

		$user = new StdClass;
		$db = $this->db = $this->new_dbal();
		$config = array(
			'load_online_time'	=> 5,
		);
	}

	static public function obtain_guest_count_data()
	{
		return array(
			array(0, 2),
			array(1, 1),
		);
	}

	/**
	* @dataProvider obtain_guest_count_data
	*/
	public function test_obtain_guest_count($forum_id, $expected)
	{
		$this->db->sql_query('DELETE FROM phpbb_sessions');

		$time = time();
		$this->create_guest_sessions($time);
		$this->assertEquals($expected, obtain_guest_count($forum_id));
	}

	static public function obtain_users_online_data()
	{
		return array(
			array(0, false, array(
				'online_users'			=> array(2 => 2, 3 => 3, 6 => 6, 7 => 7, 10 => 10),
				'hidden_users'			=> array(6 => 6, 7 => 7, 10 => 10),
				'total_online'			=> 5,
				'visible_online'		=> 2,
				'hidden_online'			=> 3,
				'guests_online'			=> 0,
			)),
			array(0, true, array(
				'online_users'			=> array(2 => 2, 3 => 3, 6 => 6, 7 => 7, 10 => 10),
				'hidden_users'			=> array(6 => 6, 7 => 7, 10 => 10),
				'total_online'			=> 7,
				'visible_online'		=> 2,
				'hidden_online'			=> 3,
				'guests_online'			=> 2,
			)),
			array(1, false, array(
				'online_users'			=> array(3 => 3, 7 => 7),
				'hidden_users'			=> array(7 => 7),
				'total_online'			=> 2,
				'visible_online'		=> 1,
				'hidden_online'			=> 1,
				'guests_online'			=> 0,
			)),
			array(1, true, array(
				'online_users'			=> array(3 => 3, 7 => 7),
				'hidden_users'			=> array(7 => 7),
				'total_online'			=> 3,
				'visible_online'		=> 1,
				'hidden_online'			=> 1,
				'guests_online'			=> 1,
			)),
			array(2, false, array(
				'online_users'			=> array(),
				'hidden_users'			=> array(),
				'total_online'			=> 0,
				'visible_online'		=> 0,
				'hidden_online'			=> 0,
				'guests_online'			=> 0,
			)),
			array(2, true, array(
				'online_users'			=> array(),
				'hidden_users'			=> array(),
				'total_online'			=> 0,
				'visible_online'		=> 0,
				'hidden_online'			=> 0,
				'guests_online'			=> 0,
			)),
		);
	}

	/**
	* @dataProvider obtain_users_online_data
	*/
	public function test_obtain_users_online($forum_id, $display_guests, $expected)
	{
		$this->db->sql_query('DELETE FROM phpbb_sessions');

		global $config;
		$config['load_online_guests'] = $display_guests;

		$time = time();
		$this->create_guest_sessions($time);
		$this->create_user_sessions($time);
		$this->assertEquals($expected, obtain_users_online($forum_id));
	}

	static public function obtain_users_online_string_data()
	{
		return array(
			array(0, false, array(
				'online_userlist'	=> 'REGISTERED_USERS <span class="username">2</span>, <span class="username">3</span>',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL 5 REG_USERS_TOTAL 2 HIDDEN_USERS_TOTAL 3',
			)),
			array(0, true, array(
				'online_userlist'	=> 'REGISTERED_USERS <span class="username">2</span>, <span class="username">3</span>',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL_GUESTS 7 REG_USERS_TOTAL 2 HIDDEN_USERS_TOTAL 3 GUEST_USERS_TOTAL 2',
			)),
			array(1, false, array(
				'online_userlist'	=> 'BROWSING_FORUM <span class="username">3</span>',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL 2 REG_USERS_TOTAL 1 HIDDEN_USERS_TOTAL 1',
			)),
			array(1, true, array(
				'online_userlist'	=> 'BROWSING_FORUM_GUESTS 1 <span class="username">3</span>',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL_GUESTS 3 REG_USERS_TOTAL 1 HIDDEN_USERS_TOTAL 1 GUEST_USERS_TOTAL 1',
			)),
			array(2, false, array(
				'online_userlist'	=> 'BROWSING_FORUM NO_ONLINE_USERS',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL 0 REG_USERS_TOTAL 0 HIDDEN_USERS_TOTAL 0',
			)),
			array(2, true, array(
				'online_userlist'	=> 'BROWSING_FORUM_GUESTS 0 NO_ONLINE_USERS',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL_GUESTS 0 REG_USERS_TOTAL 0 HIDDEN_USERS_TOTAL 0 GUEST_USERS_TOTAL 0',
			)),
		);
	}

	/**
	* @dataProvider obtain_users_online_string_data
	*/
	public function test_obtain_users_online_string($forum_id, $display_guests, $expected)
	{
		$this->db->sql_query('DELETE FROM phpbb_sessions');

		global $config, $user, $auth, $phpbb_dispatcher;
		$config['load_online_guests'] = $display_guests;
		$user = new phpbb_mock_lang();
		$user->lang = $this->load_language();
		$auth = $this->getMock('\phpbb\auth\auth');
		$acl_get_map = array(
			array('u_viewonline', true),
			array('u_viewprofile', true),
		);
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'),
				$this->anything())
			->will($this->returnValueMap($acl_get_map));
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$time = time();
		$this->create_guest_sessions($time);
		$this->create_user_sessions($time);

		$online_users = obtain_users_online($forum_id);
		$this->assertEquals($expected, obtain_users_online_string($online_users, $forum_id));
	}

	protected function create_guest_sessions($time)
	{
		$this->add_session(1, '0001', '192.168.0.1', 0, true, $time);
		$this->add_session(1, '0002', '192.168.0.2', 1, true, $time);
		$this->add_session(1, '0003', '192.168.0.3', 0, true, $time, 10);
		$this->add_session(1, '0004', '192.168.0.4', 1, true, $time, 10);
	}

	protected function create_user_sessions($time)
	{
		$this->add_session(2, '0005', '192.168.0.5', 0, true, $time);
		$this->add_session(3, '0006', '192.168.0.6', 1, true, $time);
		$this->add_session(4, '0007', '192.168.0.7', 0, true, $time, 10);
		$this->add_session(5, '0008', '192.168.0.8', 1, true, $time, 10);
		$this->add_session(6, '0005', '192.168.0.9', 0, false, $time);
		$this->add_session(7, '0006', '192.168.0.10', 1, false, $time);
		$this->add_session(8, '0007', '192.168.0.11', 0, false, $time, 10);
		$this->add_session(9, '0008', '192.168.0.12', 1, false, $time, 10);
		$this->add_session(10, '009', '192.168.0.13', 0, false, $time);
	}

	protected function add_session($user_id, $session_id, $user_ip, $forum_id, $view_online, $time, $time_delta = 0)
	{
		$sql_ary = array(
			'session_id'			=> $user_id . '_' . $forum_id . '_session00000000000000000' . $session_id,
			'session_user_id'		=> $user_id,
			'session_ip'			=> $user_ip,
			'session_forum_id'		=> $forum_id,
			'session_time'			=> $time - $time_delta * 60,
			'session_viewonline'	=> $view_online,
		);
		$this->db->sql_query('INSERT INTO phpbb_sessions ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}

	protected function load_language()
	{
		return array(
			'NO_ONLINE_USERS'	=> 'NO_ONLINE_USERS',
			'REGISTERED_USERS'	=> 'REGISTERED_USERS',
			'BROWSING_FORUM'	=> 'BROWSING_FORUM %s',
			'BROWSING_FORUM_GUEST'	=> 'BROWSING_FORUM_GUEST %s %d',
			'BROWSING_FORUM_GUESTS'	=> 'BROWSING_FORUM_GUESTS %s %d',
		);
	}
}
