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
use phpbb\notification\method\email;

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

	protected function get_method(string $secret = 'secret'): email
	{
		return new email(
			$this->createMock(\phpbb\user_loader::class),
			$this->createMock(\phpbb\user::class),
			new config(['email_unsubscribe_secret' => $secret]),
			$this->createMock(\phpbb\db\driver\driver_interface::class),
			'',
			'php',
			'phpbb_notification_emails',
			$this->createMock(\phpbb\di\service_collection::class)
		);
	}

	public function test_valid_link(): void
	{
		$method = $this->get_method();
		$params = $method->get_unsubscribe_params(self::USER_ROW, 'notification.type.post', 'topic', 5);

		$this->assertSame(2, $params['u']);
		$this->assertSame('notification.type.post', $params['t']);
		$this->assertSame('topic', $params['m']);
		$this->assertSame(5, $params['i']);
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
			'user'		=> ['u', 3],
			'type'		=> ['t', 'notification.type.pm'],
			'mode'		=> ['m', 'forum'],
			'item'		=> ['i', 6],
			'date'		=> ['d', 1],
			'signature'	=> ['k', str_repeat('0', 64)],
			'empty'		=> ['k', ''],
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
			'u' => 2, 't' => 'notification.type.post', 'm' => '', 'i' => 0, 'd' => 1, 'k' => '',
		]));
	}

	public function test_invalid_email_encoding_does_not_throw(): void
	{
		$user_row = ['user_id' => 2, 'user_email' => "invalid\xFF@example.com"];
		$method = $this->get_method();

		$params = $method->get_unsubscribe_params($user_row, 'notification.type.post');

		$this->assertTrue($method->check_unsubscribe_params($user_row, $params));
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
