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

require_once __DIR__ . '/base.php';

class phpbb_notification_manager_mark_by_id_test extends phpbb_tests_notification_base
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/notification.xml');
	}

	public function setUp(): void
	{
		parent::setUp();

		if (strpos($this->get_database_config()['dbms'], 'mssql') !== false)
		{
			$this->db->sql_query('SET IDENTITY_INSERT phpbb_notification_types ON;');
		}
	}

	public function tearDown(): void
	{
		if (strpos($this->get_database_config()['dbms'], 'mssql') !== false)
		{
			$this->db->sql_query('SET IDENTITY_INSERT phpbb_notification_types OFF;');
		}

		parent::tearDown();
	}

	/**
	* Test mark_notifications_by_id with a single notification ID
	*/
	public function test_mark_notification_by_single_id()
	{
		// Add a test notification
		$this->db->sql_query('DELETE FROM phpbb_notification_types');
		$this->db->sql_query('INSERT INTO phpbb_notification_types ' .
			$this->db->sql_build_array('INSERT', array(
				'notification_type_id'		=> 1,
				'notification_type_name'	=> 'test',
				'notification_type_enabled'	=> 1,
			))
		);

		$this->notifications->add_notifications('test', array(
			'post_id'		=> 1,
			'topic_id'		=> 1,
			'post_time'		=> 1234567890,
		));

		// Load notifications to get the notification ID
		$loaded = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(1, $loaded['unread_count']);
		$notification_id = key($loaded['notifications']);

		// Mark as read
		$this->notifications->mark_notifications_by_id('notification.method.board', $notification_id, false, true);

		// Verify it's marked as read
		$loaded_after = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(0, $loaded_after['unread_count']);

		// Mark as unread again
		$this->notifications->mark_notifications_by_id('notification.method.board', $notification_id, false, false);


		// Verify it's marked as unread again
		$loaded_finally = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));
		$this->assertEquals(1, $loaded_finally['unread_count']);
	}

	/**
	* Test mark_notifications_by_id with multiple notification IDs
	*/
	public function test_mark_notifications_by_multiple_ids()
	{
		// Add test notifications
		$this->db->sql_query('DELETE FROM phpbb_notification_types');
		$this->db->sql_query('INSERT INTO phpbb_notification_types ' .
			$this->db->sql_build_array('INSERT', array(
				'notification_type_id'		=> 1,
				'notification_type_name'	=> 'test',
				'notification_type_enabled'	=> 1,
			))
		);

		$this->notifications->add_notifications('test', array(
			'post_id'		=> 1,
			'topic_id'		=> 1,
			'post_time'		=> 1234567890,
		));

		$this->notifications->add_notifications('test', array(
			'post_id'		=> 2,
			'topic_id'		=> 1,
			'post_time'		=> 1234567891,
		));

		$this->notifications->add_notifications('test', array(
			'post_id'		=> 3,
			'topic_id'		=> 1,
			'post_time'		=> 1234567892,
		));

		// Load notifications to get notification IDs
		$loaded = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(3, $loaded['unread_count']);
		$notification_ids = array_keys($loaded['notifications']);

		// Mark multiple notifications as read
		$this->notifications->mark_notifications_by_id('notification.method.board', $notification_ids, false, true);

		// Verify they're all marked as read
		$loaded_after = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(0, $loaded_after['unread_count']);
	}

	/**
	* Test mark_notifications_by_id with time constraint
	*/
	public function test_mark_notifications_by_id_with_time()
	{
		// Add test notifications
		$this->db->sql_query('DELETE FROM phpbb_notification_types');
		$this->db->sql_query('INSERT INTO phpbb_notification_types ' .
			$this->db->sql_build_array('INSERT', array(
				'notification_type_id'		=> 1,
				'notification_type_name'	=> 'test',
				'notification_type_enabled'	=> 1,
			))
		);

		$old_time = 1234567890;
		$new_time = 1234567900;

		$this->notifications->add_notifications('test', array(
			'post_id'		=> 1,
			'topic_id'		=> 1,
			'post_time'		=> $old_time,
		));

		$this->notifications->add_notifications('test', array(
			'post_id'		=> 2,
			'topic_id'		=> 1,
			'post_time'		=> $new_time,
		));

		// Load notifications to get notification IDs
		$loaded = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(2, $loaded['unread_count']);
		$notification_ids = array_keys($loaded['notifications']);

		// Mark notifications as read with time constraint (only old ones)
		$this->notifications->mark_notifications_by_id('notification.method.board', $notification_ids, $old_time, true);

		// Verify only old notification is marked as read
		$loaded_after = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(1, $loaded_after['unread_count']);
	}

	/**
	* Test mark_notifications_by_id with unavailable method
	*/
	public function test_mark_notifications_by_id_unavailable_method()
	{
		// Add a test notification
		$this->db->sql_query('DELETE FROM phpbb_notification_types');
		$this->db->sql_query('INSERT INTO phpbb_notification_types ' .
			$this->db->sql_build_array('INSERT', array(
				'notification_type_id'		=> 1,
				'notification_type_name'	=> 'test',
				'notification_type_enabled'	=> 1,
			))
		);

		$this->notifications->add_notifications('test', array(
			'post_id'		=> 1,
			'topic_id'		=> 1,
			'post_time'		=> 1234567890,
		));

		// Load notifications
		$loaded = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(1, $loaded['unread_count']);
		$notification_id = key($loaded['notifications']);

		// Try to mark with non-existent method - should throw exception from container
		try
		{
			$this->notifications->mark_notifications_by_id('notification.method.nonexistent', $notification_id, false, true);
			$this->fail('Expected exception for non-existent method');
		}
		catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $e)
		{
			// Expected exception - test passes
			$this->assertTrue(true);
		}

		// Verify notification is still unread
		$loaded_after = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(1, $loaded_after['unread_count']);
	}

	/**
	* Test mark_notifications_by_id with non-existent notification ID
	*/
	public function test_mark_notifications_by_id_nonexistent_id()
	{
		// Add a test notification
		$this->db->sql_query('DELETE FROM phpbb_notification_types');
		$this->db->sql_query('INSERT INTO phpbb_notification_types ' .
			$this->db->sql_build_array('INSERT', array(
				'notification_type_id'		=> 1,
				'notification_type_name'	=> 'test',
				'notification_type_enabled'	=> 1,
			))
		);

		$this->notifications->add_notifications('test', array(
			'post_id'		=> 1,
			'topic_id'		=> 1,
			'post_time'		=> 1234567890,
		));

		// Try to mark a non-existent notification ID (should not affect existing notifications)
		$this->notifications->mark_notifications_by_id('notification.method.board', 999999, false, true);

		// Verify notification is still unread
		$loaded = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
		));

		$this->assertEquals(1, $loaded['unread_count']);
	}

	/**
	* Test that a user cannot mark another user's notification as read
	*/
	public function test_mark_notifications_by_id_wrong_user()
	{
		// Setup notification type
		$this->db->sql_query('DELETE FROM phpbb_notification_types');
		$this->db->sql_query('INSERT INTO phpbb_notification_types ' .
			$this->db->sql_build_array('INSERT', array(
				'notification_type_id'		=> 1,
				'notification_type_name'	=> 'test',
				'notification_type_enabled'	=> 1,
			))
		);

		// Add subscription for user 2
		$this->notifications->add_subscription('test', 0, 'notification.method.board', 2);

		// Create a notification for user 2
		$this->notifications->add_notifications_for_users('test', array(
			'post_id'		=> 1,
			'topic_id'		=> 1,
			'post_time'		=> 1234567890,
		), array(
			2	=> array('notification.method.board'),
		));

		// Load notifications for user 2 to get the notification ID
		$loaded_user2 = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
			'user_id'		=> 2,
		));

		$this->assertEquals(1, $loaded_user2['unread_count'], 'User 2 should have 1 unread notification');
		$notification_id = key($loaded_user2['notifications']);

		// Verify the notification belongs to user 2
		$sql = 'SELECT user_id, notification_read
			FROM phpbb_notifications
			WHERE notification_id = ' . (int) $notification_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals(2, $row['user_id'], 'Notification should belong to user 2');
		$this->assertEquals(0, $row['notification_read'], 'Notification should be unread initially');

		// Try to mark user 2's notification as read while being other user
		$userReflection = new \ReflectionProperty($this->notifications, 'user');
		$userReflection->setAccessible(true);
		$user = $userReflection->getValue($this->notifications);
		$user->data['id'] = 3;
		$this->notifications->mark_notifications_by_id('notification.method.board', $notification_id);

		// Check if the notification was actually marked as read
		$sql = 'SELECT notification_read
			FROM phpbb_notifications
			WHERE notification_id = ' . (int) $notification_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals(0, $row['notification_read'], 'Notification should remain unread - user 0 cannot mark user 2 notification');

		// Verify user 2's notification count remains unchanged
		$loaded_user2_after = $this->notifications->load_notifications('notification.method.board', array(
			'count_unread'	=> true,
			'user_id'		=> 2,
		));

		$this->assertEquals(1, $loaded_user2_after['unread_count'], 'User 2 should still have 1 unread notification');
	}
}





