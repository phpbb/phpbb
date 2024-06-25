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

require_once __DIR__ . '/base.php';

class notification_method_email_test extends phpbb_tests_notification_base
{
	/** @var \phpbb\notification\method\email */
	protected $notification_method_email;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/email_notification.type.post.xml');
	}

	protected function get_notification_methods()
	{
		return [
			'notification.method.email',
		];
	}

	protected function setUp(): void
	{
		phpbb_database_test_case::setUp();

		global $phpbb_root_path, $phpEx;

		include_once(__DIR__ . '/ext/test/notification/type/test.' . $phpEx);

		global $db, $config, $user, $auth, $cache, $phpbb_container;

		$avatar_helper = $this->getMockBuilder('\phpbb\avatar\helper')
							  ->disableOriginalConstructor()
							  ->getMock();
		$db = $this->db = $this->new_dbal();
		$config = $this->config = new \phpbb\config\config([
			'allow_privmsg'			=> true,
			'allow_bookmarks'		=> true,
			'allow_topic_notify'	=> true,
			'allow_forum_notify'	=> true,
			'allow_board_notifications'	=> true,
		]);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$this->user = $user;
		$this->user_loader = new \phpbb\user_loader($avatar_helper, $this->db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$auth = $this->auth = new phpbb_mock_notifications_auth();
		$this->phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$cache_driver = new \phpbb\cache\driver\dummy();
		$cache = $this->cache = new \phpbb\cache\service(
			$cache_driver,
			$this->config,
			$this->db,
			$this->phpbb_dispatcher,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_container = $this->container = new ContainerBuilder();
		$loader     = new YamlFileLoader($phpbb_container, new FileLocator(__DIR__ . '/fixtures'));
		$loader->load('services_notification.yml');
		$phpbb_container->set('user_loader', $this->user_loader);
		$phpbb_container->set('user', $user);
		$phpbb_container->set('language', $lang);
		$phpbb_container->set('config', $this->config);
		$phpbb_container->set('dbal.conn', $this->db);
		$phpbb_container->set('auth', $auth);
		$phpbb_container->set('cache.driver', $cache_driver);
		$phpbb_container->set('cache', $cache);
		$phpbb_container->set('log', new \phpbb\log\dummy());
		$phpbb_container->set('text_formatter.utils', new \phpbb\textformatter\s9e\utils());
		$phpbb_container->set('event_dispatcher', $this->phpbb_dispatcher);
		$phpbb_container->setParameter('core.root_path', $phpbb_root_path);
		$phpbb_container->setParameter('core.php_ext', $phpEx);
		$phpbb_container->setParameter('tables.notifications', 'phpbb_notifications');
		$phpbb_container->setParameter('tables.user_notifications', 'phpbb_user_notifications');
		$phpbb_container->setParameter('tables.notification_types', 'phpbb_notification_types');
		$phpbb_container->setParameter('tables.notification_emails', 'phpbb_notification_emails');
		$phpbb_container->setParameter('tables.notification_push', 'phpbb_notification_push');
		$phpbb_container->setParameter('tables.push_subscriptions', 'phpbb_push_subscriptions');
		$phpbb_container->set(
			'text_formatter.s9e.mention_helper',
			new \phpbb\textformatter\s9e\mention_helper(
				$this->db,
				$auth,
				$this->user,
				$phpbb_root_path,
				$phpEx
			)
		);

		$this->notification_method_email = $this->getMockBuilder('\phpbb\notification\method\email')
			->setConstructorArgs([
				$phpbb_container->get('user_loader'),
				$phpbb_container->get('user'),
				$phpbb_container->get('config'),
				$phpbb_container->get('dbal.conn'),
				$phpbb_root_path,
				$phpEx,
				$phpbb_container->getParameter('tables.notification_emails')
			])
			->setMethods(['notify_using_messenger'])
			->getMock();
		$notification_method_email = $this->notification_method_email;

		$class = new ReflectionClass($notification_method_email);
		$empty_queue_method = $class->getMethod('empty_queue');
		$empty_queue_method->setAccessible(true);

		$this->notification_method_email->method('notify_using_messenger')
			->will($this->returnCallback(function () use ($notification_method_email, $empty_queue_method) {
				$empty_queue_method->invoke($notification_method_email);
			}));

		$phpbb_container->set('notification.method.email', $this->notification_method_email);

		$this->notifications = new phpbb_notification_manager_helper(
			array(),
			array(),
			$this->container,
			$this->user_loader,
			$this->phpbb_dispatcher,
			$this->db,
			$this->cache,
			$lang,
			$this->user,
			'phpbb_notification_types',
			'phpbb_user_notifications'
		);

		$phpbb_container->set('notification_manager', $this->notifications);

		$phpbb_container->addCompilerPass(new phpbb\di\pass\markpublic_pass());

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
	}

	public function data_notification_email()
	{
		return [
			/**
			* Normal post
			*
			* User => State description
			*	2	=> Topic id=1 and id=2 subscribed, should receive a new topics post notification
			*	3	=> Topic id=1 subscribed, should receive a new topic post notification
			*	4	=> Topic id=1 subscribed, should receive a new topic post notification
			*	5	=> Topic id=1 subscribed, post id=1 already notified, should receive a new topic post notification
			*	6	=> Topic id=1 and forum id=1 subscribed, should receive a new topic/forum post notification
			*	7	=> Forum id=1 subscribed, should NOT receive a new topic post but a forum post notification
			*	8	=> Forum id=1 subscribed, post id=1 already notified, should NOT receive a new topic post but a forum post notification
			*/
			[
				'notification.type.post',
				[
					'forum_id'		=> '1',
					'post_id'		=> '2',
					'topic_id'		=> '1',
				],
				[
					2 => ['user_id' => '2'],
					3 => ['user_id' => '3'],
					4 => ['user_id' => '4'],
					5 => ['user_id' => '5'],
					6 => ['user_id' => '6'],
				],
			],
			[
				'notification.type.forum',
				[
					'forum_id'		=> '1',
					'post_id'		=> '3',
					'topic_id'		=> '1',
				],
				[
					6 => ['user_id' => '6'],
					7 => ['user_id' => '7'],
					8 => ['user_id' => '8']
				],
			],
			[
				'notification.type.post',
				[
					'forum_id'		=> '1',
					'post_id'		=> '4',
					'topic_id'		=> '2',
				],
				[
					2 => ['user_id' => '2'],
				],
			],
			[
				'notification.type.forum',
				[
					'forum_id'		=> '1',
					'post_id'		=> '5',
					'topic_id'		=> '2',
				],
				[
					6 => ['user_id' => '6'],
					7 => ['user_id' => '7'],
					8 => ['user_id' => '8'],
				],
			],
			[
				'notification.type.post',
				[
					'forum_id'		=> '2',
					'post_id'		=> '6',
					'topic_id'		=> '3',
				],
				[
				],
			],
			[
				'notification.type.forum',
				[
					'forum_id'		=> '2',
					'post_id'		=> '6',
					'topic_id'		=> '3',
				],
				[
				],
			],
		];
	}

	/**
	 * @dataProvider data_notification_email
	 */
	public function test_notification_email($notification_type, $post_data, $expected_users)
	{
		$post_data = array_merge([
				'post_time' => 1349413322,
				'poster_id' => 1,
				'topic_title' => '',
				'post_subject' => '',
				'post_username' => '',
				'forum_name' => '',
			],

			$post_data);
		$notification_options = [
			'item_id'			=> $post_data['post_id'],
			'item_parent_id'	=> $post_data['topic_id'],
		];

		$notified_users = $this->notification_method_email->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals(0, count($notified_users), 'Assert no user has been notified yet');

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users = $this->notification_method_email->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users, 'Assert that expected users have been notified');

		$post_data['post_id']++;
		$notification_options['item_id'] = $post_data['post_id'];
		$post_data['post_time'] = 1349413323;

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users2 = $this->notification_method_email->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users2, 'Assert that expected users stay the same after replying to same topic');
	}
}
