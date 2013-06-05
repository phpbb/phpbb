<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/manager_helper.php';

class phpbb_notification_test extends phpbb_database_test_case
{
	protected $notifications, $db, $container, $user, $config, $auth, $cache;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/notification.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once(__DIR__ . '/ext/test/notification/type/test.' . $phpEx);

		$this->db = $this->new_dbal();
		$this->config = new phpbb_config(array(
			'allow_privmsg'			=> true,
			'allow_bookmarks'		=> true,
			'allow_topic_notify'	=> true,
			'allow_forum_notify'	=> true,
		));
		$this->user = new phpbb_user();
		$this->user_loader = new phpbb_user_loader($this->db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$this->auth = new phpbb_mock_notifications_auth();
		$this->cache = new phpbb_cache_service(
			new phpbb_cache_driver_null(),
			$this->config,
			$this->db,
			$phpbb_root_path,
			$phpEx
		);

		$this->container = new phpbb_mock_container_builder();

		$this->notifications = new phpbb_notification_manager_helper(
			array(),
			array(),
			$this->container,
			$this->user_loader,
			$this->db,
			$this->cache,
			$this->user,
			$phpbb_root_path,
			$phpEx,
			'phpbb_notification_types',
			'phpbb_notifications',
			'phpbb_user_notifications'
		);

		$this->notifications->setDependencies($this->auth, $this->config);

		$types = array();
		foreach (array(
			'test',
			'approve_post',
			'approve_topic',
			'bookmark',
			'disapprove_post',
			'disapprove_topic',
			'pm',
			'post',
			'post_in_queue',
			'quote',
			'report_pm',
			'report_pm_closed',
			'report_post',
			'report_post_closed',
			'topic',
			'topic_in_queue',
		) as $type)
		{
			$class = $this->build_type('phpbb_notification_type_' . $type);

			$types[$type] = $class;
			$this->container->set('notification.type.' . $type, $class);
		}

		$this->notifications->set_var('notification_types', $types);
	}

	protected function build_type($type)
	{
		global $phpbb_root_path, $phpEx;

		return new $type($this->user_loader, $this->db, $this->cache->get_driver(), $this->user, $this->auth, $this->config, $phpbb_root_path, $phpEx, 'phpbb_notification_types', 'phpbb_notifications', 'phpbb_user_notifications');
	}

	public function test_get_notification_type_id()
	{
		// They should be inserted the first time
		$this->assertEquals(1, $this->notifications->get_notification_type_id('post'));
		$this->assertEquals(2, $this->notifications->get_notification_type_id('quote'));
		$this->assertEquals(3, $this->notifications->get_notification_type_id('test'));

		$this->assertEquals(array(
				'test'	=> 3,
				'quote'	=> 2,
				'post'	=> 1,
			),
			$this->notifications->get_notification_type_ids(array(
				'test',
				'quote',
				'post',
			)
		));
		$this->assertEquals(2, $this->notifications->get_notification_type_id('quote'));

		try
		{
			$this->assertEquals(3, $this->notifications->get_notification_type_id('fail'));

			$this->fail('Non-existent type should throw an exception');
		}
		catch (Exception $e) {}
	}

	public function test_get_subscription_types()
	{
		$subscription_types = $this->notifications->get_subscription_types();

		$this->assertArrayHasKey('NOTIFICATION_GROUP_MISCELLANEOUS', $subscription_types);
		$this->assertArrayHasKey('NOTIFICATION_GROUP_POSTING', $subscription_types);

		$this->assertArrayHasKey('bookmark', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		$this->assertArrayHasKey('post', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		$this->assertArrayHasKey('quote', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		$this->assertArrayHasKey('topic', $subscription_types['NOTIFICATION_GROUP_POSTING']);

		$this->assertArrayHasKey('pm', $subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS']);

		//get_subscription_types
		//get_subscription_methods
	}

	public function test_subscriptions()
	{
		$this->notifications->delete_subscription('post', 0, '', 2);

		$this->assertArrayNotHasKey('post', $this->notifications->get_global_subscriptions(2));

		$this->notifications->add_subscription('post', 0, '', 2);

		$this->assertArrayHasKey('post', $this->notifications->get_global_subscriptions(2));
	}

	public function test_notifications()
	{
		$this->db->sql_query('DELETE FROM phpbb_notification_types');

		$types = array('quote', 'bookmark', 'post', 'test');
		foreach ($types as $id => $type)
		{
			$this->db->sql_query('INSERT INTO phpbb_notification_types ' .
				$this->db->sql_build_array('INSERT', array(
					'notification_type_id'		=> ($id + 1),
					'notification_type_name'	=> $type,
					'notification_type_enabled'	=> 1,
				))
			);
		}

		// Used to test post notifications later
		$this->db->sql_query('INSERT INTO ' . TOPICS_WATCH_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
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

		$this->notifications->add_notifications('test', array(
			'post_id'		=> '1',
			'topic_id'		=> '1',
			'post_time'		=> 1349413321,
		));

		$this->notifications->add_notifications('test', array(
			'post_id'		=> '2',
			'topic_id'		=> '2',
			'post_time'		=> 1349413322,
		));

		$this->notifications->add_notifications('test', array(
			'post_id'		=> '3',
			'topic_id'		=> '2',
			'post_time'		=> 1349413323,
		));

		$this->notifications->add_notifications(array('quote', 'bookmark', 'post', 'test'), array(
			'post_id'		=> '4',
			'topic_id'		=> '2',
			'post_time'		=> 1349413324,
			'poster_id'		=> 2,
			'topic_title'	=> 'test-title',
			'post_subject'	=> 'Re: test-title',
			'forum_id'		=> 2,
			'forum_name'	=> 'Your first forum',
		));

		$this->db->sql_query('INSERT INTO ' . BOOKMARKS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
			'topic_id'			=> 2,
			'user_id'			=> 0,
		)));

		$this->notifications->add_notifications(array('quote', 'bookmark', 'post', 'test'), array(
			'post_id'		=> '5',
			'topic_id'		=> '2',
			'post_time'		=> 1349413325,
			'poster_id'		=> 2,
			'topic_title'	=> 'test-title',
			'post_subject'	=> 'Re: test-title',
			'forum_id'		=> 2,
			'forum_name'	=> 'Your first forum',
		));

		$this->notifications->delete_subscription('test');

		$this->notifications->add_notifications('test', array(
			'post_id'		=> '6',
			'topic_id'		=> '2',
			'post_time'		=> 1349413326,
		));

		$notifications = $this->notifications->load_notifications(array(
			'count_unread'	=> true,
		));

		$expected = array(
			1 => array(
				'notification_type_id'	=> 4,
				'item_id'			=> 1,
				'item_parent_id'	=> 1,
				'user_id'	   		=> 0,
				'notification_read'				=> 0,
				'notification_time'	   			=> 1349413321,
				'notification_data'			   	=> array(),
			),
			2 => array(
				'notification_type_id'	=> 4,
				'item_id'			=> 2,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'				=> 0,
				'notification_time'	   			=> 1349413322,
				'notification_data'				=> array(),
			),
			3 => array(
				'notification_type_id'	=> 4,
				'item_id'			=> 3,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'				=> 0,
				'notification_time'	   			=> 1349413323,
				'notification_data'			   	=> array(),
			),
			4 => array(
				'notification_type_id'	=> 3,
				'item_id'			=> 4,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'				=> 0,
				'notification_time'	   			=> 1349413324,
				'notification_data'			   	=> array(
					'poster_id'		=> 2,
					'topic_title'	=> 'test-title',
					'post_subject'	=> 'Re: test-title',
					'post_username'	=> '',
					'forum_id'		=> 2,
					'forum_name'	=> 'Your first forum',
				),
			),
			5 => array(
				'notification_type_id'	=> 2,
				'item_id'			=> 5,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'				=> 0,
				'notification_time'	   			=> 1349413325,
				'notification_data'			   	=> array(
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

		$this->notifications->update_notifications('test', array(
			'post_id'		=> '1',
			'topic_id'		=> '2', // change parent_id
			'post_time'		=> 1349413321,
		));

		$this->notifications->update_notifications('test', array(
			'post_id'		=> '3',
			'topic_id'		=> '2',
			'post_time'		=> 1234, // change time
		));

		$this->notifications->update_notifications(array('quote', 'bookmark', 'post', 'test'), array(
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
				'notification_type_id'	=> 4,
				'item_id'			=> 1,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'	=> 0,
				'notification_time'	=> 1349413321,
				'notification_data'	=> array(),
			),
			2 => array(
				'notification_type_id'	=> 4,
				'item_id'			=> 2,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'	=> 0,
				'notification_time'	=> 1349413322,
				'notification_data'	=> array(),
			),
			3 => array(
				'notification_type_id'	=> 4,
				'item_id'			=> 3,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'	=> 0,
				'notification_time'	=> 1234,
				'notification_data'	=> array(),
			),
			4 => array(
				'notification_type_id'	=> 3,
				'item_id'			=> 4,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'	=> 0,
				'notification_time'	=> 1349413324,
				'notification_data'	=> array(
					'poster_id'		=> 2,
					'topic_title'	=> 'test-title',
					'post_subject'	=> 'Re: test-title',
					'post_username'	=> '',
					'forum_id'		=> 2,
					'forum_name'	=> 'Your first forum',
				),
			),
			5 => array(
				'notification_type_id'	=> 2,
				'item_id'			=> 5,
				'item_parent_id'	=> 2,
				'user_id'	   		=> 0,
				'notification_read'	=> 0,
				'notification_time'	=> 1349413325,
				'notification_data'	=> array(
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
