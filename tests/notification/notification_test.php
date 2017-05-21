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

require_once dirname(__FILE__) . '/base.php';

class phpbb_notification_test extends phpbb_tests_notification_base
{
	protected $notifications, $db, $container, $user, $config, $auth, $cache;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/notification.xml');
	}

	public function test_get_notification_type_id()
	{
		// They should be inserted the first time
		$post_type_id = $this->notifications->get_notification_type_id('notification.type.post');
		$quote_type_id = $this->notifications->get_notification_type_id('notification.type.quote');
		$test_type_id = $this->notifications->get_notification_type_id('test');

		self::assertEquals(array(
				'test'	=> $test_type_id,
				'notification.type.quote'	=> $quote_type_id,
				'notification.type.post'	=> $post_type_id,
			),
			$this->notifications->get_notification_type_ids(array(
				'test',
				'notification.type.quote',
				'notification.type.post',
			)
		));
		self::assertEquals($quote_type_id, $this->notifications->get_notification_type_id('notification.type.quote'));

		try
		{
			self::assertEquals(false, $this->notifications->get_notification_type_id('fail'));

			self::fail('Non-existent type should throw an exception');
		}
		catch (Exception $e) {}
	}

	public function test_get_subscription_types()
	{
		$subscription_types = $this->notifications->get_subscription_types();

		self::assertArrayHasKey('NOTIFICATION_GROUP_MISCELLANEOUS', $subscription_types);
		self::assertArrayHasKey('NOTIFICATION_GROUP_POSTING', $subscription_types);

		self::assertArrayHasKey('notification.type.bookmark', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		self::assertArrayHasKey('notification.type.post', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		self::assertArrayHasKey('notification.type.quote', $subscription_types['NOTIFICATION_GROUP_POSTING']);
		self::assertArrayHasKey('notification.type.topic', $subscription_types['NOTIFICATION_GROUP_POSTING']);

		self::assertArrayHasKey('notification.type.pm', $subscription_types['NOTIFICATION_GROUP_MISCELLANEOUS']);

		//get_subscription_types
		//get_subscription_methods
	}

	public function test_subscriptions()
	{
		$expected_subscriptions = array(
			'notification.type.post'		=> array('notification.method.board'),
			'notification.type.topic'		=> array('notification.method.board'),
			'notification.type.quote'		=> array('notification.method.board'),
			'notification.type.bookmark'	=> array('notification.method.board'),
			'test'		=> array('notification.method.board'),
			'notification.type.pm'		=> array('notification.method.board'),
		);

		$subscriptions = $this->notifications->get_global_subscriptions(2);
		foreach ($expected_subscriptions as $item_type => $methods)
		{
			self::assertArrayHasKey($item_type, $subscriptions);
			$this->assert_array_content_equals($methods, $subscriptions[$item_type]);
		}

		foreach ($subscriptions as $item_type => $methods)
		{
			$this->assert_array_content_equals($methods, $expected_subscriptions[$item_type]);
		}

		$this->notifications->delete_subscription('notification.type.post', 0, 'notification.method.board', 2);

		self::assertArrayNotHasKey('notification.type.post', $this->notifications->get_global_subscriptions(2));

		$this->notifications->add_subscription('notification.type.post', 0, 'notification.method.board', 2);

		self::assertArrayHasKey('notification.type.post', $this->notifications->get_global_subscriptions(2));
	}

	public function test_notifications()
	{
		$this->db->sql_query('DELETE FROM phpbb_notification_types');

		$types = array('notification.type.quote', 'notification.type.bookmark', 'notification.type.post', 'test');
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

		self::assertEquals(array(
				'notifications'		=> array(),
				'unread_count'		=> 0,
				'total_count'		=> 0,
		), $this->notifications->load_notifications('notification.method.board', array(
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

		$this->notifications->add_notifications(array('notification.type.quote', 'notification.type.bookmark', 'notification.type.post', 'test'), array(
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

		$this->notifications->add_notifications(array('notification.type.quote', 'notification.type.bookmark', 'notification.type.post', 'test'), array(
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

		$this->assert_notifications(
			array(
				array(
					'item_id'			=> 1,
					'item_parent_id'	=> 1,
					'user_id'	   		=> 0,
					'notification_read'				=> 0,
					'notification_time'	   			=> 1349413321,
					'notification_data'			   	=> array(),
				),
				array(
					'item_id'			=> 2,
					'item_parent_id'	=> 2,
					'user_id'	   		=> 0,
					'notification_read'				=> 0,
					'notification_time'	   			=> 1349413322,
					'notification_data'				=> array(),
				),
				array(
					'item_id'			=> 3,
					'item_parent_id'	=> 2,
					'user_id'	   		=> 0,
					'notification_read'				=> 0,
					'notification_time'	   			=> 1349413323,
					'notification_data'			   	=> array(),
				),
				array(
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
				array(
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
			)
		);

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

		$this->notifications->update_notifications(array('notification.type.quote', 'notification.type.bookmark', 'notification.type.post', 'test'), array(
			'post_id'		=> '5',
			'topic_id'		=> '2',
			'poster_id'		=> 2,
			'topic_title'	=> 'test-title2', // change topic_title
			'post_subject'	=> 'Re: test-title2', // change post_subject
			'forum_id'		=> 3, // change forum_id
			'forum_name'	=> 'Your second forum', // change forum_name
		));

		$this->assert_notifications(
			array(
				array(
					'item_id'			=> 3,
					'item_parent_id'	=> 2,
					'user_id'	   		=> 0,
					'notification_read'	=> 0,
					'notification_time'	=> 1234,
					'notification_data'	=> array(),
				),
				array(
					'item_id'			=> 1,
					'item_parent_id'	=> 2,
					'user_id'	   		=> 0,
					'notification_read'	=> 0,
					'notification_time'	=> 1349413321,
					'notification_data'	=> array(),
				),
				array(
					'item_id'			=> 2,
					'item_parent_id'	=> 2,
					'user_id'	   		=> 0,
					'notification_read'	=> 0,
					'notification_time'	=> 1349413322,
					'notification_data'	=> array(),
				),
				array(
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
				array(
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
			)
		);
	}
}
