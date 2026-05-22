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

use phpbb\notification\method\webpush;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once __DIR__ . '/base.php';

/**
 * @group slow
 */
class notification_method_webpush_test extends phpbb_tests_notification_base
{
	/** @var string[] VAPID keys for testing purposes */
	public const VAPID_KEYS = [
		'publicKey'		=> 'BIcGkq1Ncj3a2-J0UW-1A0NETLjvxZzNLiYBiPVMKNjgwmwPi5jyK87VfS4FZn9n7S9pLMQzjV3LmFuOnRSOvmI',
		'privateKey'	=> 'SrlbBEVgibWmKHYbDPu4Y2XvDWPjeGcc9fC16jq01xU',
	];

	/** @var webpush */
	protected $notification_method_webpush;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log_interface */
	protected $log;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/webpush_notification.type.post.xml');
	}

	protected function get_notification_methods()
	{
		return [
			'notification.method.webpush',
		];
	}

	public static function setUpBeforeClass(): void
	{
		self::start_webpush_testing();
	}

	public static function tearDownAfterClass(): void
	{
		self::stop_webpush_testing();
	}

	protected static function start_webpush_testing(): void
	{
		// Stop first to ensure port is available
		self::stop_webpush_testing();

		$process = new \Symfony\Component\Process\Process(['node_modules/.bin/web-push-testing', '--port', '9012', 'start']);
		$process->run();
		if (!$process->isSuccessful())
		{
			self::fail('Starting web push testing service failed: ' . $process->getErrorOutput());
		}
	}

	protected static function stop_webpush_testing(): void
	{
		$process = new \Symfony\Component\Process\Process(['node_modules/.bin/web-push-testing', '--port', '9012', 'stop']);
		$process->run();
	}

	protected function setUp(): void
	{
		phpbb_database_test_case::setUp();

		global $phpbb_root_path, $phpEx;

		include_once(__DIR__ . '/ext/test/notification/type/test.' . $phpEx);

		global $db, $config, $user, $auth, $cache, $phpbb_container, $phpbb_dispatcher;

		$avatar_helper = $this->getMockBuilder('\phpbb\avatar\helper')
							  ->disableOriginalConstructor()
							  ->getMock();
		$controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
								  ->disableOriginalConstructor()
								  ->getMock();
		$db = $this->db = $this->new_dbal();
		$config = $this->config = new \phpbb\config\config([
			'allow_privmsg'			=> true,
			'allow_bookmarks'		=> true,
			'allow_topic_notify'	=> true,
			'allow_forum_notify'	=> true,
			'allow_board_notifications'	=> true,
			'webpush_vapid_public'	=> self::VAPID_KEYS['publicKey'],
			'webpush_vapid_private'	=> self::VAPID_KEYS['privateKey'],
		]);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new \phpbb\language\language($lang_loader);
		$this->language->add_lang('acp/common');
		$user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->user = $user;
		$this->user->data['user_options'] = 230271;
		$this->user_loader = new \phpbb\user_loader($avatar_helper, $this->db, $phpbb_root_path, $phpEx, 'phpbb_users');
		$auth = $this->auth = new phpbb_mock_notifications_auth();
		$this->phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_dispatcher = $this->phpbb_dispatcher;
		$cache_driver = new \phpbb\cache\driver\dummy();
		$cache = $this->cache = new \phpbb\cache\service(
			$cache_driver,
			$this->config,
			$this->db,
			$this->phpbb_dispatcher,
			$phpbb_root_path,
			$phpEx
		);

		$log_table = 'phpbb_log';
		$this->log = new \phpbb\log\log($this->db, $user, $auth, $this->phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, $log_table);

		$phpbb_container = $this->container = new ContainerBuilder();
		$loader     = new YamlFileLoader($phpbb_container, new FileLocator(__DIR__ . '/fixtures'));
		$loader->load('services_notification.yml');
		$phpbb_container->set('avatar.helper', $avatar_helper);
		$phpbb_container->set('user_loader', $this->user_loader);
		$phpbb_container->set('user', $user);
		$phpbb_container->set('language', $this->language);
		$phpbb_container->set('config', $this->config);
		$phpbb_container->set('dbal.conn', $this->db);
		$phpbb_container->set('controller.helper', $this->createMock('\phpbb\controller\helper'));
		$phpbb_container->set('auth', $auth);
		$phpbb_container->set('cache.driver', $cache_driver);
		$phpbb_container->set('cache', $cache);
		$phpbb_container->set('log', $this->log);
		$phpbb_container->set('text_formatter.utils', new \phpbb\textformatter\s9e\utils());
		$phpbb_container->set('dispatcher', $this->phpbb_dispatcher);
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

		$ban_type_email = new \phpbb\ban\type\email($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_user = new \phpbb\ban\type\user($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$ban_type_ip = new \phpbb\ban\type\ip($this->db, 'phpbb_bans', 'phpbb_users', 'phpbb_sessions', 'phpbb_sessions_keys');
		$phpbb_container->set('ban.type.email', $ban_type_email);
		$phpbb_container->set('ban.type.user', $ban_type_user);
		$phpbb_container->set('ban.type.ip', $ban_type_ip);
		$collection = new \phpbb\di\service_collection($phpbb_container);
		$collection->add('ban.type.email');
		$collection->add('ban.type.user');
		$collection->add('ban.type.ip');
		$ban_manager = new \phpbb\ban\manager($collection, new \phpbb\cache\driver\dummy(), $this->db, $this->language, $this->log, $user, 'phpbb_bans', 'phpbb_users');
		$phpbb_container->set('ban.manager', $ban_manager);

		$messenger_method_collection = new \phpbb\di\service_collection($phpbb_container);
		$phpbb_container->set('messenger.method_collection', $messenger_method_collection);

		$this->notification_method_webpush = new \phpbb\notification\method\webpush(
			$phpbb_container->get('config'),
			$phpbb_container->get('dbal.conn'),
			$phpbb_container->get('log'),
			$phpbb_container->get('user_loader'),
			$phpbb_container->get('user'),
			$phpbb_root_path,
			$phpEx,
			$phpbb_container->getParameter('tables.notification_push'),
			$phpbb_container->getParameter('tables.push_subscriptions')
		);

		$phpbb_container->set('notification.method.webpush', $this->notification_method_webpush);

		$this->notifications = new phpbb_notification_manager_helper(
			array(),
			array(),
			$this->container,
			$this->user_loader,
			$this->phpbb_dispatcher,
			$this->db,
			$this->cache,
			$this->language,
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

	public static function data_notification_webpush()
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
	 * @dataProvider data_notification_webpush
	 */
	public function test_notification_webpush($notification_type, $post_data, $expected_users)
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

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals(0, count($notified_users), 'Assert no user has been notified yet');

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users, 'Assert that expected users have been notified');

		$post_data['post_id']++;
		$notification_options['item_id'] = $post_data['post_id'];
		$post_data['post_time'] = 1349413323;

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users2 = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users2, 'Assert that expected users stay the same after replying to same topic');
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_get_subscription($notification_type, $post_data, $expected_users): void
	{
		$subscription_info = [];
		foreach ($expected_users as $user_id => $user_data)
		{
			$subscription_info[$user_id][] = $this->create_subscription_for_user($user_id);
		}

		// Create second subscription for first user ID passed
		if (count($expected_users))
		{
			$first_user_id = array_key_first($expected_users);
			$subscription_info[$first_user_id][] = $this->create_subscription_for_user($first_user_id);
		}

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

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals(0, count($notified_users), 'Assert no user has been notified yet');

		foreach ($expected_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertEmpty($messages);
		}

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users, 'Assert that expected users have been notified');

		foreach ($expected_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertNotEmpty($messages, 'Failed asserting that user ' . $user_id . ' has received messages.');
		}
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_notify_empty_queue($notification_type, $post_data, $expected_users): void
	{
		foreach ($expected_users as $user_id => $user_data)
		{
			$this->create_subscription_for_user($user_id);
		}

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

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals(0, count($notified_users), 'Assert no user has been notified yet');

		$this->notification_method_webpush->notify(); // should have no effect

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals(0, count($notified_users), 'Assert no user has been notified yet');

		$post_data['post_id']++;
		$notification_options['item_id'] = $post_data['post_id'];
		$post_data['post_time'] = 1349413323;

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users2 = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users2, 'Assert that expected users stay the same after replying to same topic');
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_notify_invalid_endpoint($notification_type, $post_data, $expected_users): void
	{
		$subscription_info = [];
		foreach ($expected_users as $user_id => $user_data)
		{
			$subscription_info[$user_id][] = $this->create_subscription_for_user($user_id);
		}

		// Create second subscription for first user ID passed
		if (count($expected_users))
		{
			$first_user_id = array_key_first($expected_users);
			$first_user_sub = $this->create_subscription_for_user($first_user_id, true);
			$subscription_info[$first_user_id][] = $first_user_sub;
		}

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

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals(0, count($notified_users), 'Assert no user has been notified yet');

		foreach ($expected_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertEmpty($messages);
		}

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users, 'Assert that expected users have been notified');

		foreach ($expected_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertNotEmpty($messages, 'Failed asserting that user ' . $user_id . ' has received messages.');
		}

		if (isset($first_user_sub))
		{
			$admin_logs = $this->log->get_logs('admin');
			$this->db->sql_query('DELETE FROM phpbb_log'); // Clear logs
			$this->assertCount(1, $admin_logs, 'Assert that an admin log was created for invalid endpoint');

			$log_entry = $admin_logs[0];

			$this->assertStringStartsWith('<strong>Web Push message could not be sent:</strong>', $log_entry['action']);
			$this->assertStringContainsString('400', $log_entry['action']);
		}
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_notify_expired($notification_type, $post_data, $expected_users)
	{
		$subscription_info = [];
		foreach ($expected_users as $user_id => $user_data)
		{
			$subscription_info[$user_id][] = $this->create_subscription_for_user($user_id);
		}

		$expected_delivered_users = $expected_users;

		// Expire subscriptions for first user
		if (count($expected_users))
		{

			$first_user_id = array_key_first($expected_users);
			$first_user_subs = $subscription_info[$first_user_id];
			unset($expected_delivered_users[$first_user_id]);
			$this->expire_subscription($first_user_subs[0]['clientHash']);
		}

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

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals(0, count($notified_users), 'Assert no user has been notified yet');

		foreach ($expected_delivered_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertEmpty($messages);
		}

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users, 'Assert that expected users have been notified');

		foreach ($expected_delivered_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertNotEmpty($messages, 'Failed asserting that user ' . $user_id . ' has received messages.');
		}
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_expired_subscriptions_deleted($notification_type, $post_data, $expected_users): void
	{
		// Skip test if no expected users
		if (empty($expected_users))
		{
			$this->assertTrue(true);
			return;
		}

		$subscription_info = [];
		foreach ($expected_users as $user_id => $user_data)
		{
			$subscription_info[$user_id][] = $this->create_subscription_for_user($user_id);
		}

		// Get first user and expire their subscription
		$first_user_id = array_key_first($expected_users);
		$first_user_sub = $subscription_info[$first_user_id][0];
		$this->expire_subscription($first_user_sub['clientHash']);

		// Count subscriptions before notification
		$subscriptions_before = $this->get_subscription_count();
		$this->assertEquals(count($expected_users), $subscriptions_before, 'Expected ' . count($expected_users) . ' subscriptions before notification');

		$post_data = array_merge([
			'post_time' => 1349413322,
			'poster_id' => 1,
			'topic_title' => '',
			'post_subject' => '',
			'post_username' => '',
			'forum_name' => '',
		], $post_data);

		// Send notifications, which should trigger cleanup of expired subscription
		$this->notifications->add_notifications($notification_type, $post_data);

		// Count subscriptions after notification - expired one should be deleted
		$subscriptions_after = $this->get_subscription_count();
		$this->assertEquals(count($expected_users) - 1, $subscriptions_after, 'Expected expired subscription to be deleted');

		// Verify the expired subscription is actually gone
		$remaining_subs = $this->get_all_subscriptions();
		foreach ($remaining_subs as $sub)
		{
			$this->assertNotEquals($first_user_sub['endpoint'], $sub['endpoint'], 'Expired subscription should be deleted');
		}
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_permanently_removed_subscriptions_deleted($notification_type, $post_data, $expected_users): void
	{
		// Skip test if no expected users
		if (empty($expected_users))
		{
			$this->assertTrue(true);
			return;
		}

		// Insert a permanently-removed.invalid subscription for the first user.
		// This simulates a dead subscription whose endpoint can never resolve (RFC 6761).
		$first_user_id = array_key_first($expected_users);
		$dead_endpoint = 'https://permanently-removed.invalid/fcm/send/test_dead_subscription';
		$this->insert_subscription_for_user($first_user_id, $dead_endpoint);

		$this->assertEquals(1, $this->get_subscription_count(), 'Expected 1 subscription before notification');

		$post_data = array_merge([
			'post_time' => 1349413322,
			'poster_id' => 1,
			'topic_title' => '',
			'post_subject' => '',
			'post_username' => '',
			'forum_name' => '',
		], $post_data);

		// Send notifications — should trigger cleanup of the permanently-removed subscription
		$this->notifications->add_notifications($notification_type, $post_data);

		// The dead subscription should have been silently deleted
		$this->assertEquals(0, $this->get_subscription_count(), 'Expected permanently-removed subscription to be deleted');

		// Verify no admin log was written — unlike real delivery failures (which log errors),
		// permanently-removed endpoints should be silently cleaned up without noise.
		$admin_logs = $this->log->get_logs('admin');
		$this->assertEmpty($admin_logs, 'Expected no admin log entry for a permanently-removed subscription');
	}

	public function test_get_type(): void
	{
		$this->assertEquals('notification.method.webpush', $this->notification_method_webpush->get_type());
	}

	public function test_push_token_map_is_per_user(): void
	{
		// Verifies that when multiple users are notified about the same item,
		// each user's push token is stored and used independently.
		// Previously the map was keyed [type_id][item_id], so the last user's
		// token overwrote all others, making every other user's token invalid.
		$subscription_info = [];
		$expected_users = [2 => ['user_id' => '2'], 3 => ['user_id' => '3'], 4 => ['user_id' => '4']];
		foreach ($expected_users as $user_id => $user_data)
		{
			$subscription_info[$user_id] = $this->create_subscription_for_user($user_id);
		}

		$post_data = [
			'post_time'		=> 1349413322,
			'poster_id'		=> 1,
			'topic_title'	=> '',
			'post_subject'	=> '',
			'post_username'	=> '',
			'forum_name'	=> '',
			'forum_id'		=> '1',
			'post_id'		=> '2',
			'topic_id'		=> '1',
		];

		$this->notifications->add_notifications('notification.type.post', $post_data);

		// Fetch the stored rows only for users we created subscriptions for
		$webpush_table = $this->container->getParameter('tables.notification_push');
		$sql = 'SELECT user_id, push_token FROM ' . $webpush_table . ' WHERE ' . $this->db->sql_in_set('user_id', array_keys($expected_users)) . ' ORDER BY user_id ASC';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$this->assertCount(count($expected_users), $rows, 'Each expected user must have a notification row');
		$tokens = array_column($rows, 'push_token');
		$this->assertEquals(count($tokens), count(array_unique($tokens)), 'Each user must have a unique push_token');

		// Verify each message payload contains the token hashed with that specific user's salt
		foreach ($rows as $row)
		{
			$user_id = (int) $row['user_id'];
			$client_hash = basename($subscription_info[$user_id]['endpoint']);
			$messages = $this->get_messages_for_subscription($client_hash);
			$this->assertNotEmpty($messages);
			$payload = json_decode($messages[0], true);
			$user = $this->user_loader->get_user($user_id);
			$expected_token = hash('sha256', $user['user_form_salt'] . $row['push_token']);
			$this->assertEquals($expected_token, $payload['token'], 'Token in push payload must match hash of that user\'s salt + their own push_token');
		}
	}

	public function test_get_ucp_template_data_uses_millisecond_expiration_time(): void
	{
		$this->user->data['user_id'] = 2;
		$this->user->page['page'] = 'ucp.php?i=ucp_notifications';
		$this->config['load_notifications'] = true;
		$this->config['allow_board_notifications'] = true;
		$this->config['webpush_dropdown_subscribe'] = true;

		$sql = 'INSERT INTO phpbb_push_subscriptions ' . $this->db->sql_build_array('INSERT', [
				'user_id'			=> 2,
				'endpoint'			=> 'https://fcm.googleapis.com/fcm/send/test_endpoint',
				'expiration_time'	=> 42,
				'p256dh'			=> 'test_p256dh',
				'auth'				=> 'test_auth',
			]);
		$this->db->sql_query($sql);

		$controller_helper = $this->createMock(\phpbb\controller\helper::class);
		$controller_helper->method('route')->willReturnArgument(0);

		$form_helper = $this->createMock(phpbb\form\form_helper::class);
		$form_helper->method('get_form_tokens')->willReturn([
			'creation_time' => 1,
			'form_token' => 'test',
		]);

		$template_data = $this->notification_method_webpush->get_ucp_template_data($controller_helper, $form_helper);

		$this->assertSame([
			[
				'endpoint' => 'https://fcm.googleapis.com/fcm/send/test_endpoint',
				'expirationTime' => 42000,
			],
		], $template_data['SUBSCRIPTIONS']);
	}

	/**
	 * Test is_subscription_unauthorized method with various HTTP status codes
	 */
	public function test_is_subscription_unauthorized(): void
	{
		$reflection = new \ReflectionMethod($this->notification_method_webpush, 'is_subscription_unauthorized');

		// Test 401 status (should return true)
		$response_401 = $this->createMockResponse(401);
		$request_401 = $this->createMockRequest();
		$report_401 = new \Minishlink\WebPush\MessageSentReport($request_401, $response_401, false, 'Unauthorized');
		$this->assertTrue($reflection->invoke($this->notification_method_webpush, $report_401), 'Expected 401 to be treated as unauthorized');

		// Test 403 status (should return true for invalid VAPID/subscription mismatches)
		$response_403 = $this->createMockResponse(403);
		$request_403 = $this->createMockRequest();
		$report_403 = new \Minishlink\WebPush\MessageSentReport($request_403, $response_403, false, 'Forbidden');
		$this->assertTrue($reflection->invoke($this->notification_method_webpush, $report_403), 'Expected 403 to be treated as a permanent authorization failure');

		// Test 404 status (should return false, handled by isSubscriptionExpired)
		$response_404 = $this->createMockResponse(404);
		$request_404 = $this->createMockRequest();
		$report_404 = new \Minishlink\WebPush\MessageSentReport($request_404, $response_404, false, 'Not Found');
		$this->assertFalse($reflection->invoke($this->notification_method_webpush, $report_404), 'Expected 404 to not be treated as unauthorized');

		// Test 410 status (should return false, handled by isSubscriptionExpired)
		$response_410 = $this->createMockResponse(410);
		$request_410 = $this->createMockRequest();
		$report_410 = new \Minishlink\WebPush\MessageSentReport($request_410, $response_410, false, 'Gone');
		$this->assertFalse($reflection->invoke($this->notification_method_webpush, $report_410), 'Expected 410 to not be treated as unauthorized');

		// Test 429 status (should return false, temporary error)
		$response_429 = $this->createMockResponse(429);
		$request_429 = $this->createMockRequest();
		$report_429 = new \Minishlink\WebPush\MessageSentReport($request_429, $response_429, false, 'Too Many Requests');
		$this->assertFalse($reflection->invoke($this->notification_method_webpush, $report_429), 'Expected 429 to not be treated as unauthorized');

		// Test 500 status (should return false, temporary error)
		$response_500 = $this->createMockResponse(500);
		$request_500 = $this->createMockRequest();
		$report_500 = new \Minishlink\WebPush\MessageSentReport($request_500, $response_500, false, 'Internal Server Error');
		$this->assertFalse($reflection->invoke($this->notification_method_webpush, $report_500), 'Expected 500 to not be treated as unauthorized');

		// Test null response (network failure - should return false)
		$request_null = $this->createMockRequest();
		$report_null = new \Minishlink\WebPush\MessageSentReport($request_null, null, false, 'Network error');
		$this->assertFalse($reflection->invoke($this->notification_method_webpush, $report_null), 'Expected null response to not be treated as unauthorized');
	}

	/**
	 * Create a mock PSR-7 ResponseInterface with specified status code
	 */
	protected function createMockResponse(int $status_code): \Psr\Http\Message\ResponseInterface
	{
		$response = $this->getMockBuilder(\Psr\Http\Message\ResponseInterface::class)
			->getMock();
		$response->method('getStatusCode')
			->willReturn($status_code);
		return $response;
	}

	/**
	 * Create a mock PSR-7 RequestInterface
	 */
	protected function createMockRequest(): \Psr\Http\Message\RequestInterface
	{
		$uri = $this->getMockBuilder(\Psr\Http\Message\UriInterface::class)
			->getMock();
		$uri->method('__toString')
			->willReturn('http://localhost:9012/notify/test');

		$request = $this->getMockBuilder(\Psr\Http\Message\RequestInterface::class)
			->getMock();
		$request->method('getUri')
			->willReturn($uri);

		$body = $this->getMockBuilder(\Psr\Http\Message\StreamInterface::class)
			->getMock();
		$body->method('getContents')
			->willReturn('test payload');
		$request->method('getBody')
			->willReturn($body);

		return $request;
	}

	/**
	 * Test is_endpoint_permanently_removed method
	 */
	public function test_is_endpoint_permanently_removed(): void
	{
		$reflection = new \ReflectionMethod($this->notification_method_webpush, 'is_endpoint_permanently_removed');

		// .invalid TLD sentinel — should return true
		$this->assertTrue(
			$reflection->invoke($this->notification_method_webpush, 'https://permanently-removed.invalid/fcm/send/abc123'),
			'Expected permanently-removed.invalid to be treated as permanently removed'
		);

		// Any .invalid host — should return true
		$this->assertTrue(
			$reflection->invoke($this->notification_method_webpush, 'https://some-other.invalid/push/endpoint'),
			'Expected any .invalid host to be treated as permanently removed'
		);

		// Valid FCM endpoint — should return false
		$this->assertFalse(
			$reflection->invoke($this->notification_method_webpush, 'https://fcm.googleapis.com/fcm/send/abc123'),
			'Expected valid FCM endpoint to not be treated as permanently removed'
		);

		// Valid Mozilla endpoint — should return false
		$this->assertFalse(
			$reflection->invoke($this->notification_method_webpush, 'https://updates.push.services.mozilla.com/push/v1/abc123'),
			'Expected valid Mozilla endpoint to not be treated as permanently removed'
		);

		// Subdomain spoofing attempt (host ends in .invalid.attacker.com, not .invalid) — should return false
		$this->assertFalse(
			$reflection->invoke($this->notification_method_webpush, 'https://permanently-removed.invalid.attacker.com/push'),
			'Expected .invalid.attacker.com to not be treated as permanently removed'
		);

		// Empty/invalid URL — should return false
		$this->assertFalse(
			$reflection->invoke($this->notification_method_webpush, 'not_a_url'),
			'Expected unparseable URL to not be treated as permanently removed'
		);
	}

	/**
	 * @dataProvider data_notification_webpush
	 */
	public function test_prune_notifications($notification_type, $post_data, $expected_users): void
	{
		$subscription_info = [];
		foreach ($expected_users as $user_id => $user_data)
		{
			$subscription_info[$user_id][] = $this->create_subscription_for_user($user_id);
		}

		// Create second subscription for first user ID passed
		if (count($expected_users))
		{
			$first_user_id = array_key_first($expected_users);
			$subscription_info[$first_user_id][] = $this->create_subscription_for_user($first_user_id);
		}

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

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals(0, count($notified_users), 'Assert no user has been notified yet');

		foreach ($expected_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertEmpty($messages);
		}

		$this->notifications->add_notifications($notification_type, $post_data);

		$notified_users = $this->notification_method_webpush->get_notified_users($this->notifications->get_notification_type_id($notification_type), $notification_options);
		$this->assertEquals($expected_users, $notified_users, 'Assert that expected users have been notified');

		foreach ($expected_users as $user_id => $data)
		{
			$messages = $this->get_messages_for_subscription($subscription_info[$user_id][0]['clientHash']);
			$this->assertNotEmpty($messages, 'Failed asserting that user ' . $user_id . ' has received messages.');
		}

		// Prune notifications with 0 time, shouldn't change anything
		$prune_time = time();
		$this->notification_method_webpush->prune_notifications(0);
		$this->assertGreaterThanOrEqual($prune_time, $this->config->offsetGet('read_notification_last_gc'), 'Assert that prune time was set');

		$cur_notifications = $this->get_notifications();
		$this->assertSameSize($cur_notifications, $expected_users, 'Assert that no notifications have been pruned');

		// Prune only read not supported, will prune all
		$this->notification_method_webpush->prune_notifications($prune_time);
		$this->assertGreaterThanOrEqual($prune_time, $this->config->offsetGet('read_notification_last_gc'), 'Assert that prune time was set');

		$cur_notifications = $this->get_notifications();
		$this->assertCount(0, $cur_notifications, 'Assert that no notifications have been pruned');
	}

	public static function data_set_endpoint_padding(): array
	{
		return [
			[
				'foo.mozilla.com',
				webpush::MOZILLA_FALLBACK_PADDING
			],
			[
				'foo.mozaws.net',
				webpush::MOZILLA_FALLBACK_PADDING
			],
			[
				'foo.android.googleapis.com',
				\Minishlink\WebPush\Encryption::MAX_COMPATIBILITY_PAYLOAD_LENGTH,
			],
		];
	}

	/**
	 * @dataProvider data_set_endpoint_padding
	 */
	public function test_set_endpoint_padding($endpoint, $expected_padding): void
	{
		$web_push_reflection = new \ReflectionMethod($this->notification_method_webpush, 'set_endpoint_padding');

		$auth = [
			'VAPID' => [
				'subject' => generate_board_url(),
				'publicKey' => $this->config['webpush_vapid_public'],
				'privateKey' => $this->config['webpush_vapid_private'],
			],
		];

		$web_push = new \Minishlink\WebPush\WebPush($auth);

		$this->assertEquals(\Minishlink\WebPush\Encryption::MAX_COMPATIBILITY_PAYLOAD_LENGTH, $web_push->getAutomaticPadding());
		$web_push_reflection->invoke($this->notification_method_webpush, $web_push, $endpoint);
		$this->assertEquals($expected_padding, $web_push->getAutomaticPadding());
	}

	protected function create_subscription_for_user($user_id, bool $invalidate_endpoint = false): array
	{
		$client = new \GuzzleHttp\Client();
		try
		{
			$response = $client->request('POST', 'http://localhost:9012/subscribe', ['form_params' => [
				'applicationServerKey'	=> self::VAPID_KEYS['publicKey'],
			]]);
		}
		catch (\GuzzleHttp\Exception\GuzzleException $exception)
		{
			$this->fail('Failed getting subscription from web-push-testing client: ' . $exception->getMessage());
		}

		$subscription_return = \phpbb\json\sanitizer::decode((string) $response->getBody());
		$subscription_data = $subscription_return['data'];
		$this->assertNotEmpty($subscription_data['endpoint']);
		$this->assertStringStartsWith('http://localhost:9012/notify/', $subscription_data['endpoint']);
		$this->assertIsArray($subscription_data['keys']);

		if ($invalidate_endpoint)
		{
			$subscription_data['endpoint'] .= 'invalid';
		}

		$push_subscriptions_table = $this->container->getParameter('tables.push_subscriptions');

		$sql = 'INSERT INTO ' . $push_subscriptions_table  . ' ' . $this->db->sql_build_array('INSERT', [
			'user_id'		=> $user_id,
			'endpoint'		=> $subscription_data['endpoint'],
			'p256dh'		=> $subscription_data['keys']['p256dh'],
			'auth'			=> $subscription_data['keys']['auth'],
		]);
		$this->db->sql_query($sql);

		return $subscription_data;
	}

	protected function expire_subscription(string $client_hash): void
	{
		$client = new \GuzzleHttp\Client();
		try
		{
			$response = $client->request('POST', 'http://localhost:9012/expire-subscription/' . $client_hash);
		}
		catch (\GuzzleHttp\Exception\GuzzleException $exception)
		{
			$this->fail('Failed expiring subscription with web-push-testing client: ' . $exception->getMessage());
		}

		$subscription_return = \phpbb\json\sanitizer::decode((string) $response->getBody());
		$this->assertEquals(200, $response->getStatusCode(), 'Expected response status to be 200');
	}

	protected function get_messages_for_subscription($client_hash): array
	{
		$client = new \GuzzleHttp\Client();
		try
		{
			$response = $client->request('POST', 'http://localhost:9012/get-notifications', ['form_params' => [
				'clientHash'	=> $client_hash,
			]]);
		}
		catch (\GuzzleHttp\Exception\GuzzleException $exception)
		{
			$this->fail('Failed getting messages from web-push-testing client: ' . $exception->getMessage());
		}

		$response_data = json_decode($response->getBody()->getContents(), true);
		$this->assertNotEmpty($response_data);
		$this->assertArrayHasKey('data', $response_data);
		$this->assertArrayHasKey('messages', $response_data['data']);

		return $response_data['data']['messages'];
	}

	protected function get_notifications(): array
	{
		$webpush_table = $this->container->getParameter('tables.notification_push');
		$sql = 'SELECT * FROM ' . $webpush_table;
		$result = $this->db->sql_query($sql);
		$sql_ary = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $sql_ary;
	}

	protected function get_subscription_count(): int
	{
		$push_subscriptions_table = $this->container->getParameter('tables.push_subscriptions');
		$sql = 'SELECT COUNT(*) as count FROM ' . $push_subscriptions_table;
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('count');
		$this->db->sql_freeresult($result);

		return $count;
	}

	protected function get_all_subscriptions(): array
	{
		$push_subscriptions_table = $this->container->getParameter('tables.push_subscriptions');
		$sql = 'SELECT * FROM ' . $push_subscriptions_table;
		$result = $this->db->sql_query($sql);
		$sql_ary = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $sql_ary;
	}

	/**
	 * Create a real subscription via the push testing service for the given user, then overwrite
	 * its endpoint with the specified value. This gives a subscription with valid encryption keys
	 * (required for payload encryption) but an endpoint that will never resolve — used for testing
	 * dead/sentinel endpoints such as permanently-removed.invalid.
	 */
	protected function insert_subscription_for_user(int $user_id, string $endpoint): void
	{
		// Get a real subscription from the push testing service so the p256dh/auth keys are
		// valid base64url-encoded EC keys that the library can actually encrypt against.
		$subscription_data = $this->create_subscription_for_user($user_id);

		// Overwrite the endpoint to the dead one we want to test with.
		$push_subscriptions_table = $this->container->getParameter('tables.push_subscriptions');
		$sql = 'UPDATE ' . $push_subscriptions_table . "
			SET endpoint = '" . $this->db->sql_escape($endpoint) . "'
			WHERE user_id = " . (int) $user_id . "
				AND endpoint = '" . $this->db->sql_escape($subscription_data['endpoint']) . "'";
		$this->db->sql_query($sql);
	}
}
