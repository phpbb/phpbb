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
use phpbb\forum\controller\mark_subforums_read;
use phpbb\forum\helper as forum_helper;
use Symfony\Component\HttpFoundation\JsonResponse;

class phpbb_forum_controller_mark_subforums_read_test extends phpbb_test_case
{
	/** @var \phpbb\auth\auth|\PHPUnit\Framework\MockObject\MockObject */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var \phpbb\controller\helper|\PHPUnit\Framework\MockObject\MockObject */
	protected $controller_helper;

	/** @var forum_helper|\PHPUnit\Framework\MockObject\MockObject */
	protected $forum_helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request|\PHPUnit\Framework\MockObject\MockObject */
	protected $request;

	/** @var \phpbb\user|\PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var bool */
	protected $markread_called;

	/** @var array */
	protected $markread_args;

	protected function setUp(): void
	{
		global $config, $user, $request, $template, $phpbb_dispatcher, $phpbb_container, $phpbb_path_helper, $phpbb_root_path, $phpEx;

		parent::setUp();

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$this->auth = $this->createMock(\phpbb\auth\auth::class);

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

		$this->forum_helper = $this->createMock(forum_helper::class);

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new \phpbb\language\language($lang_loader);

		$this->user = $this->createMock(\phpbb\user::class);
		$this->user->data = [
			'user_id' => 2,
			'is_registered' => true,
			'user_form_salt' => 'test_salt_123',
		];
		$this->user->host = 'localhost';
		$this->user->page = ['page_dir' => '', 'page_name' => ''];
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

	public function test_handle_forum_not_found(): void
	{
		$this->forum_helper->method('get_forum_data')->with(999)->willReturn(false);

		$this->setExpectedTriggerError(E_USER_NOTICE, 'NO_FORUM');

		$controller = new mark_subforums_read(
			$this->auth,
			$this->config,
			$this->controller_helper,
			$this->forum_helper,
			$this->language,
			$this->request,
			$this->user,
			$this->phpbb_root_path,
			$this->php_ext
		);
		$controller->handle(999);
	}

	public function test_handle_unauthorized_registered_user(): void
	{
		$root_data = [
			'forum_id' => 10,
			'forum_name' => 'Secret Category',
			'left_id' => 1,
			'right_id' => 10,
		];
		$this->forum_helper->method('get_forum_data')->with(10)->willReturn($root_data);
		$this->auth->method('acl_gets')->with('f_list', 'f_read', 10)->willReturn(false);
		$this->user->data['user_id'] = 2;

		$this->setExpectedTriggerError(E_USER_NOTICE, 'SORRY_AUTH_READ');

		$controller = new mark_subforums_read(
			$this->auth,
			$this->config,
			$this->controller_helper,
			$this->forum_helper,
			$this->language,
			$this->request,
			$this->user,
			$this->phpbb_root_path,
			$this->php_ext
		);
		$controller->handle(10);
	}

	public function test_handle_invalid_hash(): void
	{
		$root_data = [
			'forum_id' => 10,
			'forum_name' => 'Category',
			'left_id' => 1,
			'right_id' => 10,
		];
		$this->forum_helper->method('get_forum_data')->with(10)->willReturn($root_data);
		$this->auth->method('acl_gets')->with('f_list', 'f_read', 10)->willReturn(true);
		$this->forum_helper->method('get_forums_rows')->willReturn([]);

		$this->request->method('variable')->willReturnMap([
			['hash', '', 'invalid_token'],
			['mark_time', 0, 0],
		]);
		$this->request->method('is_ajax')->willReturn(false);

		$this->setExpectedTriggerError(E_USER_NOTICE);

		$controller = new mark_subforums_read(
			$this->auth,
			$this->config,
			$this->controller_helper,
			$this->forum_helper,
			$this->language,
			$this->request,
			$this->user,
			$this->phpbb_root_path,
			$this->php_ext
		);
		$controller->handle(10);

		$this->assertFalse($this->markread_called);
	}

	public function test_handle_valid_hash_ajax_with_subforums(): void
	{
		$root_data = [
			'forum_id' => 10,
			'forum_name' => 'Category',
			'left_id' => 1,
			'right_id' => 10,
		];
		$this->forum_helper->method('get_forum_data')->with(10)->willReturn($root_data);
		$this->auth->method('acl_gets')->with('f_list', 'f_read', 10)->willReturn(true);

		$this->forum_helper->method('get_forums_rows')->with($root_data)->willReturn([
			['forum_id' => 20, 'forum_name' => 'Subforum 1'],
			['forum_id' => 30, 'forum_name' => 'Subforum 2'],
			['forum_id' => 40, 'forum_name' => 'Subforum Hidden'],
		]);
		$this->auth->method('acl_get')->willReturnMap([
			['f_list', 20, true],
			['f_list', 30, true],
			['f_list', 40, false],
		]);

		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);

		$controller = new mark_subforums_read(
			$this->auth,
			$this->config,
			$this->controller_helper,
			$this->forum_helper,
			$this->language,
			$this->request,
			$this->user,
			$this->phpbb_root_path,
			$this->php_ext
		);
		$response = $controller->handle(10);

		$this->assertTrue($this->markread_called);
		$this->assertEquals('topics', $this->markread_args['mode']);
		$this->assertEquals([20, 30], $this->markread_args['forum_id']);
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

	public function test_handle_valid_hash_ajax_with_no_subforums_does_not_call_markread(): void
	{
		$root_data = [
			'forum_id' => 10,
			'forum_name' => 'Category',
			'left_id' => 1,
			'right_id' => 10,
		];
		$this->forum_helper->method('get_forum_data')->with(10)->willReturn($root_data);
		$this->auth->method('acl_gets')->with('f_list', 'f_read', 10)->willReturn(true);
		$this->forum_helper->method('get_forums_rows')->with($root_data)->willReturn([]);

		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);

		$controller = new mark_subforums_read(
			$this->auth,
			$this->config,
			$this->controller_helper,
			$this->forum_helper,
			$this->language,
			$this->request,
			$this->user,
			$this->phpbb_root_path,
			$this->php_ext
		);
		$response = $controller->handle(10);

		$this->assertFalse($this->markread_called);
		$this->assertInstanceOf(JsonResponse::class, $response);
	}

	public function test_handle_valid_hash_non_ajax(): void
	{
		$root_data = [
			'forum_id' => 10,
			'forum_name' => 'Category',
			'left_id' => 1,
			'right_id' => 10,
		];
		$this->forum_helper->method('get_forum_data')->with(10)->willReturn($root_data);
		$this->auth->method('acl_gets')->with('f_list', 'f_read', 10)->willReturn(true);
		$this->forum_helper->method('get_forums_rows')->with($root_data)->willReturn([
			['forum_id' => 20, 'forum_name' => 'Subforum 1'],
		]);
		$this->auth->method('acl_get')->with('f_list', 20)->willReturn(true);

		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(false);

		$this->setExpectedTriggerError(E_USER_NOTICE);

		$controller = new mark_subforums_read(
			$this->auth,
			$this->config,
			$this->controller_helper,
			$this->forum_helper,
			$this->language,
			$this->request,
			$this->user,
			$this->phpbb_root_path,
			$this->php_ext
		);
		$controller->handle(10);

		$this->assertTrue($this->markread_called);
		$this->assertEquals('topics', $this->markread_args['mode']);
		$this->assertEquals([20], $this->markread_args['forum_id']);
	}
}
