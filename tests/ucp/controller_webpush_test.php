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

	public function data_notification_no_data(): array
	{
		return [
			'logged_in_user' => [
				USER_NORMAL,
				2,
				[],
			],
			'anonymous_user' => [
				USER_NORMAL,
				ANONYMOUS,
				[
					['token', '', false, request_interface::REQUEST, 'foobar'],
				],
			],
		];
	}

	/**
	 * @dataProvider data_notification_no_data
	 */
	public function test_notification_no_data($user_type, $user_id, $request_data)
	{
		$this->request->method('is_ajax')->willReturn(true);
		$this->request->expects($this->any())
			->method('variable')
			->will($this->returnValueMap($request_data));
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = $user_type;
		$this->user->method('id')->willReturn($user_id);

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage('AJAX_ERROR_TEXT');

		$this->controller->notification();
	}
}
