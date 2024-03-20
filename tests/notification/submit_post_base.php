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

require_once __DIR__ . '/../../phpBB/includes/functions_posting.php';

abstract class phpbb_notification_submit_post_base extends phpbb_database_test_case
{
	protected $notifications, $db, $container, $user, $config, $auth, $cache;

	protected $item_type = '';

	protected $poll_data = array();
	protected $post_data = array(
		'forum_id'		=> 1,
		'topic_id'		=> 1,
		'topic_title'	=> 'topic_title',
		'icon_id'		=> 0,
		'enable_bbcode'		=> 0,
		'enable_smilies'	=> 0,
		'enable_urls'		=> 0,
		'enable_sig'		=> 0,
		'message'			=> '',
		'message_md5'		=> '',
		'attachment_data'	=> array(),
		'bbcode_bitfield'	=> '',
		'bbcode_uid'		=> '',
		'post_edit_locked'	=> false,
		'notify_set'		=> 0,
		'notify'			=> false,
		'forum_name'		=> 'Test forum name',
		//'force_approved_state'	=> 1,
	);

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/submit_post_' . $this->item_type . '.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $auth, $cache, $config, $db, $phpbb_container, $phpbb_dispatcher, $lang, $user, $request, $phpEx, $phpbb_root_path, $user_loader;

		// Database
		$this->db = $this->new_dbal();
		$db = $this->db;

		// Auth
		$auth = $this->createMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'),
				$this->anything())
			->will($this->returnValueMap(array(
				array('f_noapprove', 1, true),
				array('f_postcount', 1, true),
				array('m_edit', 1, false),
				array('f_mention', 1, true),
				array('u_mention', 0, true),
			)));

		// Config
		$config = new \phpbb\config\config(array(
			'num_topics' => 1,
			'num_posts' => 1,
			'allow_board_notifications'	=> true,
			'allow_mentions' => true,
			'board_startdate' => 1692429414,
		));

		// Event dispatcher
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$cache_driver = new \phpbb\cache\driver\dummy();
		$cache = new \phpbb\cache\service(
			$cache_driver,
			$config,
			$db,
			$phpbb_dispatcher,
			$phpbb_root_path,
			$phpEx
		);

		// Language
		$lang = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		// Storage
		$storage = $this->createMock('\phpbb\storage\storage');

		// User
		$user = $this->createMock('\phpbb\user');
		$user->ip = '';
		$user->data = array(
			'user_id'		=> 2,
			'username'		=> 'user-name',
			'is_registered'	=> true,
			'user_colour'	=> '',
			'user_lastmark'	=> 0,
		);

		// Request
		$request = new phpbb_mock_request();

		$avatar_helper = $this->getMockBuilder('\phpbb\avatar\helper')
			->disableOriginalConstructor()
			->getMock();

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user_loader = new \phpbb\user_loader($avatar_helper, $db, $phpbb_root_path, $phpEx, USERS_TABLE);

		// Container
		$phpbb_container = new ContainerBuilder();
		$loader     = new YamlFileLoader($phpbb_container, new FileLocator(__DIR__ . '/fixtures'));
		$loader->load('services_notification.yml');
		$phpbb_container->set('user_loader', $user_loader);
		$phpbb_container->set('user', $user);
		$phpbb_container->set('language', $lang);
		$phpbb_container->set('config', $config);
		$phpbb_container->set('dbal.conn', $db);
		$phpbb_container->set('auth', $auth);
		$phpbb_container->set('cache.driver', $cache_driver);
		$phpbb_container->set('cache', $cache);
		$phpbb_container->set('log', new \phpbb\log\dummy());
		$phpbb_container->set('text_formatter.utils', new \phpbb\textformatter\s9e\utils());
		$phpbb_container->set(
			'text_formatter.s9e.mention_helper',
			new \phpbb\textformatter\s9e\mention_helper(
				$this->db,
				$auth,
				$user,
				$phpbb_root_path,
				$phpEx
			)
		);
		$phpbb_container->set('event_dispatcher', $phpbb_dispatcher);
		$phpbb_container->set('storage.attachment', $storage);
		$phpbb_container->setParameter('core.root_path', $phpbb_root_path);
		$phpbb_container->setParameter('core.php_ext', $phpEx);
		$phpbb_container->setParameter('tables.notifications', 'phpbb_notifications');
		$phpbb_container->setParameter('tables.user_notifications', 'phpbb_user_notifications');
		$phpbb_container->setParameter('tables.notification_types', 'phpbb_notification_types');
		$phpbb_container->setParameter('tables.notification_emails', 'phpbb_notification_emails');
		$phpbb_container->setParameter('tables.notification_push', 'phpbb_notification_push');
		$phpbb_container->setParameter('tables.push_subscriptions', 'phpbb_push_subscriptions');
		$phpbb_container->set('content.visibility', new \phpbb\content_visibility($auth, $config, $phpbb_dispatcher, $db, $user, $phpbb_root_path, $phpEx, FORUMS_TABLE, POSTS_TABLE, TOPICS_TABLE, USERS_TABLE));
		$phpbb_container->addCompilerPass(new phpbb\di\pass\markpublic_pass());
		$phpbb_container->compile();

		// Notification Types
		$notification_types = array('quote', 'mention', 'bookmark', 'post', 'post_in_queue', 'topic', 'topic_in_queue', 'approve_topic', 'approve_post', 'forum');
		$notification_types_array = array();
		foreach ($notification_types as $type)
		{
			$class = $phpbb_container->get('notification.type.' . $type);
			$notification_types_array['notification.type.' . $type] = $class;
		}

		// Methods Types
		$notification_methods_array = array('notification.method.board' => $phpbb_container->get('notification.method.board'));

		// Notification Manager
		$phpbb_notifications = new \phpbb\notification\manager($notification_types_array, $notification_methods_array,
			$phpbb_container, $user_loader, $phpbb_dispatcher, $db, $cache, $lang, $user,
			NOTIFICATION_TYPES_TABLE, USER_NOTIFICATIONS_TABLE);
		$phpbb_container->set('notification_manager', $phpbb_notifications);
	}

	/**
	* @dataProvider submit_post_data
	*/
	public function test_submit_post($additional_post_data, $expected_before, $expected_after)
	{
		$sql = 'SELECT user_id, item_id, item_parent_id
			FROM ' . NOTIFICATIONS_TABLE . ' n, ' . NOTIFICATION_TYPES_TABLE . " nt
			WHERE nt.notification_type_name = '" . $this->item_type . "'
				AND n.notification_type_id = nt.notification_type_id
			ORDER BY user_id ASC, item_id ASC";
		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_before, $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);

		$poll_data = $this->poll_data;
		$post_data = array_merge($this->post_data, $additional_post_data);
		submit_post('reply', '', 'poster-name', POST_NORMAL, $poll_data, $post_data, false, false);

		$result = $this->db->sql_query($sql);
		$this->assertEquals($expected_after, $this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);
	}
}
