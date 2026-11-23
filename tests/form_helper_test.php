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

class phpbb_form_helper_test extends phpbb_test_case
{
	protected $config;
	protected $request;
	protected $user;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config([
			'form_token_lifetime' => 60,
			'form_token_sid_guests' => true,
		]);
		$this->request = $this->createMock(\phpbb\request\request_interface::class);
		$this->user = $this->createMock(\phpbb\user::class);
		$this->user->data = [
			'user_id' => 2,
			'user_form_salt' => 'salt',
		];
		$this->user->session_id = 'session';
	}

	public static function check_data(): array
	{
		return [
			'same-second token' => [2, 60, null, 0, true, true, true],
			'valid user token' => [2, 60, null, 10, true, true, true],
			'valid guest token with sid' => [ANONYMOUS, 60, null, 10, true, true, true],
			'unlimited lifetime' => [2, -1, null, 3600, true, true, true],
			'future-dated token' => [2, 60, null, -10, true, true, false],
			'minimum lifetime enforced' => [2, 1, null, 31, true, true, false],
			'explicit lifetime' => [2, 60, 5, 10, true, true, false],
			'missing creation time' => [2, 60, null, 10, false, true, false],
			'missing token' => [2, 60, null, 10, true, false, false],
			'invalid token' => [2, 60, null, 10, true, true, false, 'invalid'],
		];
	}

	/**
	 * @dataProvider check_data
	 */
	public function test_check_form_tokens($user_id, $lifetime, $timespan, $age, $has_time, $has_token, $expected, $token_override = null): void
	{
		$this->config['form_token_lifetime'] = $lifetime;
		$this->user->data['user_id'] = $user_id;
		$creation_time = time() - $age;
		$token_sid = $user_id === ANONYMOUS ? $this->user->session_id : '';
		$token = $token_override ?? sha1($creation_time . 'salt' . 'test-form' . $token_sid);

		$this->request->method('is_set_post')->willReturnMap([
			['creation_time', $has_time],
			['form_token', $has_token],
		]);
		$this->request->method('variable')->willReturnMap([
			['creation_time', 0, false, \phpbb\request\request_interface::REQUEST, $creation_time],
			['form_token', '', false, \phpbb\request\request_interface::REQUEST, $token],
		]);

		$helper = new \phpbb\form\form_helper($this->config, $this->request, $this->user);
		self::assertSame($expected, $helper->check_form_tokens('test-form', $timespan));
	}
}
