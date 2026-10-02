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
use phpbb\forum\controller\mark_all_read;
use Symfony\Component\HttpFoundation\JsonResponse;

class phpbb_forum_controller_mark_all_read_test extends phpbb_test_case
{
	/** @var config */
	protected $config;

	/** @var \phpbb\controller\helper|\PHPUnit\Framework\MockObject\MockObject */
	protected $controller_helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request|\PHPUnit\Framework\MockObject\MockObject */
	protected $request;

	/** @var \phpbb\user|\PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	/** @var bool */
	protected $markread_called;

	/** @var array */
	protected $markread_args;

	protected function setUp(): void
	{
		global $config, $user, $request, $template, $phpbb_dispatcher, $phpbb_container, $phpbb_path_helper, $phpbb_root_path, $phpEx;

		parent::setUp();

		$this->config = new config([
			'load_anon_lastread' => false,
			'force_server_vars' => true,
			'server_protocol' => 'http://',
			'server_name' => 'localhost',
			'server_port' => 80,
			'script_path' => '/phpBB',
			'cookie_secure' => false,
		]);
		$config = $this->config;

		$this->controller_helper = $this->createMock(\phpbb\controller\helper::class);
		$this->controller_helper->method('route')
			->willReturnCallback(function ($route, $params = [], $is_amp = true) {
				return 'http://localhost/phpBB/' . $route;
			});

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new \phpbb\language\language($lang_loader);

		$this->user = $this->createMock(\phpbb\user::class);
		$this->user->data = [
			'user_id' => 2,
			'is_registered' => true,
			'user_form_salt' => 'test_salt_123',
		];
		$this->user->host = 'localhost';
		$this->user->method('is_setup')->willReturn(true);
		$user = $this->user;

		$this->request = $this->createMock(\phpbb\request\request::class);
		$request = $this->request;

		$template = $this->createMock(\phpbb\template\template::class);

		$phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(new phpbb_mock_request()),
			new phpbb_mock_request(),
			$phpbb_root_path,
			$phpEx
		);

		$this->markread_called = false;
		$this->markread_args = [];
		$phpbb_dispatcher = $this->createMock(\phpbb\event\dispatcher::class);
		$phpbb_dispatcher->method('trigger_event')
			->willReturnCallback(function ($eventName, $data = []) {
				if ($eventName === 'core.markread_before')
				{
					$this->markread_called = true;
					$this->markread_args = $data;
					$data['should_markread'] = false;
				}
				return $data;
			});

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('notification_manager', new phpbb_mock_notification_manager());
	}

	public function test_handle_invalid_hash(): void
	{
		$this->request->method('variable')->willReturnMap([
			['hash', '', 'invalid_hash_value'],
			['mark_time', 0, 0],
		]);
		$this->request->method('is_ajax')->willReturn(false);

		$this->setExpectedTriggerError(E_USER_NOTICE);

		$controller = new mark_all_read($this->config, $this->controller_helper, $this->language, $this->request, $this->user);
		$controller->handle();

		$this->assertFalse($this->markread_called);
	}

	public function test_handle_valid_hash_ajax_registered(): void
	{
		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['is_registered'] = true;

		$controller = new mark_all_read($this->config, $this->controller_helper, $this->language, $this->request, $this->user);
		$response = $controller->handle();

		$this->assertTrue($this->markread_called);
		$this->assertEquals('all', $this->markread_args['mode']);
		$this->assertFalse($this->markread_args['forum_id']);
		$this->assertEquals(1600000000, $this->markread_args['post_time']);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$data = json_decode($response->getContent(), true);
		$this->assertArrayHasKey('NO_UNREAD_POSTS', $data);
		$this->assertArrayHasKey('UNREAD_POSTS', $data);
		$this->assertArrayHasKey('U_MARK_FORUMS', $data);
		$this->assertNotEmpty($data['U_MARK_FORUMS']);
		$this->assertArrayHasKey('MESSAGE_TITLE', $data);
		$this->assertArrayHasKey('MESSAGE_TEXT', $data);
	}

	public function test_handle_valid_hash_ajax_anonymous(): void
	{
		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['is_registered'] = false;
		$this->config->set('load_anon_lastread', false);

		$controller = new mark_all_read($this->config, $this->controller_helper, $this->language, $this->request, $this->user);
		$response = $controller->handle();

		$this->assertInstanceOf(JsonResponse::class, $response);
		$data = json_decode($response->getContent(), true);
		$this->assertSame('', $data['U_MARK_FORUMS']);
	}

	public function test_handle_valid_hash_ajax_anonymous_with_load_anon(): void
	{
		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['is_registered'] = false;
		$this->config->set('load_anon_lastread', true);

		$controller = new mark_all_read($this->config, $this->controller_helper, $this->language, $this->request, $this->user);
		$response = $controller->handle();

		$this->assertInstanceOf(JsonResponse::class, $response);
		$data = json_decode($response->getContent(), true);
		$this->assertNotEmpty($data['U_MARK_FORUMS']);
	}

	public function test_handle_valid_hash_non_ajax(): void
	{
		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(false);

		$this->setExpectedTriggerError(E_USER_NOTICE);

		$controller = new mark_all_read($this->config, $this->controller_helper, $this->language, $this->request, $this->user);
		$controller->handle();

		$this->assertTrue($this->markread_called);
		$this->assertEquals('all', $this->markread_args['mode']);
		$this->assertFalse($this->markread_args['forum_id']);
		$this->assertEquals(1600000000, $this->markread_args['post_time']);
	}
}
