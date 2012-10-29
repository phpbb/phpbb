<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_notification_test extends phpbb_database_test_case
{
	protected $notifications;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/notification.xml');
	}


	protected function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $db, $phpEx;

		if (!function_exists('set_var'))
		{
			include($phpbb_root_path . 'includes/functions.' . $phpEx);
		}

		include_once(__DIR__ . '/ext/test/notification/type/test.' . $phpEx);

		$db = $this->new_dbal();
		$config = new phpbb_config(array(
			'allow_privmsg'			=> true,
			'allow_bookmarks'		=> true,
			'allow_topic_notify'	=> true,
			'allow_forum_notify'	=> true,
		));
		$user = new phpbb_mock_user();

		$this->notifications = new phpbb_notification_manager(
			$db,
			new phpbb_mock_cache(),
			new phpbb_template($phpbb_root_path, $phpEx, $config, $user, new phpbb_style_resource_locator()),
			new phpbb_mock_extension_manager($phpbb_root_path,
				array(
					'test' => array(
						'ext_name' => 'test',
						'ext_active' => '1',
						'ext_path' => 'ext/test/',
					),
				)
			),
			$user,
			new phpbb_mock_notifications_auth(),
			$config,
			$phpbb_root_path,
			$phpEx
		);
	}

	public function test_get_subscription_types()
	{
		$subscription_types = $this->notifications->get_subscription_types();

		$this->assertArrayHasKey('NOTIFICATION_GROUP_MISCELLANEOUS', $subscription_types);
		$this->assertArrayHasKey('NOTIFICATION_GROUP_POSTING', $subscription_types);

		$this->assertArrayHasKey('phpbb_notification_type_bookmark', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		$this->assertArrayHasKey('phpbb_notification_type_post', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		$this->assertArrayHasKey('phpbb_notification_type_quote', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		$this->assertArrayHasKey('phpbb_notification_type_topic', $subscription_types['NOTIFICATION_GROUP_POSTING']);

		$this->assertArrayHasKey('phpbb_notification_type_pm', $subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS']);

		//get_subscription_types
		//get_subscription_methods
	}

	public function test_subscriptions()
	{
		$this->notifications->delete_subscription('phpbb_notification_type_post', 0, '', 2);

		$this->assertArrayNotHasKey('phpbb_notification_type_post', $this->notifications->get_global_subscriptions(2));

		$this->notifications->add_subscription('phpbb_notification_type_post', 0, '', 2);

		$this->assertArrayHasKey('phpbb_notification_type_post', $this->notifications->get_global_subscriptions(2));
	}

	public function test_notifications()
	{
		global $db;

		// Used to test post notifications later
		$db->sql_query('INSERT INTO ' . TOPICS_WATCH_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'topic_id'			=> 2,
			'notify_status'		=> NOTIFY_YES,
			'user_id'			=> 0,
		)));

		$this->assertEquals(array(
				'notifications'		=> array(),
				'unread_count'		=> 0,
				'total_count'		=> 0,
		), $this->notifications->load_notifications(array(
			'count_unread'	=> true,
		)));

		$this->notifications->add_notifications('phpbb_ext_test_notification_type_test', array(
			'post_id'		=> '1',
			'topic_id'		=> '1',
			'post_time'		=> 1349413321,
		));

		$this->notifications->add_notifications('phpbb_ext_test_notification_type_test', array(
			'post_id'		=> '2',
			'topic_id'		=> '2',
			'post_time'		=> 1349413322,
		));

		$this->notifications->add_notifications('phpbb_ext_test_notification_type_test', array(
			'post_id'		=> '3',
			'topic_id'		=> '2',
			'post_time'		=> 1349413323,
		));

		$this->notifications->add_notifications(array('phpbb_notification_type_quote', 'phpbb_notification_type_bookmark', 'phpbb_notification_type_post', 'phpbb_ext_test_notification_type_test'), array(
			'post_id'		=> '4',
			'topic_id'		=> '2',
			'post_time'		=> 1349413324,
			'poster_id'		=> 2,
			'topic_title'	=> 'test-title',
			'post_subject'	=> 'Re: test-title',
			'forum_id'		=> 2,
			'forum_name'	=> 'Your first forum',
		));

		$db->sql_query('INSERT INTO ' . BOOKMARKS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'topic_id'			=> 2,
			'user_id'			=> 0,
		)));

		$this->notifications->add_notifications(array('phpbb_notification_type_quote', 'phpbb_notification_type_bookmark', 'phpbb_notification_type_post', 'phpbb_ext_test_notification_type_test'), array(
			'post_id'		=> '5',
			'topic_id'		=> '2',
			'post_time'		=> 1349413325,
			'poster_id'		=> 2,
			'topic_title'	=> 'test-title',
			'post_subject'	=> 'Re: test-title',
			'forum_id'		=> 2,
			'forum_name'	=> 'Your first forum',
		));

		$this->notifications->delete_subscription('phpbb_ext_test_notification_type_test');

		$this->notifications->add_notifications('phpbb_ext_test_notification_type_test', array(
			'post_id'		=> '6',
			'topic_id'		=> '2',
			'post_time'		=> 1349413326,
		));

		$notifications = $this->notifications->load_notifications(array(
			'count_unread'	=> true,
		));

		$expected = array(
			1 => array(
				'item_type'			=> 'phpbb_ext_test_notification_type_test',
				'item_id'			=> 1,
				'item_parent_id'	=> 1,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413321,
				'data'				=> array(),
			),
			2 => array(
				'item_type'			=> 'phpbb_ext_test_notification_type_test',
				'item_id'			=> 2,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413322,
				'data'				=> array(),
			),
			3 => array(
				'item_type'			=> 'phpbb_ext_test_notification_type_test',
				'item_id'			=> 3,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413323,
				'data'				=> array(),
			),
			4 => array(
				'item_type'			=> 'phpbb_notification_type_post',
				'item_id'			=> 4,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413324,
				'data'				=> array(
					'poster_id'		=> 2,
					'topic_title'	=> 'test-title',
					'post_subject'	=> 'Re: test-title',
					'post_username'	=> '',
					'forum_id'		=> 2,
					'forum_name'	=> 'Your first forum',
				),
			),
			5 => array(
				'item_type'			=> 'phpbb_notification_type_bookmark',
				'item_id'			=> 5,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413325,
				'data'				=> array(
					'poster_id'		=> 2,
					'topic_title'	=> 'test-title',
					'post_subject'	=> 'Re: test-title',
					'post_username'	=> '',
					'forum_id'		=> 2,
					'forum_name'	=> 'Your first forum',
				),
			),
		);

		$this->assertEquals(sizeof($expected), $notifications['unread_count']);

		$notifications = $notifications['notifications'];

		foreach ($expected as $notification_id => $notification_data)
		{
			//echo $notifications[$notification_id];

			$this->assertEquals($notification_id, $notifications[$notification_id]->notification_id, 'notification_id');

			foreach ($notification_data as $key => $value)
			{
				$this->assertEquals($value, $notifications[$notification_id]->$key, $key . ' ' . $notification_id);
			}
		}

		// Now test updating -------------------------------

		$this->notifications->update_notifications('phpbb_ext_test_notification_type_test', array(
			'post_id'		=> '1',
			'topic_id'		=> '2', // change parent_id
			'post_time'		=> 1349413321,
		));

		$this->notifications->update_notifications('phpbb_ext_test_notification_type_test', array(
			'post_id'		=> '3',
			'topic_id'		=> '2',
			'post_time'		=> 1234, // change time
		));

		$this->notifications->update_notifications(array('phpbb_notification_type_quote', 'phpbb_notification_type_bookmark', 'phpbb_notification_type_post', 'phpbb_ext_test_notification_type_test'), array(
			'post_id'		=> '5',
			'topic_id'		=> '2',
			'poster_id'		=> 2,
			'topic_title'	=> 'test-title2', // change topic_title
			'post_subject'	=> 'Re: test-title2', // change post_subject
			'forum_id'		=> 3, // change forum_id
			'forum_name'	=> 'Your second forum', // change forum_name
		));

		$notifications = $this->notifications->load_notifications(array(
			'count_unread'	=> true,
		));

		$expected = array(
			1 => array(
				'item_type'			=> 'phpbb_ext_test_notification_type_test',
				'item_id'			=> 1,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413321,
				'data'				=> array(),
			),
			2 => array(
				'item_type'			=> 'phpbb_ext_test_notification_type_test',
				'item_id'			=> 2,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413322,
				'data'				=> array(),
			),
			3 => array(
				'item_type'			=> 'phpbb_ext_test_notification_type_test',
				'item_id'			=> 3,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1234,
				'data'				=> array(),
			),
			4 => array(
				'item_type'			=> 'phpbb_notification_type_post',
				'item_id'			=> 4,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413324,
				'data'				=> array(
					'poster_id'		=> 2,
					'topic_title'	=> 'test-title',
					'post_subject'	=> 'Re: test-title',
					'post_username'	=> '',
					'forum_id'		=> 2,
					'forum_name'	=> 'Your first forum',
				),
			),
			5 => array(
				'item_type'			=> 'phpbb_notification_type_bookmark',
				'item_id'			=> 5,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'unread'	   		=> 1,
				'time'	   			=> 1349413325,
				'data'				=> array(
					'poster_id'		=> 2,
					'topic_title'	=> 'test-title2',
					'post_subject'	=> 'Re: test-title2',
					'post_username'	=> '',
					'forum_id'		=> 3,
					'forum_name'	=> 'Your second forum',
				),
			),
		);

		$this->assertEquals(sizeof($expected), $notifications['unread_count']);

		$notifications = $notifications['notifications'];

		foreach ($expected as $notification_id => $notification_data)
		{
			//echo $notifications[$notification_id];

			$this->assertEquals($notification_id, $notifications[$notification_id]->notification_id, 'notification_id');

			foreach ($notification_data as $key => $value)
			{
				$this->assertEquals($value, $notifications[$notification_id]->$key, $key . ' ' . $notification_id);
			}
		}
	}
}
