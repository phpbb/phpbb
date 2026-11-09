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
use phpbb\messenger\method\email as email_messenger;
use phpbb\notification\method\email;
use phpbb\notification\type\post;

class phpbb_notification_email_unsubscribe_link_test extends phpbb_test_case
{
	protected const USER_ROW = [
		'user_id'				=> 2,
		'username'				=> 'user',
		'user_email'			=> 'user@example.com',
		'user_lang'				=> 'en',
		'user_type'				=> USER_NORMAL,
		'user_inactive_reason'	=> 0,
	];

	protected function get_method(string $secret = 'secret', int $expire_days = 365): email
	{
		$routes = new \Symfony\Component\Routing\RouteCollection();
		$routes->add('phpbb_notifications_unsubscribe', new \Symfony\Component\Routing\Route('/notifications/unsubscribe'));

		$router = $this->createMock(\phpbb\routing\router::class);
		$router->method('get_routes')->willReturn($routes);

		return new email(
			$this->createMock(\phpbb\user_loader::class),
			$this->createMock(\phpbb\user::class),
			new config(['email_unsubscribe_secret' => $secret, 'email_unsubscribe_expire_days' => $expire_days]),
			$this->createMock(\phpbb\db\driver\driver_interface::class),
			'',
			'php',
			'phpbb_notification_emails',
			$this->createMock(\phpbb\di\service_collection::class),
			$router
		);
	}

	public static function expiry_data(): array
	{
		return [
			'new link'				=> [365, 0, false],
			'one day before limit'	=> [365, 364, false],
			'past limit'			=> [365, 366, true],
			'short limit'			=> [7, 8, true],
			'never expires'			=> [0, 10000, false],
		];
	}

	/**
	 * @dataProvider expiry_data
	 */
	public function test_link_expiry(int $expire_days, int $age_days, bool $expired): void
	{
		$method = $this->get_method('secret', $expire_days);
		$params = $method->get_unsubscribe_params(self::USER_ROW, 'notification.type.post');
		$params['time'] -= $age_days * 86400;

		$this->assertSame($expired, $method->is_unsubscribe_link_expired($params));
	}

	public function test_valid_link(): void
	{
		$method = $this->get_method();
		$params = $method->get_unsubscribe_params(self::USER_ROW, 'notification.type.post', 'topic', 5);

		$this->assertSame(2, $params['user_id']);
		$this->assertSame('notification.type.post', $params['type']);
		$this->assertSame('topic', $params['watch_mode']);
		$this->assertSame(5, $params['watch_id']);
		$this->assertTrue($method->check_unsubscribe_params(self::USER_ROW, $params));
	}

	public function test_link_params_survive_string_conversion(): void
	{
		$method = $this->get_method();
		$params = array_map('strval', $method->get_unsubscribe_params(self::USER_ROW, 'notification.type.pm'));

		$this->assertTrue($method->check_unsubscribe_params(self::USER_ROW, $params));
	}

	public static function tampered_params_data(): array
	{
		return [
			'user'		=> ['user_id', 3],
			'type'		=> ['type', 'notification.type.pm'],
			'mode'		=> ['watch_mode', 'forum'],
			'item'		=> ['watch_id', 6],
			'date'		=> ['time', 1],
			'signature'	=> ['hash', str_repeat('0', 64)],
			'empty'		=> ['hash', ''],
		];
	}

	/**
	 * @dataProvider tampered_params_data
	 */
	public function test_tampered_link(string $key, $value): void
	{
		$method = $this->get_method();
		$params = $method->get_unsubscribe_params(self::USER_ROW, 'notification.type.post', 'topic', 5);
		$params[$key] = $value;

		$this->assertFalse($method->check_unsubscribe_params(self::USER_ROW, $params));
	}

	public function test_link_for_other_user_row(): void
	{
		$method = $this->get_method();
		$params = $method->get_unsubscribe_params(self::USER_ROW, 'notification.type.post');

		$this->assertFalse($method->check_unsubscribe_params(['user_id' => 3, 'user_email' => 'user@example.com'], $params));
	}

	public function test_changed_email_invalidates_link(): void
	{
		$method = $this->get_method();
		$params = $method->get_unsubscribe_params(self::USER_ROW, 'notification.type.post');

		$this->assertFalse($method->check_unsubscribe_params(['user_id' => 2, 'user_email' => 'new@example.com'], $params));
	}

	public function test_rotated_secret_invalidates_link(): void
	{
		$params = $this->get_method('old')->get_unsubscribe_params(self::USER_ROW, 'notification.type.post');

		$this->assertFalse($this->get_method('new')->check_unsubscribe_params(self::USER_ROW, $params));
	}

	public static function unsignable_data(): array
	{
		return [
			'no secret'	=> ['', self::USER_ROW],
			'no email'	=> ['secret', ['user_id' => 2, 'user_email' => '']],
		];
	}

	/**
	 * @dataProvider unsignable_data
	 */
	public function test_nothing_is_signed_without_secret_or_email(string $secret, array $user_row): void
	{
		$method = $this->get_method($secret);

		$this->assertNull($method->get_unsubscribe_params($user_row, 'notification.type.post'));
		$this->assertNull($method->get_unsubscribe_url($user_row, 'notification.type.post'));
		$this->assertFalse($method->check_unsubscribe_params($user_row, [
			'user_id' => 2, 'type' => 'notification.type.post', 'watch_mode' => '', 'watch_id' => 0, 'time' => 1, 'hash' => '',
		]));
	}

	public function test_invalid_email_encoding_does_not_throw(): void
	{
		$user_row = ['user_id' => 2, 'user_email' => "invalid\xFF@example.com"];
		$method = $this->get_method();

		$params = $method->get_unsubscribe_params($user_row, 'notification.type.post');

		$this->assertTrue($method->check_unsubscribe_params($user_row, $params));
	}

	/**
	 * Run the protected message hook the way messenger_base does while sending
	 */
	protected function get_message_vars(string $secret, array $template_vars, array|null $watch_item, email_messenger $messenger, bool $allows_unsubscribe = true): array
	{
		global $config, $request, $symfony_request, $user;

		$config = new config([
			'force_server_vars'	=> 1,
			'server_protocol'	=> 'https://',
			'server_name'		=> 'example.com',
			'server_port'		=> 443,
			'script_path'		=> '/',
		]);
		$request = new phpbb_mock_request();
		$user = new phpbb_mock_user();
		$user->page['root_script_path'] = '/';
		$symfony_request = $this->createMock(\phpbb\symfony_request::class);

		$notification = $this->createMock(post::class);
		$notification->method('get_type')->willReturn('notification.type.post');
		$notification->method('get_email_template_variables')->willReturn($template_vars);
		$notification->method('get_unsubscribe_watch_item')->willReturn($watch_item);
		$notification->method('allows_email_unsubscribe')->willReturn($allows_unsubscribe);

		$method = $this->get_method($secret);

		return (new \ReflectionMethod($method, 'get_message_vars'))->invoke($method, $messenger, $notification, self::USER_ROW);
	}

	public function test_email_gets_unsubscribe_link_and_header(): void
	{
		$header_url = '';
		$messenger = $this->createMock(email_messenger::class);
		$messenger->expects($this->once())->method('set_list_unsubscribe')->willReturnCallback(function (string $url) use (&$header_url) {
			$header_url = $url;
		});

		$vars = $this->get_message_vars('secret', [
			'U_STOP_WATCHING_TOPIC'	=> 'https://example.com/viewtopic.php?uid=2&t=1&unwatch=topic',
			'U_TOPIC'				=> 'https://example.com/viewtopic.php?t=1',
		], ['mode' => 'topic', 'id' => 1], $messenger);

		$this->assertStringContainsString('/index.php/notifications/unsubscribe?user_id=2&type=notification.type.post&watch_mode=topic&watch_id=1&', $vars['U_UNSUBSCRIBE']);
		$this->assertSame($vars['U_UNSUBSCRIBE'], $header_url);
		$this->assertSame($vars['U_UNSUBSCRIBE'], $vars['U_STOP_WATCHING_TOPIC']);
		$this->assertSame('https://example.com/viewtopic.php?t=1', $vars['U_TOPIC']);
		$this->assertArrayNotHasKey('U_STOP_WATCHING_FORUM', $vars);
	}

	public function test_type_without_watched_item(): void
	{
		$vars = $this->get_message_vars('secret', [], null, $this->createMock(email_messenger::class));

		$this->assertStringContainsString('&watch_mode=&watch_id=0&', $vars['U_UNSUBSCRIBE']);
	}

	public function test_email_falls_back_to_settings_if_link_can_not_be_signed(): void
	{
		$old_url = 'https://example.com/viewtopic.php?uid=2&t=1&unwatch=topic';

		$messenger = $this->createMock(email_messenger::class);
		$messenger->expects($this->never())->method('set_list_unsubscribe');

		$vars = $this->get_message_vars('', ['U_STOP_WATCHING_TOPIC' => $old_url], ['mode' => 'topic', 'id' => 1], $messenger);

		$this->assertSame($vars['U_NOTIFICATION_SETTINGS'], $vars['U_UNSUBSCRIBE']);
		$this->assertNotEmpty($vars['U_UNSUBSCRIBE']);
		$this->assertSame($old_url, $vars['U_STOP_WATCHING_TOPIC']);
	}

	public function test_email_without_unsubscribe_link_if_type_does_not_allow_it(): void
	{
		$messenger = $this->createMock(email_messenger::class);
		$messenger->expects($this->never())->method('set_list_unsubscribe');

		$vars = $this->get_message_vars('secret', [], null, $messenger, false);

		$this->assertSame($vars['U_NOTIFICATION_SETTINGS'], $vars['U_UNSUBSCRIBE']);
		$this->assertStringNotContainsString('unsubscribe', $vars['U_UNSUBSCRIBE']);
	}

	public static function allows_email_unsubscribe_data(): array
	{
		return [
			'post'					=> [\phpbb\notification\type\post::class, true],
			'pm'					=> [\phpbb\notification\type\pm::class, true],
			'admin_activate_user'	=> [\phpbb\notification\type\admin_activate_user::class, true],
			'update_maintenance'	=> [\phpbb\notification\type\update_maintenance::class, false],
		];
	}

	/**
	 * @dataProvider allows_email_unsubscribe_data
	 */
	public function test_allows_email_unsubscribe(string $class, bool $expected): void
	{
		$type = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

		$this->assertSame($expected, $type->allows_email_unsubscribe());
	}

	public static function watch_item_data(): array
	{
		return [
			'post'				=> [\phpbb\notification\type\post::class, ['mode' => 'topic', 'id' => 7]],
			'topic'				=> [\phpbb\notification\type\topic::class, ['mode' => 'forum', 'id' => 7]],
			'forum'				=> [\phpbb\notification\type\forum::class, ['mode' => 'forum', 'id' => 9]],
			'quote'				=> [\phpbb\notification\type\quote::class, null],
			'bookmark'			=> [\phpbb\notification\type\bookmark::class, null],
			'mention'			=> [\phpbb\notification\type\mention::class, null],
			'approve_post'		=> [\phpbb\notification\type\approve_post::class, null],
			'approve_topic'		=> [\phpbb\notification\type\approve_topic::class, null],
			'post_in_queue'		=> [\phpbb\notification\type\post_in_queue::class, null],
			'topic_in_queue'	=> [\phpbb\notification\type\topic_in_queue::class, null],
			'report_post'		=> [\phpbb\notification\type\report_post::class, null],
			'pm'				=> [\phpbb\notification\type\pm::class, null],
		];
	}

	/**
	 * @dataProvider watch_item_data
	 */
	public function test_watch_item_of_type(string $class, array|null $expected): void
	{
		$type = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
		$type->set_initial_data([
			'item_parent_id'	=> 7,
			'notification_data'	=> serialize(['forum_id' => 9]),
		]);

		$this->assertSame($expected, $type->get_unsubscribe_watch_item());
	}
}
