<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		set_config(null, null, null, $config);
		set_config_count(null, null, null, $config);

		$cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\null(),
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

		$user = new \phpbb\user();
		$user->data = array('user_lang' => 'en');
		$user->add_lang('common');

		$user_loader = new phpbb\user_loader($db, $phpbb_root_path, $phpEx, USERS_TABLE);
		$user_loader->load_users(array(2, 3, 4, 5, 6));

		$this->notification = new phpbb_mock_notification_type_post(
			$user_loader, null, null, $user, null, null, $phpbb_root_path, $phpEx, null, null, null
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
				'A replied to the topic “Test”.',
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
				'A and B replied to the topic “Test”.',
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
				'A, B, and C replied to the topic “Test”.',
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
				'A, B, C, and D replied to the topic “Test”.',
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
				'A, B, C, and 2 others replied to the topic “Test”.',
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
