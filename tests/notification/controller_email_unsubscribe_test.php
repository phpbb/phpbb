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

use phpbb\config\config;
use phpbb\notification\controller\email_unsubscribe;
use phpbb\notification\manager;
use phpbb\notification\method\email;
use phpbb\notification\type\approve_post;
use phpbb\notification\type\post;
use Symfony\Component\HttpFoundation\Response;

class phpbb_notification_controller_email_unsubscribe_test extends phpbb_database_test_case
{
	protected const EMAIL = 'notification.method.email';

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var email */
	protected $email_method;

	/** @var manager */
	protected $manager;

	/** @var array Template variables of the rendered page */
	protected $template_vars;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/unsubscribe.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->template_vars = [];

		$this->email_method = new email(
			$this->createMock(\phpbb\user_loader::class),
			$this->createMock(\phpbb\user::class),
			new config(['email_unsubscribe_secret' => 'secret']),
			$this->createMock(\phpbb\db\driver\driver_interface::class),
			'',
			'php',
			'phpbb_notification_emails',
			$this->createMock(\phpbb\di\service_collection::class)
		);

		$this->manager = new class(
			[],
			[],
			$this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class),
			$this->createMock(\phpbb\user_loader::class),
			$this->createMock(\phpbb\event\dispatcher_interface::class),
			$this->db,
			$this->createMock(\phpbb\cache\service::class),
			$this->createMock(\phpbb\language\language::class),
			$this->createMock(\phpbb\user::class),
			'phpbb_notification_types',
			'phpbb_user_notifications'
		) extends manager
		{
			public function get_item_type_class($notification_type_name, $data = [])
			{
				$classes = [
					'notification.type.post'			=> post::class,
					'notification.type.approve_post'	=> approve_post::class,
				];

				if (!isset($classes[$notification_type_name]))
				{
					throw new \RuntimeException('Service not found');
				}

				return (new \ReflectionClass($classes[$notification_type_name]))->newInstanceWithoutConstructor();
			}
		};
	}

	protected function handle(int $user_id, string $type = 'notification.type.post', string $mode = 'topic', int $item_id = 1, array $post = [], array $overrides = []): Response
	{
		$email = ['2' => 'two@example.com', '3' => 'three@example.com', '4' => 'four@example.com'][$user_id];
		$params = $this->email_method->get_unsubscribe_params(['user_id' => $user_id, 'user_email' => $email], $type, $mode, $item_id);

		$helper = $this->createMock(\phpbb\controller\helper::class);
		$helper->method('route')->willReturn('action');
		$helper->method('render')->willReturnCallback(function () {
			return new Response('page');
		});
		$helper->method('message')->willReturnCallback(function ($message, $parameters, $title, $code) {
			return new Response($message, $code);
		});

		$language = $this->createMock(\phpbb\language\language::class);
		$language->method('lang')->willReturnArgument(0);

		$template = $this->createMock(\phpbb\template\template::class);
		$template->method('assign_vars')->willReturnCallback(function (array $vars) use ($template) {
			$this->template_vars = $vars;

			return $template;
		});

		$controller = new email_unsubscribe(
			$this->db,
			$this->email_method,
			$helper,
			$language,
			new \phpbb\log\dummy(),
			$this->manager,
			new phpbb_mock_request(array_merge($params, $overrides), $post),
			$template,
			$this->createMock(\phpbb\user::class),
			'phpbb_forums',
			'phpbb_forums_watch',
			'phpbb_topics',
			'phpbb_topics_watch',
			'phpbb_users'
		);

		return $controller->handle();
	}

	protected function get_notify(int $user_id, string $item_type, string $method = self::EMAIL): int|false
	{
		$sql = 'SELECT notify
			FROM phpbb_user_notifications
			WHERE user_id = ' . $user_id . "
				AND item_type = '" . $this->db->sql_escape($item_type) . "'
				AND method = '" . $this->db->sql_escape($method) . "'";
		$result = $this->db->sql_query($sql);
		$notify = $this->db->sql_fetchfield('notify');
		$this->db->sql_freeresult($result);

		return $notify === false ? false : (int) $notify;
	}

	protected function get_massemail(int $user_id): int
	{
		$result = $this->db->sql_query('SELECT user_allow_massemail FROM phpbb_users WHERE user_id = ' . $user_id);
		$massemail = $this->db->sql_fetchfield('user_allow_massemail');
		$this->db->sql_freeresult($result);

		return (int) $massemail;
	}

	protected function get_watch_status(string $table, string $column, int $user_id): int|false
	{
		$result = $this->db->sql_query("SELECT notify_status FROM $table WHERE $column = 1 AND user_id = $user_id");
		$status = $this->db->sql_fetchfield('notify_status');
		$this->db->sql_freeresult($result);

		return $status === false ? false : (int) $status;
	}

	public function test_invalid_signature_changes_nothing(): void
	{
		$response = $this->handle(2, post: ['List-Unsubscribe' => 'One-Click'], overrides: ['k' => str_repeat('0', 64)]);

		$this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
		$this->assertSame(1, $this->get_notify(2, 'notification.type.post'));
	}

	public function test_responses_keep_link_private(): void
	{
		$response = $this->handle(2, post: ['List-Unsubscribe' => 'One-Click']);

		$this->assertSame('no-referrer', $response->headers->get('Referrer-Policy'));
		$this->assertSame('noindex', $response->headers->get('X-Robots-Tag'));
		$this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
	}

	public function test_one_click_stops_type_only(): void
	{
		$response = $this->handle(2, post: ['List-Unsubscribe' => 'One-Click']);

		$this->assertSame(Response::HTTP_OK, $response->getStatusCode());
		$this->assertSame(0, $this->get_notify(2, 'notification.type.post'));
		$this->assertSame(1, $this->get_notify(2, 'moderation_queue'));
		$this->assertSame(1, $this->get_notify(2, 'notification.type.post', 'notification.method.board'));
		$this->assertSame(1, $this->get_notify(3, 'notification.type.post'));
		$this->assertSame(1, $this->get_massemail(2));
		$this->assertSame(1, $this->get_watch_status('phpbb_topics_watch', 'topic_id', 2));
	}

	public function test_one_click_uses_shared_preference(): void
	{
		$this->handle(2, 'notification.type.approve_post', '', 0, ['List-Unsubscribe' => 'One-Click']);

		$this->assertSame(0, $this->get_notify(2, 'moderation_queue'));
		$this->assertFalse($this->get_notify(2, 'notification.type.approve_post'));
		$this->assertSame(1, $this->get_notify(2, 'notification.type.post'));
	}

	public function test_one_click_with_removed_type(): void
	{
		$response = $this->handle(2, 'notification.type.removed', '', 0, ['List-Unsubscribe' => 'One-Click']);

		$this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
	}

	public function test_page_without_chosen_scope_changes_nothing(): void
	{
		$response = $this->handle(2, 'notification.type.post', '', 0, ['unsubscribe' => 'Unsubscribe']);

		$this->assertSame('page', $response->getContent());
		$this->assertArrayNotHasKey('S_EMAIL_UNSUBSCRIBE_DONE', $this->template_vars);
		$this->assertSame(1, $this->get_notify(2, 'notification.type.post'));
		$this->assertSame(1, $this->get_massemail(2));
	}

	public function test_item_scope_needs_watched_item(): void
	{
		$this->handle(4, 'notification.type.post', '', 0, ['unsubscribe' => 'Unsubscribe', 'scope' => 'item']);

		$this->assertArrayNotHasKey('S_EMAIL_UNSUBSCRIBE_DONE', $this->template_vars);
	}

	public function test_unsubscribe_topic(): void
	{
		$this->handle(2, post: ['unsubscribe' => 'Unsubscribe', 'scope' => 'item']);

		$this->assertTrue($this->template_vars['S_EMAIL_UNSUBSCRIBE_DONE']);
		$this->assertFalse($this->get_watch_status('phpbb_topics_watch', 'topic_id', 2));
		$this->assertSame(0, $this->get_watch_status('phpbb_topics_watch', 'topic_id', 3));
		$this->assertSame(1, $this->get_watch_status('phpbb_forums_watch', 'forum_id', 2));
		$this->assertSame(1, $this->get_notify(2, 'notification.type.post'));
	}

	public function test_unsubscribe_forum(): void
	{
		$this->handle(2, 'notification.type.forum', 'forum', 1, ['unsubscribe' => 'Unsubscribe', 'scope' => 'item']);

		$this->assertFalse($this->get_watch_status('phpbb_forums_watch', 'forum_id', 2));
		$this->assertSame(1, $this->get_watch_status('phpbb_topics_watch', 'topic_id', 2));
	}

	public function test_unsubscribe_type(): void
	{
		$this->handle(2, post: ['unsubscribe' => 'Unsubscribe', 'scope' => 'type']);

		$this->assertTrue($this->template_vars['S_EMAIL_UNSUBSCRIBE_DONE']);
		$this->assertSame(0, $this->get_notify(2, 'notification.type.post'));
		$this->assertSame(1, $this->get_notify(2, 'moderation_queue'));
		$this->assertSame(1, $this->get_watch_status('phpbb_topics_watch', 'topic_id', 2));
	}

	public function test_unsubscribe_disabled_type_stays_disabled(): void
	{
		$this->db->sql_query("UPDATE phpbb_user_notifications SET notify = 0 WHERE user_id = 2 AND item_type = 'notification.type.post'");

		$this->handle(2, post: ['unsubscribe' => 'Unsubscribe', 'scope' => 'type']);

		$this->assertTrue($this->template_vars['S_EMAIL_UNSUBSCRIBE_DONE']);
		$this->assertSame(0, $this->get_notify(2, 'notification.type.post'));
	}

	public function test_unsubscribe_all(): void
	{
		$this->handle(2, post: ['unsubscribe' => 'Unsubscribe', 'scope' => 'all']);

		$this->assertSame(0, $this->get_notify(2, 'notification.type.post'));
		$this->assertSame(0, $this->get_notify(2, 'moderation_queue'));
		$this->assertSame(0, $this->get_massemail(2));

		// Board notifications, watched items and other users are not affected
		$this->assertSame(1, $this->get_notify(2, 'notification.type.post', 'notification.method.board'));
		$this->assertSame(1, $this->get_watch_status('phpbb_topics_watch', 'topic_id', 2));
		$this->assertSame(1, $this->get_watch_status('phpbb_forums_watch', 'forum_id', 2));
		$this->assertSame(1, $this->get_notify(3, 'notification.type.post'));
		$this->assertSame(1, $this->get_massemail(3));
	}

	public function test_unsubscribe_all_without_anything_enabled(): void
	{
		$this->handle(4, post: ['unsubscribe' => 'Unsubscribe', 'scope' => 'all']);

		$this->assertTrue($this->template_vars['S_EMAIL_UNSUBSCRIBE_DONE']);
		$this->assertSame(0, $this->get_massemail(4));
	}

	public function test_manager_ignores_other_methods_and_users(): void
	{
		$this->manager->disable_subscriptions(2, self::EMAIL);

		$this->assertSame(0, $this->get_notify(2, 'notification.type.post'));
		$this->assertSame(0, $this->get_notify(2, 'moderation_queue'));
		$this->assertSame(1, $this->get_notify(2, 'notification.type.post', 'notification.method.board'));
		$this->assertSame(1, $this->get_notify(3, 'notification.type.post'));
	}

	public function test_manager_subscription_option(): void
	{
		$this->assertSame('moderation_queue', $this->manager->get_subscription_option('notification.type.approve_post')['id']);
		$this->assertSame('notification.type.post', $this->manager->get_subscription_option('notification.type.post')['id']);
		$this->assertNull($this->manager->get_subscription_option('notification.type.removed'));
	}
}
