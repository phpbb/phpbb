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

use phpbb\exception\http_exception;
use phpbb\request\request_interface;
use phpbb\ucp\controller\webpush;

class test_ucp_controller_webpush_test extends phpbb_test_case
{
	protected $config;
	protected $controller;
	protected $controller_helper;
	protected $db;
	protected $form_helper;
	protected $language;
	protected $notification;
	protected $notification_manager;
	protected $path_helper;
	protected $request;
	protected $template;
	protected $user;
	protected $user_loader;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = $this->createMock(\phpbb\config\config::class);
		$this->controller_helper = $this->createMock(\phpbb\controller\helper::class);
		$this->db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$this->form_helper = $this->createMock(\phpbb\form\form_helper::class);
		$this->language = $this->createMock(\phpbb\language\language::class);
		$this->notification = $this->createMock(\phpbb\notification\type\type_interface::class);
		$this->notification_manager = $this->createMock(\phpbb\notification\manager::class);
		$this->path_helper = $this->createMock(\phpbb\path_helper::class);
		$this->request = $this->createMock(\phpbb\request\request_interface::class);
		$this->template = $this->createMock(\Twig\Environment::class);
		$this->user = $this->createMock(\phpbb\user::class);
		$this->user_loader = $this->createMock(\phpbb\user_loader::class);

		$this->controller = new webpush(
			$this->config,
			$this->controller_helper,
			$this->db,
			$this->form_helper,
			$this->language,
			$this->notification_manager,
			$this->path_helper,
			$this->request,
			$this->user_loader,
			$this->user,
			$this->template,
			'webpush_table',
			'subscription_table'
		);
	}

	public function data_notification_exceptions(): array
	{
		return [
			'not_ajax' => [
				false,
				false,
				USER_NORMAL,
				2,
				[],
				'NO_AUTH_OPERATION',
			],
			'is_bot' => [
				true,
				true,
				USER_NORMAL,
				2,
				[],
				'NO_AUTH_OPERATION',
			],
			'inactive_user' => [
				true,
				false,
				USER_INACTIVE,
				2,
				[],
				'NO_AUTH_OPERATION',
			],
			'ignore_user' => [
				true,
				false,
				USER_IGNORE,
				2,
				[],
				'NO_AUTH_OPERATION',
			],
			'no_notification' => [
				true,
				false,
				USER_NORMAL,
				2,
				[],
				'AJAX_ERROR_TEXT',
			],
			'no_notification_anonymous' => [
				true,
				false,
				USER_NORMAL,
				ANONYMOUS,
				[
					['token', '', false, request_interface::REQUEST, 'foobar'],
				],
				'AJAX_ERROR_TEXT',
			],
			'no_notification_anonymous_no_token' => [
				true,
				false,
				USER_NORMAL,
				ANONYMOUS,
				[],
				'NO_AUTH_OPERATION',
			],
		];
	}

	/**
	 * @dataProvider data_notification_exceptions
	 */
	public function test_notification_no_data($is_ajax, $is_bot, $user_type, $user_id, $request_data, $expected_message)
	{
		$this->request->method('is_ajax')->willReturn($is_ajax);
		$this->request->expects($this->any())
			->method('variable')
			->will($this->returnValueMap($request_data));
		$this->user->data['is_bot'] = $is_bot;
		$this->user->data['user_type'] = $user_type;
		$this->user->method('id')->willReturn($user_id);

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage($expected_message);

		$this->controller->notification();
	}
}
