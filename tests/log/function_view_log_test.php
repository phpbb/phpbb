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
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_admin.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';
require_once dirname(__FILE__) . '/../mock/user.php';
require_once dirname(__FILE__) . '/../mock/cache.php';

class phpbb_log_function_view_log_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/full_log.xml');
	}

	public static function view_log_function_data()
	{
		global $phpEx, $phpbb_dispatcher;
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$expected_data_sets = array(
			1 => array(
				'id'				=> 1,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 0,
				'topic_id'			=> 0,

				'viewforum'			=> '',
				'action'			=> 'LOG_INSTALL_INSTALLED 3.1.0-dev',
			),
			2 => array(
				'id'				=> 2,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 0,
				'topic_id'			=> 0,

				'viewforum'			=> '',
				'action'			=> '{LOG KEY NOT EXISTS}<br />additional_data',
			),
			3 => array(
				'id'				=> 3,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 0,
				'topic_id'			=> 0,

				'viewforum'			=> '',
				'action'			=> '{LOG CRITICAL}<br />critical data',
			),
			4 => array(
				'id'				=> 4,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 12,
				'topic_id'			=> 34,

				'viewforum'			=> '',
				'action'			=> '{LOG MOD}',
				'viewtopic'			=> '',
				'viewlogs'			=> '',
			),
			5 => array(
				'id'				=> 5,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 12,
				'topic_id'			=> 45,

				'viewforum'			=> '',
				'action'			=> '{LOG MOD}',
				'viewtopic'			=> '',
				'viewlogs'			=> '',
			),
			6 => array(
				'id'				=> 6,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 23,
				'topic_id'			=> 56,

				'viewforum'			=> append_sid("phpBB/viewforum.$phpEx", 'f=23'),
				'action'			=> '{LOG MOD}',
				'viewtopic'			=> append_sid("phpBB/viewtopic.$phpEx", 'f=23&amp;t=56'),
				'viewlogs'			=> append_sid("phpBB/mcp.$phpEx", 'i=logs&amp;mode=topic_logs&amp;t=56'),
			),
			7 => array(
				'id'				=> 7,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 12,
				'topic_id'			=> 45,

				'viewforum'			=> '',
				'action'			=> 'LOG_MOD2',
				'viewtopic'			=> '',
				'viewlogs'			=> '',
			),
			8 => array(
				'id'				=> 8,

				'reportee_id'			=> 2,
				'reportee_username'		=> 'admin',
				'reportee_username_full'=> '<span class="username">admin</span>',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 0,
				'topic_id'			=> 0,

				'viewforum'			=> '',
				'action'			=> 'LOG_USER admin',
			),
			9 => array(
				'id'				=> 9,

				'reportee_id'			=> 1,
				'reportee_username'		=> 'Anonymous',
				'reportee_username_full'=> '<span class="username">Anonymous</span>',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 0,
				'topic_id'			=> 0,

				'viewforum'			=> '',
				'action'			=> 'LOG_USER guest',
			),
			10 => array(
				'id'				=> 10,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 0,
				'topic_id'			=> 0,

				'viewforum'			=> '',
				'action'			=> 'LOG_SINGULAR_PLURAL 2',
			),
			11 => array(
				'id'				=> 11,

				'reportee_id'			=> 0,
				'reportee_username'		=> '',
				'reportee_username_full'=> '',

				'user_id'			=> 1,
				'username'			=> 'Anonymous',
				'username_full'		=> '<span class="username">Anonymous</span>',

				'ip'				=> '127.0.0.1',
				'time'				=> 1,
				'forum_id'			=> 15,
				'topic_id'			=> 3,

				'viewforum'			=> '',
				'action'			=> 'LOG_MOD3 guest ',
				'viewtopic'			=> '',
				'viewlogs'			=> '',
			),
		);

		$test_cases = array(
			/**
			* Case documentation
			array(
				// Array of datasets that should be in $log after running the function
				'expected'			=> array(5, 7),
				// Offset that will be returned from the function
				'expected_returned'	=> 0,
				// view_log parameters (see includes/functions_admin.php for docblock)
				// $log is ommited!
				'mod', 5, 0, 12, 45,
			),
			*/
			array(
				'expected'			=> array(1, 2),
				'expected_returned'	=> 0,
				'admin', false,
			),
			array(
				'expected'			=> array(1),
				'expected_returned'	=> 0,
				'admin', false, 1,
			),
			array(
				'expected'			=> array(2),
				'expected_returned'	=> 1,
				'admin', false, 1, 1,
			),
			array(
				'expected'			=> array(2),
				'expected_returned'	=> 1,
				'admin', 0, 1, 1,
			),
			array(
				'expected'			=> array(2),
				'expected_returned'	=> 1,
				'admin', 0, 1, 5,
			),
			array(
				'expected'			=> array(3),
				'expected_returned'	=> 0,
				'critical', false,
			),
			array(
				'expected'			=> array(),
				'expected_returned'	=> null,
				'mode_does_not_exist', false,
			),
			array(
				'expected'			=> array(4, 5, 7),
				'expected_returned'	=> 0,
				'mod', 0, 5, 0, 12,
			),
			array(
				'expected'			=> array(5, 7),
				'expected_returned'	=> 0,
				'mod', 0, 5, 0, 12, 45,
			),
			array(
				'expected'			=> array(6),
				'expected_returned'	=> 0,
				'mod', 0, 5, 0, 23,
			),
			array(
				'expected'			=> array(8),
				'expected_returned'	=> 0,
				'user', 0, 5, 0, 0, 0, 2,
			),
			array(
				'expected'			=> array(8, 9, 10),
				'expected_returned'	=> 0,
				'users', 0,
			),
			array(
				'expected'			=> array(1),
				'expected_returned'	=> 0,
				'admin', false, 5, 0, 0, 0, 0, 0, 'l.log_id ASC', 'install',
			),
			array(
				'expected'			=> array(10),
				'expected_returned'	=> 0,
				'user', false, 5, 0, 0, 0, 0, 0, 'l.log_id ASC', 'plural',
			),
			array(
				'expected'			=> array(11),
				'expected_returned'	=> 0,
				'mod', 0, 5, 0, 15, 3,
			),
		);

		foreach ($test_cases as $case => $case_data)
		{
			foreach ($case_data['expected'] as $data_set => $expected)
			{
				$test_cases[$case]['expected'][$data_set] = $expected_data_sets[$expected];
			}
		}

		return $test_cases;
	}

	/**
	* @dataProvider view_log_function_data
	*/
	public function test_view_log_function($expected, $expected_returned, $mode, $log_count, $limit = 5, $offset = 0, $forum_id = 0, $topic_id = 0, $user_id = 0, $limit_days = 0, $sort_by = 'l.log_id ASC', $keywords = '')
	{
		global $cache, $db, $user, $auth, $phpbb_log, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$db = $this->new_dbal();
		$cache = new phpbb_mock_cache;
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		// Create auth mock
		$auth = $this->getMock('\phpbb\auth\auth');
		$acl_get_map = array(
			array('f_read', 23, true),
			array('m_', 23, true),
		);
		$acl_gets_map = array(
			array('a_', 'm_', 23, true),
		);

		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'),
				$this->anything())
			->will($this->returnValueMap($acl_get_map));
		$auth->expects($this->any())
			->method('acl_gets')
			->with($this->stringContains('_'),
				$this->anything())
			->will($this->returnValueMap($acl_gets_map));

		$user = new phpbb_mock_user;
		$user->optionset('viewcensors', false);
		// Test sprintf() of the data into the action
		$user->lang = array(
			'LOG_INSTALL_INSTALLED'		=> 'installed: %s',
			'LOG_USER'					=> 'User<br /> %s',
			'LOG_MOD2'					=> 'Mod2',
			'LOG_MOD3'		            => 'Mod3: %1$s, %2$s',
			'LOG_SINGULAR_PLURAL'		=> array(
				1	=> 'singular',
				2	=> 'plural (%d)',
			),
		);

		$phpbb_log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		$log = array();
		$this->assertEquals($expected_returned, view_log($mode, $log, $log_count, $limit, $offset, $forum_id, $topic_id, $user_id, $limit_days, $sort_by, $keywords));

		$this->assertEquals($expected, $log);
	}
}
