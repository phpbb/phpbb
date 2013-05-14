<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/auth.php';

class phpbb_functions_obtain_online_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/obtain_online.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		global $config, $db;

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

		$this->create_guest_sessions();
		$this->assertEquals($expected, obtain_guest_count($forum_id));
	}

	static public function obtain_users_online_data()
	{
		return array(
			array(0, false, array(
				'online_users'			=> array(2 => 2, 3 => 3, 6 => 6, 7 => 7),
				'hidden_users'			=> array(6 => 6, 7 => 7),
				'total_online'			=> 4,
				'visible_online'		=> 2,
				'hidden_online'			=> 2,
				'guests_online'			=> 0,
			)),
			array(0, true, array(
				'online_users'			=> array(2 => 2, 3 => 3, 6 => 6, 7 => 7),
				'hidden_users'			=> array(6 => 6, 7 => 7),
				'total_online'			=> 6,
				'visible_online'		=> 2,
				'hidden_online'			=> 2,
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

		$this->create_guest_sessions();
		$this->create_user_sessions();
		$this->assertEquals($expected, obtain_users_online($forum_id));
	}

	static public function obtain_users_online_string_data()
	{
		return array(
			array(0, false, array(
				'online_userlist'	=> 'REGISTERED_USERS 2, 3',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL 4REG_USERS_TOTAL_AND 2HIDDEN_USERS_TOTAL 2',
			)),
			array(0, true, array(
				'online_userlist'	=> 'REGISTERED_USERS 2, 3',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL 6REG_USERS_TOTAL 2HIDDEN_USERS_TOTAL_AND 2GUEST_USERS_TOTAL 2',
			)),
			array(1, false, array(
				'online_userlist'	=> 'BROWSING_FORUM 3',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL 2REG_USER_TOTAL_AND 1HIDDEN_USER_TOTAL 1',
			)),
			array(1, true, array(
				'online_userlist'	=> 'BROWSING_FORUM_GUEST 3 1',
				'l_online_users'	=> 'ONLINE_USERS_TOTAL 3REG_USER_TOTAL 1HIDDEN_USER_TOTAL_AND 1GUEST_USER_TOTAL 1',
			)),
			array(2, false, array(
				'online_userlist'	=> 'BROWSING_FORUM NO_ONLINE_USERS',
				'l_online_users'	=> 'ONLINE_USERS_ZERO_TOTAL 0REG_USERS_ZERO_TOTAL_AND 0HIDDEN_USERS_ZERO_TOTAL 0',
			)),
			array(2, true, array(
				'online_userlist'	=> 'BROWSING_FORUM_GUESTS NO_ONLINE_USERS 0',
				'l_online_users'	=> 'ONLINE_USERS_ZERO_TOTAL 0REG_USERS_ZERO_TOTAL 0HIDDEN_USERS_ZERO_TOTAL_AND 0GUEST_USERS_ZERO_TOTAL 0',
			)),
		);
	}

	/**
	* @dataProvider obtain_users_online_string_data
	*/
	public function test_obtain_users_online_string($forum_id, $display_guests, $expected)
	{
		$this->db->sql_query('DELETE FROM phpbb_sessions');

		global $config, $user, $auth;
		$config['load_online_guests'] = $display_guests;
		$user->lang = $this->load_language();
		$auth = $this->getMock('auth');
		$acl_get_map = array(
			array('u_viewonline', true),
		);
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'),
				$this->anything())
			->will($this->returnValueMap($acl_get_map));

		$this->create_guest_sessions();
		$this->create_user_sessions();

		$online_users = obtain_users_online($forum_id);
		$this->assertEquals($expected, obtain_users_online_string($online_users, $forum_id));
	}

	protected function create_guest_sessions()
	{
		$this->add_session(1, '0001', 0, true, 0);
		$this->add_session(1, '0002', 1, true, 0);
		$this->add_session(1, '0003', 0, true, 10);
		$this->add_session(1, '0004', 1, true, 10);
	}

	protected function create_user_sessions()
	{
		$this->add_session(2, '0005', 0, true, 0);
		$this->add_session(3, '0006', 1, true, 0);
		$this->add_session(4, '0007', 0, true, 10);
		$this->add_session(5, '0008', 1, true, 10);
		$this->add_session(6, '0005', 0, false, 0);
		$this->add_session(7, '0006', 1, false, 0);
		$this->add_session(8, '0007', 0, false, 10);
		$this->add_session(9, '0008', 1, false, 10);
	}

	protected function add_session($user_id, $user_ip, $forum_id, $view_online, $time_delta)
	{
		$sql_ary = array(
			'session_id'			=> $user_id . '_' . $forum_id . '_session00000000000000000' . $user_ip,
			'session_user_id'		=> $user_id,
			'session_ip'			=> $user_ip,
			'session_forum_id'		=> $forum_id,
			'session_time'			=> time() - $time_delta * 60,
			'session_viewonline'	=> $view_online,
		);
		$this->db->sql_query('INSERT INTO phpbb_sessions ' . $this->db->sql_build_array('INSERT', $sql_ary));
	}

	protected function load_language()
	{
		$lang = array(
			'NO_ONLINE_USERS'	=> 'NO_ONLINE_USERS',
			'REGISTERED_USERS'	=> 'REGISTERED_USERS',
			'BROWSING_FORUM'	=> 'BROWSING_FORUM %s',
			'BROWSING_FORUM_GUEST'	=> 'BROWSING_FORUM_GUEST %s %d',
			'BROWSING_FORUM_GUESTS'	=> 'BROWSING_FORUM_GUESTS %s %d',
		);
		$vars_online = array('ONLINE', 'REG', 'HIDDEN', 'GUEST');
		foreach ($vars_online as $online)
		{
			$lang = array_merge($lang, array(
				$online . '_USERS_ZERO_TOTAL'	=> $online . '_USERS_ZERO_TOTAL %d',
				$online . '_USER_TOTAL'			=> $online . '_USER_TOTAL %d',
				$online . '_USERS_TOTAL'		=> $online . '_USERS_TOTAL %d',
				$online . '_USERS_ZERO_TOTAL_AND'	=> $online . '_USERS_ZERO_TOTAL_AND %d',
				$online . '_USER_TOTAL_AND'			=> $online . '_USER_TOTAL_AND %d',
				$online . '_USERS_TOTAL_AND'		=> $online . '_USERS_TOTAL_AND %d',
			));
		}
		return $lang;
	}
}
