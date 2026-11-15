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

namespace phpbb\notification\method
{
	function phpbb_get_banned_user_ids($user_ids)
	{
		return $GLOBALS['phpbb_webpush_test_banned_users'] ?? \phpbb_get_banned_user_ids($user_ids);
	}
}

namespace
{
	class webpush_branches_test extends phpbb_test_case
	{
		private const VAPID_KEYS = [
			'publicKey' => 'BIcGkq1Ncj3a2-J0UW-1A0NETLjvxZzNLiYBiPVMKNjgwmwPi5jyK87VfS4FZn9n7S9pLMQzjV3LmFuOnRSOvmI',
			'privateKey' => 'SrlbBEVgibWmKHYbDPu4Y2XvDWPjeGcc9fC16jq01xU',
		];

		protected function tearDown(): void
		{
			unset($GLOBALS['phpbb_webpush_test_banned_users']);
			parent::tearDown();
		}

		public function test_oversized_payload_logs_failure_without_removing_subscription(): void
		{
			$GLOBALS['phpbb_webpush_test_banned_users'] = [];
			$log = $this->createMock(\phpbb\log\log_interface::class);
			$log->expects(self::once())->method('add')->with(
				'critical',
				ANONYMOUS,
				'',
				'LOG_WEBPUSH_MESSAGE_FAIL'
			);

			$user_loader = $this->createMock(\phpbb\user_loader::class);
			$user_loader->method('get_user')->with(2)->willReturn([
				'user_id' => 2,
				'user_type' => USER_NORMAL,
				'user_inactive_reason' => 0,
				'user_form_salt' => 'salt',
				'user_ip' => '127.0.0.1',
				'username' => 'Tester',
			]);

			$method = $this->create_method($log, $user_loader);
			$method->subscriptions = [
				2 => [[
					'subscription_id' => 9,
					'endpoint' => 'https://updates.push.services.mozilla.com/test',
					'p256dh' => self::VAPID_KEYS['publicKey'],
					'auth' => 'AAAAAAAAAAAAAAAAAAAAAA',
				]],
			];
			$method->add_to_queue($this->create_notification());
			$method->set_push_token(1, 10, 2, 'token');

			$method->send_queued_notifications();

			self::assertSame([], $method->removed_subscriptions);
		}

		public function test_delivery_failures_are_logged_once_per_category(): void
		{
			$entries = [];
			$log = $this->createMock(\phpbb\log\log_interface::class);
			$log->expects(self::exactly(3))->method('add')->willReturnCallback(
				static function (...$arguments) use (&$entries)
				{
					$entries[] = $arguments;
					return true;
				}
			);
			$method = $this->create_method($log, $this->createMock(\phpbb\user_loader::class));
			$method->log_delivery_failures_for_test([
				$this->create_failed_report(401, 'Unauthorized'),
				$this->create_failed_report(401, 'Unauthorized again'),
				$this->create_failed_report(401, 'Unauthorized once more'),
				$this->create_failed_report(403, 'Forbidden'),
				$this->create_failed_report(403, 'Forbidden again'),
				$this->create_failed_report(null, 'DNS failure one'),
				$this->create_failed_report(null, 'DNS failure two'),
			]);
			self::assertSame([
				['admin', ANONYMOUS, '', 'LOG_WEBPUSH_MESSAGE_FAIL', false, ['3 × Unauthorized']],
				['admin', ANONYMOUS, '', 'LOG_WEBPUSH_MESSAGE_FAIL', false, ['2 × Forbidden']],
				['admin', ANONYMOUS, '', 'LOG_WEBPUSH_MESSAGE_FAIL', false, ['2 × DNS failure one']],
			], $entries);
		}

		public function test_single_delivery_failure_keeps_sanitized_reason(): void
		{
			$log = $this->createMock(\phpbb\log\log_interface::class);
			$log->expects(self::once())->method('add')->with(
				'admin', ANONYMOUS, '', 'LOG_WEBPUSH_MESSAGE_FAIL', false, ['Too Many Requests &lt;retry&gt;']
			);
			$this->create_method($log, $this->createMock(\phpbb\user_loader::class))->log_delivery_failures_for_test([
				$this->create_failed_report(429, 'Too Many Requests <retry>'),
			]);
		}

		private function create_failed_report(?int $status_code, string $reason): \Minishlink\WebPush\MessageSentReport
		{
			$request = new \GuzzleHttp\Psr7\Request('POST', 'https://push.example.test/endpoint');
			$response = $status_code === null ? null : new \GuzzleHttp\Psr7\Response($status_code);

			return new \Minishlink\WebPush\MessageSentReport($request, $response, false, $reason);
		}

		private function create_method($log, $user_loader): phpbb_testable_webpush_branches
		{
			global $config, $phpbb_root_path, $phpEx, $user;

			$config = new \phpbb\config\config([
				'webpush_vapid_public' => self::VAPID_KEYS['publicKey'],
				'webpush_vapid_private' => self::VAPID_KEYS['privateKey'],
				'assets_version' => str_repeat('x', 5000),
				'force_server_vars' => true,
				'server_protocol' => 'https://',
				'server_name' => 'example.com',
				'server_port' => 443,
				'script_path' => '',
				'cookie_secure' => true,
			]);

			$user = $this->createMock(\phpbb\user::class);

			return new phpbb_testable_webpush_branches(
				$config,
				$this->createMock(\phpbb\db\driver\driver_interface::class),
				$log,
				$user_loader,
				$user,
				$phpbb_root_path,
				$phpEx,
				'phpbb_notification_push',
				'phpbb_push_subscriptions'
			);
		}

		private function create_notification()
		{
			require_once __DIR__ . '/ext/test/notification/type/test.php';
			$reflection = new ReflectionClass(\phpbb\notification\type\test::class);
			$notification = $reflection->newInstanceWithoutConstructor();
			$notification->set_initial_data([
				'user_id' => 2,
				'notification_type_id' => 1,
				'item_id' => 10,
			]);

			return $notification;
		}
	}

	class phpbb_testable_webpush_branches extends \phpbb\notification\method\webpush
	{
		public array $subscriptions = [];
		public array $removed_subscriptions = [];

		public function send_queued_notifications(): void
		{
			$this->notify_using_webpush();
		}

		public function log_delivery_failures_for_test(array $reports): void
		{
			$failures = [];
			foreach ($reports as $report)
			{
				$this->add_delivery_failure($failures, $report);
			}
			$this->log_delivery_failures($failures);
		}

		public function set_push_token($type_id, $item_id, $user_id, $token): void
		{
			$property = new ReflectionProperty(\phpbb\notification\method\webpush::class, 'push_token_map');
			$property->setValue($this, [$type_id => [$item_id => [$user_id => $token]]]);
		}

		protected function get_user_subscription_map(array $notify_users): array
		{
			return $this->subscriptions;
		}

		public function remove_subscriptions(array $subscription_ids): void
		{
			$this->removed_subscriptions = $subscription_ids;
		}
	}
}
