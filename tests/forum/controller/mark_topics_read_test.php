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
use phpbb\exception\http_exception;
use phpbb\forum\controller\mark_topics_read;
use phpbb\forum\helper as forum_helper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class phpbb_forum_controller_mark_topics_read_test extends phpbb_test_case
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
		$this->controller_helper->method('message')
			->willReturnCallback(function ($message, $parameters = [], $title = 'INFORMATION', $code = 200) {
				return new Response($message, $code);
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

		$controller = new mark_topics_read(
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

		try
		{
			$controller->handle(999);
			$this->fail('Expected http_exception was not thrown');
		}
		catch (http_exception $e)
		{
			$this->assertSame(404, $e->getStatusCode());
			$this->assertSame('NO_FORUM', $e->getMessage());
		}
	}

	public function test_handle_unauthorized_registered_user(): void
	{
		$forum_data = [
			'forum_id' => 5,
			'forum_name' => 'Secret Forum',
		];
		$this->forum_helper->method('get_forum_data')->with(5)->willReturn($forum_data);
		$this->auth->method('acl_gets')->with('f_read', 'f_list_topics', 5)->willReturn(false);
		$this->user->data['user_id'] = 2;

		$controller = new mark_topics_read(
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

		try
		{
			$controller->handle(5);
			$this->fail('Expected http_exception was not thrown');
		}
		catch (http_exception $e)
		{
			$this->assertSame(403, $e->getStatusCode());
			$this->assertSame('SORRY_AUTH_READ', $e->getMessage());
		}

		$this->assertFalse($this->markread_called);
	}

	public function test_handle_invalid_hash(): void
	{
		$forum_data = [
			'forum_id' => 5,
			'forum_name' => 'General Discussion',
		];
		$this->forum_helper->method('get_forum_data')->with(5)->willReturn($forum_data);
		$this->auth->method('acl_gets')->with('f_read', 'f_list_topics', 5)->willReturn(true);

		$this->request->method('variable')->willReturnMap([
			['hash', '', 'invalid_token'],
			['mark_time', 0, 0],
		]);
		$this->request->method('is_ajax')->willReturn(false);

		$controller = new mark_topics_read(
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

		try
		{
			$controller->handle(5);
			$this->fail('Expected http_exception was not thrown');
		}
		catch (http_exception $e)
		{
			$this->assertSame(403, $e->getStatusCode());
			$this->assertSame('NOT_AUTHORISED', $e->getMessage());
		}

		$this->assertFalse($this->markread_called);
	}

	public function test_handle_valid_hash_ajax_registered(): void
	{
		$forum_data = [
			'forum_id' => 5,
			'forum_name' => 'General Discussion',
		];
		$this->forum_helper->method('get_forum_data')->with(5)->willReturn($forum_data);
		$this->auth->method('acl_gets')->with('f_read', 'f_list_topics', 5)->willReturn(true);

		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);

		$controller = new mark_topics_read(
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
		$response = $controller->handle(5);

		$this->assertTrue($this->markread_called);
		$this->assertEquals('topics', $this->markread_args['mode']);
		$this->assertEquals(5, $this->markread_args['forum_id']);
		$this->assertEquals(1600000000, $this->markread_args['post_time']);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$data = json_decode($response->getContent(), true);
		$this->assertArrayHasKey('NO_UNREAD_POSTS', $data);
		$this->assertArrayHasKey('UNREAD_POSTS', $data);
		$this->assertArrayHasKey('U_MARK_TOPICS', $data);
		$this->assertNotEmpty($data['U_MARK_TOPICS']);
		$this->assertArrayHasKey('MESSAGE_TITLE', $data);
		$this->assertArrayHasKey('MESSAGE_TEXT', $data);
	}

	public function test_handle_valid_hash_ajax_anonymous(): void
	{
		$forum_data = [
			'forum_id' => 5,
			'forum_name' => 'General Discussion',
		];
		$this->forum_helper->method('get_forum_data')->with(5)->willReturn($forum_data);
		$this->auth->method('acl_gets')->with('f_read', 'f_list_topics', 5)->willReturn(true);

		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['is_registered'] = false;
		$this->config->set('load_anon_lastread', false);

		$controller = new mark_topics_read(
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
		$response = $controller->handle(5);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$data = json_decode($response->getContent(), true);
		$this->assertSame('', $data['U_MARK_TOPICS']);
	}

	public function test_handle_valid_hash_non_ajax(): void
	{
		$forum_data = [
			'forum_id' => 5,
			'forum_name' => 'General Discussion',
		];
		$this->forum_helper->method('get_forum_data')->with(5)->willReturn($forum_data);
		$this->auth->method('acl_gets')->with('f_read', 'f_list_topics', 5)->willReturn(true);

		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(false);

		$controller = new mark_topics_read(
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
		$response = $controller->handle(5);

		$this->assertTrue($this->markread_called);
		$this->assertEquals('topics', $this->markread_args['mode']);
		$this->assertEquals(5, $this->markread_args['forum_id']);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertNotInstanceOf(JsonResponse::class, $response);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertStringContainsString($this->language->lang('TOPICS_MARKED'), $response->getContent());
	}

	public static function non_post_forum_types_data(): array
	{
		return [
			'category'	=> [FORUM_CAT],
			'link'		=> [FORUM_LINK],
		];
	}

	/**
	 * @dataProvider non_post_forum_types_data
	 */
	public function test_handle_non_post_forum_throws(int $forum_type): void
	{
		$forum_data = [
			'forum_id' => 5,
			'forum_name' => 'Non-post Forum',
			'forum_type' => $forum_type,
		];
		$this->forum_helper->method('get_forum_data')->with(5)->willReturn($forum_data);
		$this->auth->method('acl_gets')->with('f_read', 'f_list_topics', 5)->willReturn(true);

		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['hash', '', $valid_hash],
			['mark_time', 0, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);

		$controller = new mark_topics_read(
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

		try
		{
			$controller->handle(5);
			$this->fail('Expected http_exception was not thrown');
		}
		catch (http_exception $e)
		{
			$this->assertSame(400, $e->getStatusCode());
			$this->assertSame('NO_TOPICS', $e->getMessage());
		}

		$this->assertFalse($this->markread_called);
	}

	public function test_handle_passworded_forum_with_access(): void
	{
		global $db;

		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$db->method('sql_escape')->willReturnArgument(0);
		$db->method('sql_query')->willReturn(true);
		$db->method('sql_fetchrow')->willReturn(['forum_id' => 5]);

		$forum_data = [
			'forum_id' => 5,
			'forum_name' => 'Passworded Forum',
			'forum_type' => FORUM_POST,
			'forum_password' => '$2y$10$hashedpassword',
		];
		$this->forum_helper->method('get_forum_data')->with(5)->willReturn($forum_data);
		$this->auth->method('acl_gets')->with('f_read', 'f_list_topics', 5)->willReturn(true);

		$valid_hash = generate_link_hash('global');
		$this->request->method('variable')->willReturnMap([
			['password', '', true, ''],
			['hash', '', false, $valid_hash],
			['mark_time', 0, false, 1600000000],
		]);
		$this->request->method('is_ajax')->willReturn(true);

		$controller = new mark_topics_read(
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
		$response = $controller->handle(5);

		$this->assertTrue($this->markread_called);
		$this->assertEquals(5, $this->markread_args['forum_id']);
		$this->assertInstanceOf(JsonResponse::class, $response);
	}
}
