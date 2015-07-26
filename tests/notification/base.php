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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once dirname(__FILE__) . '/manager_helper.php';

abstract class phpbb_tests_notification_base extends phpbb_database_test_case
{
	protected $notifications, $db, $container, $user, $config, $auth, $cache;

	protected function get_notification_types()
	{
		return array(
			'test',
			'notification.type.approve_post',
			'notification.type.approve_topic',
			'notification.type.bookmark',
			'notification.type.disapprove_post',
			'notification.type.disapprove_topic',
			'notification.type.pm',
			'notification.type.post',
			'notification.type.post_in_queue',
			'notification.type.quote',
			'notification.type.report_pm',
			'notification.type.report_pm_closed',
			'notification.type.report_post',
			'notification.type.report_post_closed',
			'notification.type.topic',
			'notification.type.topic_in_queue',
		);
	}

	protected function get_notification_methods()
	{
		return array(
			'notification.method.board',
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
			'allow_board_notifications'	=> true,
		));
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user = $user;
		$this->user_loader = new \phpbb\user_loader($this->db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$auth = $this->auth = new phpbb_mock_notifications_auth();
		$cache_driver = new \phpbb\cache\driver\dummy();
		$cache = $this->cache = new \phpbb\cache\service(
			$cache_driver,
			$this->config,
			$this->db,
			$phpbb_root_path,
			$phpEx
		);

		$this->phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$phpbb_container = $this->container = new ContainerBuilder();
		$loader     = new YamlFileLoader($phpbb_container, new FileLocator(__DIR__ . '/fixtures'));
		$loader->load('services_notification.yml');
		$phpbb_container->set('user_loader', $this->user_loader);
		$phpbb_container->set('user', $user);
		$phpbb_container->set('config', $this->config);
		$phpbb_container->set('dbal.conn', $this->db);
		$phpbb_container->set('auth', $auth);
		$phpbb_container->set('cache.driver', $cache_driver);
		$phpbb_container->set('cache', $cache);
		$phpbb_container->set('text_formatter.utils', new \phpbb\textformatter\s9e\utils());
		$phpbb_container->set('dispatcher', $this->phpbb_dispatcher);
		$phpbb_container->setParameter('core.root_path', $phpbb_root_path);
		$phpbb_container->setParameter('core.php_ext', $phpEx);
		$phpbb_container->setParameter('tables.notifications', 'phpbb_notifications');
		$phpbb_container->setParameter('tables.user_notifications', 'phpbb_user_notifications');
		$phpbb_container->setParameter('tables.notification_types', 'phpbb_notification_types');

		$this->notifications = new phpbb_notification_manager_helper(
			array(),
			array(),
			$this->container,
			$this->user_loader,
			$this->phpbb_dispatcher,
			$this->db,
			$this->cache,
			$this->user,
			'phpbb_notification_types',
			'phpbb_user_notifications'
		);

		$phpbb_container->set('notification_manager', $this->notifications);
		$phpbb_container->compile();

		$this->notifications->setDependencies($this->auth, $this->config);

		$types = array();
		foreach ($this->get_notification_types() as $type)
		{
			$class = $this->build_type($type);

			$types[$type] = $class;
		}

		$this->notifications->set_var('notification_types', $types);

		$methods = array();
		foreach ($this->get_notification_methods() as $method)
		{
			$class = $this->container->get($method);

			$methods[$method] = $class;
		}

		$this->notifications->set_var('notification_methods', $methods);

		$this->db->sql_query('DELETE FROM phpbb_notification_types');
		$this->db->sql_query('DELETE FROM phpbb_notifications');
		$this->db->sql_query('DELETE FROM phpbb_user_notifications');
	}

	protected function build_type($type)
	{
		$instance = $this->container->get($type);

		return $instance;
	}

	protected function assert_notifications($expected, $options = array())
	{
		$notifications = $this->notifications->load_notifications('notification.method.board', array_merge(array(
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
