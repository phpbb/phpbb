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

use phpbb\webpush\subscription\validator;

class phpbb_webpush_subscription_validator_test extends phpbb_test_case
{
	private const VAPID_KEYS = [
		'publicKey'		=> 'BIcGkq1Ncj3a2-J0UW-1A0NETLjvxZzNLiYBiPVMKNjgwmwPi5jyK87VfS4FZn9n7S9pLMQzjV3LmFuOnRSOvmI',
		'privateKey'	=> 'SrlbBEVgibWmKHYbDPu4Y2XvDWPjeGcc9fC16jq01xU',
	];

	public function test_valid_subscription_keys(): void
	{
		$this->assertTrue(validator::is_valid(self::VAPID_KEYS['publicKey'], 'AAAAAAAAAAAAAAAAAAAAAA'));
		$this->assertFalse(validator::is_valid('invalid', 'AAAAAAAAAAAAAAAAAAAAAA'));
		$this->assertFalse(validator::is_valid(self::VAPID_KEYS['publicKey'], 'invalid'));
	}

	public function test_valid_vapid_keys(): void
	{
		$this->assertTrue(validator::is_valid_vapid_keys(self::VAPID_KEYS['publicKey'], self::VAPID_KEYS['privateKey']));
		$this->assertFalse(validator::is_valid_vapid_keys(self::VAPID_KEYS['publicKey'], 'invalid'));
		$this->assertFalse(validator::is_valid_vapid_keys('invalid', self::VAPID_KEYS['privateKey']));
	}

	public static function endpoint_data(): array
	{
		return [
			'valid' => ['https://fcm.googleapis.com/fcm/send/test', true],
			'explicit HTTPS port' => ['https://fcm.googleapis.com:443/fcm/send/test', true],
			'HTTP' => ['http://fcm.googleapis.com/fcm/send/test', false],
			'credentials' => ['https://user:pass@fcm.googleapis.com/fcm/send/test', false],
			'non-HTTPS port' => ['https://fcm.googleapis.com:8443/fcm/send/test', false],
			'missing host' => ['https:///fcm/send/test', false],
			'invalid hostname' => ['https://bad_host.example/fcm/send/test', false],
		];
	}

	/**
	 * @dataProvider endpoint_data
	 */
	public function test_endpoint_url(string $endpoint, bool $expected): void
	{
		$this->assertSame($expected, validator::is_valid_endpoint_url($endpoint));
	}
}
