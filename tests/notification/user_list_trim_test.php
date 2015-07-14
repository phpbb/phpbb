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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_notification_user_list_trim_test extends phpbb_database_test_case
{
	protected $notification;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/user_list_trim.xml');
	}

	public function setUp()
	{
		global $phpbb_root_path, $phpEx, $phpbb_dispatcher, $user, $cache, $auth;

		parent::setUp();

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$db = $this->new_dbal();

		$config = new \phpbb\config\config(array());

		$cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\dummy(),
			$config,
			$db,
			$phpbb_root_path,
			$phpEx
		);

		$auth = $this->getMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'),
				$this->anything())
			->will($this->returnValueMap(array(
				array('u_viewprofile', 1, false),
			)));

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->data = array('user_lang' => 'en');
		$user->add_lang('common');

		$user_loader = new phpbb\user_loader($db, $phpbb_root_path, $phpEx, USERS_TABLE);
		$user_loader->load_users(array(2, 3, 4, 5, 6));

		$this->notification = new phpbb_mock_notification_type_post(
			$user_loader, null, null, $user, null, null, $phpbb_root_path, $phpEx, null, null
		);
	}

	public function user_list_trim_data()
	{
		return array(
			array(
				array(
					'topic_title'	=> 'Test',
					'poster_id'		=> 2,
					'post_username'	=> 'A',
					'responders'	=> null,
				),
				'<strong>Reply</strong> from A in topic:',
			),
			array(
				array(
					'topic_title'	=> 'Test',
					'poster_id'		=> 2,
					'post_username'	=> 'A',
					'responders'	=> array(
						array('username' => '', 'poster_id' => 3),
					),
				),
				'<strong>Reply</strong> from A and <span class="username">B</span> in topic:',
			),
			array(
				array(
					'topic_title'	=> 'Test',
					'poster_id'		=> 2,
					'post_username'	=> 'A',
					'responders'	=> array(
						array('username' => '', 'poster_id' => 3),
						array('username' => '', 'poster_id' => 4),
					),
				),
				'<strong>Reply</strong> from A, <span class="username">B</span>, and <span class="username">C</span> in topic:',
			),
			array(
				array(
					'topic_title'	=> 'Test',
					'poster_id'		=> 2,
					'post_username'	=> 'A',
					'responders'	=> array(
						array('username' => '', 'poster_id' => 3),
						array('username' => '', 'poster_id' => 4),
						array('username' => '', 'poster_id' => 5),
					),
				),
				'<strong>Reply</strong> from A, <span class="username">B</span>, <span class="username">C</span>, and <span class="username">D</span> in topic:',
			),
			array(
				array(
					'topic_title'	=> 'Test',
					'poster_id'		=> 2,
					'post_username'	=> 'A',
					'responders'	=> array(
						array('username' => '', 'poster_id' => 3),
						array('username' => '', 'poster_id' => 4),
						array('username' => '', 'poster_id' => 5),
						array('username' => '', 'poster_id' => 6),
					),
				),
				'<strong>Reply</strong> from A, <span class="username">B</span>, <span class="username">C</span>, and 2 others in topic:',
			),
		);
	}

	/**
	* @dataProvider user_list_trim_data
	*/
	public function test_user_list_trim($data, $expected_result)
	{
		$data = array('notification_data' => serialize($data));
		$this->notification->set_initial_data($data);

		$this->assertEquals($expected_result, $this->notification->get_title());
	}
}
