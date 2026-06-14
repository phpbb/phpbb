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
use phpbb\notification\type\quote;
use phpbb\request\request_interface;
use phpbb\ucp\controller\webpush;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class test_ucp_controller_webpush_test extends phpbb_database_test_case
{
	protected $auth;
	protected $avatar_helper;
	protected $config;
	protected $controller;
	protected $controller_helper;
	protected $db;
	protected $dispatcher;
	protected $form_helper;
	protected $language;
	protected $notification_manager;
	protected $path_helper;
	protected $phpbb_root_path;
	protected $php_ext;
	protected $request;
	protected $template;
	protected $user;
	protected $user_loader;

	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/webpush.xml');
	}

	protected function setUp(): void
	{
		global $auth, $config, $phpbb_dispatcher, $user, $phpbb_root_path, $phpEx;

		parent::setUp();

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$this->auth = $this->createMock(\phpbb\auth\auth::class);
		$auth = $this->auth;
		$this->avatar_helper = $this->createMock(\phpbb\avatar\helper::class);
		$this->config = new \phpbb\config\config([
			'allow_nocensors'	=> false,
			'sitename'			=> 'yourdomain.com',
		]);
		$config = $this->config;
		$this->controller_helper = $this->createMock(\phpbb\controller\helper::class);
		$this->db = $this->new_dbal();
		$this->dispatcher = $this->createMock(\phpbb\event\dispatcher::class);
		$this->form_helper = $this->createMock(\phpbb\form\form_helper::class);
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$this->language = new \phpbb\language\language($lang_loader);
		$this->notification_manager = $this->createMock(\phpbb\notification\manager::class);
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		$symfony_request = $this->createMock(\phpbb\symfony_request::class);
		$this->request = $this->createMock(request_interface::class);
		$this->template = $this->createMock(\Twig\Environment::class);
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');

		$user = $this->user;
		$this->user_loader = new \phpbb\user_loader($this->avatar_helper, $this->db, $this->phpbb_root_path, $this->php_ext, 'phpbb_users');
		$this->path_helper = new \phpbb\path_helper($symfony_request, $this->request, $phpbb_root_path, $phpEx);

		$this->controller = new webpush(
			$this->config,
			$this->controller_helper,
			$this->db,
			$this->dispatcher,
			$this->form_helper,
			$this->language,
			$this->notification_manager,
			$this->path_helper,
			$this->request,
			$this->user_loader,
			$this->user,
			$this->template,
			'phpbb_notification_push',
			'phpbb_push_subscriptions'
		);
	}

	public static function data_notification_exceptions(): array
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
			->willReturnMap($request_data);
		$this->user->data = [
			'is_bot'		=> $is_bot,
			'user_type'		=> $user_type,
			'user_id'		=> $user_id,
		];

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage($expected_message);

		$this->controller->notification();
	}

	public function test_get_user_notification()
	{
		global $cache;
		$cache = $this->createMock(\phpbb\cache\service::class);
		$cache->method('obtain_word_list')->willReturn([]);

		$this->auth->method('acl_get')->willReturn(true);

		$this->notification_manager->method('get_item_type_class')
			->willReturnCallback(function(string $type_name, array $row_data) {
				$notification_type = new quote(
					$this->avatar_helper,
					$this->controller_helper,
					$this->db,
					$this->language,
					$this->user,
					$this->auth,
					$this->phpbb_root_path,
					$this->php_ext,
					'phpbb_notifications'
				);

				$notification_type->set_user_loader($this->user_loader);
				$notification_type->set_initial_data($row_data);

				return $notification_type;
			});

		$this->request->method('is_ajax')->willReturn(true);
		$this->request->expects($this->any())
			->method('variable')
			->willReturnMap([
				['token', '', false, request_interface::REQUEST, 'foobar'],
				['item_id', 0, false, request_interface::REQUEST, 1],
				['type_id', 0, false, request_interface::REQUEST, 4],
			]);
		$this->user->data = [
			'is_bot'		=> false,
			'user_type'		=> USER_NORMAL,
			'user_id'		=> 2,
			'user_options'	=> 230271,
		];

		$json_response = $this->controller->notification();

		$response_data = json_decode($json_response->getContent(), true);

		$this->assertEquals([
			'heading' => 'yourdomain.com',
			'title' => 'Quoted by Guest in:',
			'text' => '"Welcome to phpBB"',
			'url' => 'phpBB/viewtopic.php?p=1#p1',
			'avatar' => [],
		], $response_data);
	}

	public function test_get_user_notification_anonymous()
	{
		global $cache;
		$cache = $this->createMock(\phpbb\cache\service::class);
		$cache->method('obtain_word_list')->willReturn([]);

		$this->auth->method('acl_get')->willReturn(true);

		$this->notification_manager->method('get_item_type_class')
			->willReturnCallback(function(string $type_name, array $row_data) {
				$notification_type = new quote(
					$this->avatar_helper,
					$this->controller_helper,
					$this->db,
					$this->language,
					$this->user,
					$this->auth,
					$this->phpbb_root_path,
					$this->php_ext,
					'phpbb_notifications'
				);

				$notification_type->set_user_loader($this->user_loader);
				$notification_type->set_initial_data($row_data);

				return $notification_type;
			});

		$this->request->method('is_ajax')->willReturn(true);
		$this->request->expects($this->any())
			->method('variable')
			->willReturnMap([
				['token', '', false, request_interface::REQUEST, '0ccf8fcd96a66297b77b66109cbe9870e1a6fa66e42b9bf36d1f2c7263240058'],
				['item_id', 0, false, request_interface::REQUEST, 1],
				['type_id', 0, false, request_interface::REQUEST, 4],
				['user_id', 0, false, request_interface::REQUEST, 2],
			]);
		$this->user->data = [
			'is_bot'		=> false,
			'user_type'		=> USER_NORMAL,
			'user_id'		=> ANONYMOUS,
			'user_options'	=> 230271,
		];

		$json_response = $this->controller->notification();

		$response_data = json_decode($json_response->getContent(), true);

		$this->assertEquals([
			'heading' => 'yourdomain.com',
			'title' => 'Quoted by Guest in:',
			'text' => '"Welcome to phpBB"',
			'url' => 'phpBB/viewtopic.php?p=1#p1',
			'avatar' => [],
		], $response_data);
	}

	public function test_get_user_notification_anonymous_invalid_token()
	{
		global $cache;
		$cache = $this->createMock(\phpbb\cache\service::class);
		$cache->method('obtain_word_list')->willReturn([]);

		$this->auth->method('acl_get')->willReturn(true);

		$this->notification_manager->method('get_item_type_class')
			->willReturnCallback(function(string $type_name, array $row_data) {
				$notification_type = new quote(
					$this->avatar_helper,
					$this->controller_helper,
					$this->db,
					$this->language,
					$this->user,
					$this->auth,
					$this->phpbb_root_path,
					$this->php_ext,
					'phpbb_notifications'
				);

				$notification_type->set_user_loader($this->user_loader);
				$notification_type->set_initial_data($row_data);

				return $notification_type;
			});

		$this->request->method('is_ajax')->willReturn(true);
		$this->request->expects($this->any())
			->method('variable')
			->willReturnMap([
				['token', '', false, request_interface::REQUEST, '488c17afe4f18714c235b395e21b78df1c3d78bf1e13d0633ed9425d2eebf967'],
				['item_id', 0, false, request_interface::REQUEST, 1],
				['type_id', 0, false, request_interface::REQUEST, 4],
				['user_id', 0, false, request_interface::REQUEST, 2],
			]);
		$this->user->data = [
			'is_bot'		=> false,
			'user_type'		=> USER_NORMAL,
			'user_id'		=> ANONYMOUS,
			'user_options'	=> 230271,
		];

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage('NO_AUTH_OPERATION');

		$this->controller->notification();
	}

	public function test_get_user_notification_legacy()
	{
		global $cache;
		$cache = $this->createMock(\phpbb\cache\service::class);
		$cache->method('obtain_word_list')->willReturn([]);

		$this->auth->method('acl_get')->willReturn(true);

		$this->notification_manager->method('get_item_type_class')
			->willReturnCallback(function(string $type_name, array $row_data) {
				$notification_type = new quote(
					$this->avatar_helper,
					$this->controller_helper,
					$this->db,
					$this->language,
					$this->user,
					$this->auth,
					$this->phpbb_root_path,
					$this->php_ext,
					'phpbb_notifications'
				);

				$notification_type->set_user_loader($this->user_loader);
				$notification_type->set_initial_data($row_data);

				return $notification_type;
			});

		$this->request->method('is_ajax')->willReturn(true);
		$this->request->expects($this->any())
			->method('variable')
			->willReturnMap([
				['token', '', false, request_interface::REQUEST, 'foobar'],
				['item_id', 0, false, request_interface::REQUEST, 2],
				['type_id', 0, false, request_interface::REQUEST, 4],
			]);
		$this->user->data = [
			'is_bot'		=> false,
			'user_type'		=> USER_NORMAL,
			'user_id'		=> 2,
			'user_options'	=> 230271,
		];

		$json_response = $this->controller->notification();

		$response_data = json_decode($json_response->getContent(), true);

		$this->assertEquals([
			'heading' => 'yourdomain.com',
			'title' => 'Quoted by Guest in:',
			'text' => '"Welcome to phpBB"',
			'url' => 'phpBB/viewtopic.php?p=1#p1',
			'avatar' => [],
		], $response_data);
	}

	public function test_worker()
	{
		$this->template->method('render')->willReturn('rendered_content');
		$this->controller_helper->method('route')->willReturn('test_route');
		$this->config['assets_version'] = '1.0';

		$response = $this->controller->worker();

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals('text/javascript; charset=UTF-8', $response->headers->get('Content-Type'));
		$this->assertEquals('rendered_content', $response->getContent());
		$this->assertNull($response->headers->get('X-PHPBB-IS-BOT'));
	}

	public function test_worker_bot()
	{
		$this->template->method('render')->willReturn('rendered_content');
		$this->controller_helper->method('route')->willReturn('test_route');
		$this->config['assets_version'] = '1.0';
		$this->user->data['is_bot'] = true;

		$response = $this->controller->worker();

		$this->assertEquals('yes', $response->headers->get('X-PHPBB-IS-BOT'));
	}

	public function test_check_subscribe_requests_invalid_form_token()
	{
		$this->form_helper->method('check_form_tokens')->willReturn(false);

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage('FORM_INVALID');

		$check_subscribe_reflection = new ReflectionMethod($this->controller, 'check_subscribe_requests');
		$check_subscribe_reflection->invoke($this->controller);
	}

	public function test_check_subscribe_requests_anonymous_user()
	{
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = ANONYMOUS;

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage('NO_AUTH_OPERATION');

		$check_subscribe_reflection = new ReflectionMethod($this->controller, 'check_subscribe_requests');
		$check_subscribe_reflection->invoke($this->controller);
	}

	public function test_subscribe_success()
	{
		$this->dispatcher->method('trigger_event')
			->with(
				$this->equalTo('core.ucp_webpush_controller_verify_endpoint'),
				$this->anything()
			)
			->willReturnCallback(function($event_name, $args) {
				return $args;
			});
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = 2;
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;

		$symfony_request = $this->createMock(\phpbb\symfony_request::class);
		$symfony_request->method('getContent')->willReturn(json_encode([
			'endpoint' => 'https://fcm.googleapis.com/fcm/send/test_endpoint',
			'expiration_time' => 0,
			'keys' => ['p256dh' => 'test_p256dh', 'auth' => 'test_auth']
		]);

		$response = $this->controller->subscribe($symfony_request);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(['success' => true, 'form_tokens' => $this->form_helper->get_form_tokens(webpush::FORM_TOKEN_UCP)], json_decode($response->getContent(), true));

		// Get subscription data from database
		$sql = 'SELECT *
				FROM phpbb_push_subscriptions
				WHERE user_id = 2';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals([
			'user_id' => '2',
			'endpoint' => 'https://fcm.googleapis.com/fcm/send/test_endpoint',
			'p256dh' => 'test_p256dh',
			'auth' => 'test_auth',
			'expiration_time' => 0,
			'subscription_id' => '1',
		], $row);
	}

	public static function data_subscribe_missing_endpoint(): array
	{
		return [
			'missing_endpoint'	=> [[
				'expiration_time'	=> 0,
				'keys'				=> ['p256dh' => 'test_p256dh', 'auth' => 'test_auth'],
			]],
			'null_endpoint'		=> [[
				'endpoint'			=> null,
				'expiration_time'	=> 0,
				'keys'				=> ['p256dh' => 'test_p256dh', 'auth' => 'test_auth'],
			]],
			'array_endpoint'	=> [[
				'endpoint'			=> ['https://fcm.googleapis.com/fcm/send/test_endpoint'],
				'expiration_time'	=> 0,
				'keys'				=> ['p256dh' => 'test_p256dh', 'auth' => 'test_auth'],
			]],
		];
	}

	/**
	 * @dataProvider data_subscribe_missing_endpoint
	 */
	public function test_subscribe_missing_endpoint(array $payload): void
	{
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = 2;
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;

		$symfony_request = $this->create_symfony_request($payload);

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage('NOTIFY_WEB_PUSH_UNSUPPORTED_SERVICE');

		$this->controller->subscribe($symfony_request);
	}

	public function test_subscribe_unsupported_endpoint()
	{
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = 2;
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;

		$symfony_request = $this->createMock(\phpbb\symfony_request::class);
		$symfony_request->method('getContent')->willReturn(json_encode([
			'endpoint' => 'test_endpoint',
			'expiration_time' => 0,
			'keys' => ['p256dh' => 'test_p256dh', 'auth' => 'test_auth']
		]);

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage('NOTIFY_WEB_PUSH_UNSUPPORTED_SERVICE');

		$this->controller->subscribe($symfony_request);
	}
	public static function data_provider_subscribe_supported_endpoint(): array
	{
		return [
			'fcm' => ['https://fcm.googleapis.com/fcm/send/test_endpoint'],
			'apns' => ['https://api.push.apple.com/3/device/test_endpoint'],
			'apns_sandbox' => ['https://api.sandbox.push.apple.com/3/device/test_endpoint'],
		];
	}

	/**
	 * @dataProvider data_provider_subscribe_supported_endpoint
	 */
	public function test_subscribe_supported_endpoint($data_provider)
	{
		$this->dispatcher->method('trigger_event')
			->with(
				$this->equalTo('core.ucp_webpush_controller_verify_endpoint'),
				$this->anything()
			)
			->willReturnCallback(function($event_name, $args) {
				return $args;
			});
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = 2;
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;

		$symfony_request = $this->createMock(\phpbb\symfony_request::class);
		$symfony_request->method('getContent')->willReturn(json_encode([
			'endpoint' => $data_provider,
			'expiration_time' => 0,
			'keys' => ['p256dh' => 'test_p256dh', 'auth' => 'test_auth']
		]);

		$response = $this->controller->subscribe($symfony_request);

		$this->assertInstanceOf(JsonResponse::class, $response);
	}

	public function test_subscribe_success_event_endpoint()
	{
		// Mock triggering of expected event and add new valid endpoint
		$this->dispatcher->method('trigger_event')
			->with(
				$this->equalTo('core.ucp_webpush_controller_verify_endpoint'),
				$this->anything()
			)
			->willReturnCallback(function($event_name, $args) {
				$args['allowed_services'][] = 'test.endpoint.com';
				return $args;
			});
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = 2;
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;

		$symfony_request = $this->createMock(\phpbb\symfony_request::class);
		$symfony_request->method('getContent')->willReturn(json_encode([
			'endpoint' => 'https://test.endpoint.com/send/candy/test_endpoint',
			'expiration_time' => 0,
			'keys' => ['p256dh' => 'test_p256dh', 'auth' => 'test_auth']
		]);

		$response = $this->controller->subscribe($symfony_request);

		$this->assertEquals(['success' => true, 'form_tokens' => $this->form_helper->get_form_tokens(webpush::FORM_TOKEN_UCP)], json_decode($response->getContent(), true));

		// Get subscription data from database
		$sql = 'SELECT *
				FROM phpbb_push_subscriptions
				WHERE user_id = 2
				ORDER BY subscription_id DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals([
			'user_id' => '2',
			'endpoint' => 'https://test.endpoint.com/send/candy/test_endpoint',
			'p256dh' => 'test_p256dh',
			'auth' => 'test_auth',
			'expiration_time' => 0,
			'subscription_id' => $row['subscription_id'] ?? 0, // will have to take ID from database
		], $row);
	}

	public function test_unsubscribe_success()
	{
		$this->dispatcher->method('trigger_event')
			->with(
				$this->equalTo('core.ucp_webpush_controller_verify_endpoint'),
				$this->anything()
			)
			->willReturnCallback(function($event_name, $args) {
				return $args;
			});
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = 2;
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;

		$symfony_request = $this->createMock(\phpbb\symfony_request::class);
		$symfony_request->method('getContent')->willReturn(json_encode([
			'endpoint' => 'https://fcm.googleapis.com/fcm/send/test_endpoint',
			'expiration_time' => 0,
			'keys' => ['p256dh' => 'test_p256dh', 'auth' => 'test_auth']
		]);

		$response = $this->controller->subscribe($symfony_request);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(['success' => true, 'form_tokens' => $this->form_helper->get_form_tokens(webpush::FORM_TOKEN_UCP)], json_decode($response->getContent(), true));

		// Get subscription data from database
		$sql = 'SELECT *
				FROM phpbb_push_subscriptions
				WHERE user_id = 2';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals([
			'user_id' => '2',
			'endpoint' => 'https://fcm.googleapis.com/fcm/send/test_endpoint',
			'p256dh' => 'test_p256dh',
			'auth' => 'test_auth',
			'expiration_time' => 0,
			'subscription_id' => '1',
		], $row);

		// Now unsubscribe
		$response = $this->controller->unsubscribe($symfony_request);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(['success' => true, 'form_tokens' => $this->form_helper->get_form_tokens(webpush::FORM_TOKEN_UCP)], json_decode($response->getContent(), true));

		// Get subscription data from database
		$sql = 'SELECT *
				FROM phpbb_push_subscriptions
				WHERE user_id = 2
				  AND subscription_id = 1';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEmpty($row);
	}

	public function test_subscribe_replaces_previous_subscription(): void
	{
		$this->dispatcher->method('trigger_event')
			->with(
				$this->equalTo('core.ucp_webpush_controller_verify_endpoint'),
				$this->anything()
			)
			->willReturnCallback(function($event_name, $args) {
				return $args;
			});
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = 2;
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;

		$sql = 'INSERT INTO phpbb_push_subscriptions ' . $this->db->sql_build_array('INSERT', [
			'user_id'			=> 2,
			'endpoint'			=> 'https://fcm.googleapis.com/fcm/send/old_endpoint',
			'expiration_time'	=> 0,
			'p256dh'			=> 'old_p256dh',
			'auth'				=> 'old_auth',
		]);
		$this->db->sql_query($sql);

		$symfony_request = $this->create_symfony_request([
			'endpoint' => 'https://fcm.googleapis.com/fcm/send/new_endpoint',
			'previous_endpoint' => 'https://fcm.googleapis.com/fcm/send/old_endpoint',
			'expirationTime' => 42000,
			'keys' => ['p256dh' => 'new_p256dh', 'auth' => 'new_auth']
		]);

		$response = $this->controller->subscribe($symfony_request);

		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertEquals(['success' => true, 'form_tokens' => $this->form_helper->get_form_tokens(webpush::FORM_TOKEN_UCP)], json_decode($response->getContent(), true));

		$subscriptions = $this->get_all_subscriptions(2);
		$this->assertCount(1, $subscriptions);
		$this->assertEquals([
			'user_id' => '2',
			'endpoint' => 'https://fcm.googleapis.com/fcm/send/new_endpoint',
			'p256dh' => 'new_p256dh',
			'auth' => 'new_auth',
			'expiration_time' => '42',
			'subscription_id' => '2',
		], $subscriptions[0]);
	}

	public function test_subscribe_invalid_payload_preserves_existing_subscription(): void
	{
		$this->dispatcher->method('trigger_event')
			->with(
				$this->equalTo('core.ucp_webpush_controller_verify_endpoint'),
				$this->anything()
			)
			->willReturnCallback(function($event_name, $args) {
				return $args;
			});
		$this->form_helper->method('check_form_tokens')->willReturn(true);
		$this->request->method('is_ajax')->willReturn(true);
		$this->user->data['user_id'] = 2;
		$this->user->data['is_bot'] = false;
		$this->user->data['user_type'] = USER_NORMAL;

		$sql = 'INSERT INTO phpbb_push_subscriptions ' . $this->db->sql_build_array('INSERT', [
			'user_id'			=> 2,
			'endpoint'			=> 'https://fcm.googleapis.com/fcm/send/old_endpoint',
			'expiration_time'	=> 10,
			'p256dh'			=> 'old_p256dh',
			'auth'				=> 'old_auth',
		]);
		$this->db->sql_query($sql);

		$symfony_request = $this->create_symfony_request([
			'endpoint' => 'https://fcm.googleapis.com/fcm/send/new_endpoint',
			'previous_endpoint' => 'https://fcm.googleapis.com/fcm/send/old_endpoint',
			'expirationTime' => 42000,
			'keys' => ['auth' => 'new_auth']
		]);

		$this->expectException(http_exception::class);
		$this->expectExceptionMessage('AJAX_ERROR_TEXT');

		try
		{
			$this->controller->subscribe($symfony_request);
		}
		finally
		{
			$subscriptions = $this->get_all_subscriptions(2);
			$this->assertCount(1, $subscriptions);
			$this->assertEquals([
				'user_id' => '2',
				'endpoint' => 'https://fcm.googleapis.com/fcm/send/old_endpoint',
				'p256dh' => 'old_p256dh',
				'auth' => 'old_auth',
				'expiration_time' => '10',
				'subscription_id' => '1',
			], $subscriptions[0]);
		}
	}

	private function create_symfony_request(array $data): \phpbb\symfony_request
	{
		$phpbb_request = $this->createMock(\phpbb\request\request_interface::class);
		$phpbb_request->method('get_super_global')->willReturnMap([
			[\phpbb\request\request_interface::POST, ['data' => json_encode($data)]],
			[\phpbb\request\request_interface::GET, []],
			[\phpbb\request\request_interface::SERVER, []],
			[\phpbb\request\request_interface::FILES, []],
			[\phpbb\request\request_interface::COOKIE, []],
		]);
		return new \phpbb\symfony_request($phpbb_request);
	}

	private function get_all_subscriptions(int $user_id): array
	{
		$sql = 'SELECT *
			FROM phpbb_push_subscriptions
			WHERE user_id = ' . $user_id . '
			ORDER BY subscription_id ASC';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);
		return $rows;
	}
}
