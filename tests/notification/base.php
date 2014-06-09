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

require_once dirname(__FILE__) . '/manager_helper.php';

abstract class phpbb_tests_notification_base extends phpbb_database_test_case
{
	protected $notifications, $db, $container, $user, $config, $auth, $cache;

	protected function get_notification_types()
	{
		return array(
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
		);
	}

	protected function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once(__DIR__ . '/ext/test/notification/type/test.' . $phpEx);

		global $db, $config, $user, $auth, $cache, $phpbb_container;

		$db = $this->db = $this->new_dbal();
		$config = $this->config = new \phpbb\config\config(array(
			'allow_privmsg'			=> true,
			'allow_bookmarks'		=> true,
			'allow_topic_notify'	=> true,
			'allow_forum_notify'	=> true,
		));
		$user = $this->user = new \phpbb\user();
		$this->user_loader = new \phpbb\user_loader($this->db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$auth = $this->auth = new phpbb_mock_notifications_auth();
		$cache = $this->cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\null(),
			$this->config,
			$this->db,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_container = $this->container = new phpbb_mock_container_builder();

		$this->notifications = new phpbb_notification_manager_helper(
			array(),
			array(),
			$this->container,
			$this->user_loader,
			$this->config,
			$this->db,
			$this->cache,
			$this->user,
			$phpbb_root_path,
			$phpEx,
			'phpbb_notification_types',
			'phpbb_notifications',
			'phpbb_user_notifications'
		);

		$phpbb_container->set('notification_manager', $this->notifications);

		$this->notifications->setDependencies($this->auth, $this->config);

		$types = array();
		foreach ($this->get_notification_types() as $type)
		{
			$class = $this->build_type('phpbb\notification\type\\' . $type);

			$types[$type] = $class;
			$this->container->set('notification.type.' . $type, $class);
		}

		$this->notifications->set_var('notification_types', $types);

		$this->db->sql_query('DELETE FROM phpbb_notification_types');
		$this->db->sql_query('DELETE FROM phpbb_notifications');
		$this->db->sql_query('DELETE FROM phpbb_user_notifications');
	}

	protected function build_type($type)
	{
		global $phpbb_root_path, $phpEx;

		return new $type($this->user_loader, $this->db, $this->cache->get_driver(), $this->user, $this->auth, $this->config, $phpbb_root_path, $phpEx, 'phpbb_notification_types', 'phpbb_notifications', 'phpbb_user_notifications');
	}

	protected function assert_notifications($expected, $options = array())
	{
		$notifications = $this->notifications->load_notifications(array_merge(array(
			'count_unread'	=> true,
			'order_by'		=> 'notification_time',
			'order_dir'		=> 'ASC',
		), $options));

		$this->assertEquals(sizeof($expected), $notifications['unread_count']);

		$i = 0;
		foreach ($notifications['notifications'] as $notification)
		{
			foreach ($expected[$i] as $key => $value)
			{
				$this->assertEquals($value, $notification->$key, $i . ' ' . $key);
			}

			$i++;
		}
	}
}
