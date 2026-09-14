<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\RedirectResponse;

class phpbb_mention_controller_test extends phpbb_database_test_case
{
	protected $controller_helper, $db, $container, $user, $config, $auth, $cache, $form_helper;

	/**
	 * @var \phpbb\mention\controller\mention
	 */
	protected $controller;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $request;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/mention.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $cache, $phpbb_dispatcher, $lang, $user, $phpEx, $phpbb_root_path, $user_loader;

		// Database
		$this->db = $this->new_dbal();

		// Auth
		$this->auth = $this->createMock('\phpbb\auth\auth');
		$this->auth->expects($this->any())
			 ->method('acl_gets')
			 ->with('a_group', 'a_groupadd', 'a_groupdel')
			 ->willReturn(false)
		;
		$this->auth->expects($this->any())
			 ->method('acl_get')
			 ->willReturnMap([
					['u_mention', 0, true],
					['f_mention', 1, true],
				]);

		// Config
		$this->config = new \phpbb\config\config(array(
			'allow_mentions'      => true,
			'mention_batch_size'  => 8,
			'mention_names_limit' => 3,
		));

		$this->form_helper = $this->getMockBuilder('\phpbb\form\form_helper')
			->disableOriginalConstructor()
			->getMock();
		$this->form_helper->method('get_form_tokens')
			->willReturn([
				'creation_time' => 1777098416,
				'form_token' => 'test_token',
			]);
		$this->form_helper->method('check_form_tokens')
			->willReturn(true);

		// Event dispatcher
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$cache_driver = new \phpbb\cache\driver\dummy();
		$cache = new \phpbb\cache\service(
			$cache_driver,
			$this->config,
			$this->db,
			$phpbb_dispatcher,
			$phpbb_root_path,
			$phpEx
		);

		// Language
		$lang = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		// User
		$user = $this->createMock('\phpbb\user');
		$user->ip = '';
		$user->data = array(
			'user_id'       => 2,
			'username'      => 'myself',
			'is_registered' => true,
			'user_colour'   => '',
		);

		// Request
		$this->request = $this->createMock('\phpbb\request\request');

		$this->request->expects($this->any())
				->method('is_ajax')
				->willReturn(true);
		$avatar_helper = $this->getMockBuilder('\phpbb\avatar\helper')
			->disableOriginalConstructor()
			->getMock();

		$user_loader = new \phpbb\user_loader($avatar_helper, $this->db, $phpbb_root_path, $phpEx, USERS_TABLE);

		// Controller helper
		$this->controller_helper = $this->createMock('\phpbb\controller\helper');

		// Container
		$this->container = new ContainerBuilder();

		$loader = new YamlFileLoader($this->container, new FileLocator(__DIR__ . '/fixtures'));
		$loader->load('services_mention.yml');
		$this->container->set('user_loader', $user_loader);
		$this->container->set('user', $user);
		$this->container->set('language', $lang);
		$this->container->set('config', $this->config);
		$this->container->set('dbal.conn', $this->db);
		$this->container->set('auth', $this->auth);
		$this->container->set('cache.driver', $cache_driver);
		$this->container->set('cache', $cache);
		$this->container->set('request', $this->request);
		$this->container->set('controller.helper', $this->controller_helper);
		$this->container->set('form_helper', $this->form_helper);
		$this->container->set('group_helper', new \phpbb\group\helper(
			$this->getMockBuilder('\phpbb\auth\auth')->disableOriginalConstructor()->getMock(),
			$avatar_helper,
			$this->db,
			$cache,
			$this->config,
			new \phpbb\language\language(
				new phpbb\language\language_file_loader($phpbb_root_path, $phpEx)
			),
			new phpbb_mock_event_dispatcher(),
			new \phpbb\path_helper(
				new \phpbb\symfony_request(
					new phpbb_mock_request()
				),
				$this->getMockBuilder('\phpbb\request\request')->disableOriginalConstructor()->getMock(),
				$phpbb_root_path,
				$phpEx
			),
			$this->getMockBuilder('\phpbb\template\template')->disableOriginalConstructor()->getMock(),
			$user
		));
		$this->container->set('text_formatter.utils', new \phpbb\textformatter\s9e\utils());
		$this->container->set(
			'text_formatter.s9e.mention_helper',
			new \phpbb\textformatter\s9e\mention_helper(
				$this->db,
				$this->auth,
				$user,
				$phpbb_root_path,
				$phpEx
			)
		);
		$this->container->setParameter('core.root_path', $phpbb_root_path);
		$this->container->setParameter('core.php_ext', $phpEx);
		$this->container->addCompilerPass(new phpbb\di\pass\markpublic_pass());
		$this->container->compile();

		// Mention Sources
		$mention_sources = array('friend', 'group', 'team', 'topic', 'user', 'usergroup');
		$mention_sources_array = new \phpbb\di\service_collection($this->container);
		foreach ($mention_sources as $source)
		{
			$mention_sources_array->add('mention.source.' . $source);
		}

		$this->controller = new \phpbb\mention\controller\mention(
			$this->auth,
			$this->config,
			$this->db,
			$this->form_helper,
			$mention_sources_array,
			$this->request,
			$this->controller_helper,
			$phpbb_root_path,
			$phpEx
		);
	}

	public static function handle_data()
	{
		/**
		 * NOTE:
		 * 1) in production comparison with 'myself' is being done in JS
		 * 2) team members of hidden groups can also be mentioned (because they are shown on teampage)
		 */
		return [
			['', 0, [
				'names' => [
					[
						'name'     => 'friend',
						'type'     => 'u',
						'id'       => 7,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'Group we are a member of',
						'type'     => 'g',
						'id'       => 3,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'Normal group',
						'type'     => 'g',
						'id'       => 1,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'team_member_hidden',
						'type'     => 'u',
						'id'       => 6,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'team_member_normal',
						'type'     => 'u',
						'id'       => 5,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'myself',
						'type'     => 'u',
						'id'       => 2,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'poster',
						'type'     => 'u',
						'id'       => 3,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'replier',
						'type'     => 'u',
						'id'       => 4,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'team_member_normal',
						'type'     => 'u',
						'id'       => 5,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'team_member_hidden',
						'type'     => 'u',
						'id'       => 6,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'friend',
						'type'     => 'u',
						'id'       => 7,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test',
						'type'     => 'u',
						'id'       => 8,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test1',
						'type'     => 'u',
						'id'       => 9,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'Group we are a member of',
						'type'     => 'g',
						'id'       => 3,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
				],
				'all'   => false,
				'form_tokens' => [
					'form_token' => 'test_token',
					'creation_time' => 1777098416,
				],
			]],
			['', 1, [
				'names' => [
					[
						'name'     => 'friend',
						'type'     => 'u',
						'id'       => 7,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'Group we are a member of',
						'type'     => 'g',
						'id'       => 3,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'Normal group',
						'type'     => 'g',
						'id'       => 1,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'team_member_hidden',
						'type'     => 'u',
						'id'       => 6,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'team_member_normal',
						'type'     => 'u',
						'id'       => 5,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'replier',
						'type'     => 'u',
						'id'       => 4,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'poster',
						'type'     => 'u',
						'id'       => 3,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 5,
					],
					[
						'name'     => 'myself',
						'type'     => 'u',
						'id'       => 2,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'poster',
						'type'     => 'u',
						'id'       => 3,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'replier',
						'type'     => 'u',
						'id'       => 4,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'team_member_normal',
						'type'     => 'u',
						'id'       => 5,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'team_member_hidden',
						'type'     => 'u',
						'id'       => 6,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'friend',
						'type'     => 'u',
						'id'       => 7,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test',
						'type'     => 'u',
						'id'       => 8,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test1',
						'type'     => 'u',
						'id'       => 9,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'Group we are a member of',
						'type'     => 'g',
						'id'       => 3,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
				],
				'all'   => false,
				'form_tokens' => [
					'form_token' => 'test_token',
					'creation_time' => 1777098416,
				],
			]],
			['t', 1, [
				'names' => [
					[
						'name'     => 'team_member_hidden',
						'type'     => 'u',
						'id'       => 6,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'team_member_normal',
						'type'     => 'u',
						'id'       => 5,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 1,
					],
					[
						'name'     => 'team_member_normal',
						'type'     => 'u',
						'id'       => 5,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'team_member_hidden',
						'type'     => 'u',
						'id'       => 6,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test',
						'type'     => 'u',
						'id'       => 8,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test1',
						'type'     => 'u',
						'id'       => 9,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test2',
						'type'     => 'u',
						'id'       => 10,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test3',
						'type'     => 'u',
						'id'       => 11,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
				],
				'all'   => true,
				'form_tokens' => [
					'form_token' => 'test_token',
					'creation_time' => 1777098416,
				],
			]],
			['test', 1, [
				'names' => [
					[
						'name'     => 'test',
						'type'     => 'u',
						'id'       => 8,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test1',
						'type'     => 'u',
						'id'       => 9,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test2',
						'type'     => 'u',
						'id'       => 10,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
					[
						'name'     => 'test3',
						'type'     => 'u',
						'id'       => 11,
						'avatar'   => [],
						'rank'     => '',
						'priority' => 0,
					],
				],
				'all'   => true,
				'form_tokens' => [
					'form_token' => 'test_token',
					'creation_time' => 1777098416,
				],
			]],
			['test1', 1, [
				'names' => [[
					'name'     => 'test1',
					'type'     => 'u',
					'id'       => 9,
					'avatar'   => [],
					'rank'     => '',
					'priority' => 0,
				]],
				'all'   => true,
				'form_tokens' => [
					'form_token' => 'test_token',
					'creation_time' => 1777098416,
				],
			]],
		];
	}

	/**
	 * @dataProvider handle_data
	 */
	public function test_handle($keyword, $topic_id, $expected_result)
	{
		$this->request->expects($this->atLeast(2))
			->method('variable')
			->willReturnCallback(function() use ($keyword, $topic_id) {
				$args = func_get_args();
				return match($args) {
					['keyword', '', true, \phpbb\request\request_interface::REQUEST] => $keyword,
					['topic_id', 0, false, \phpbb\request\request_interface::REQUEST] => $topic_id,
					['forum_id', 0, false, \phpbb\request\request_interface::REQUEST] => 1,
				};
			});

		$data = json_decode($this->controller->handle()->getContent(), true);
		$this->assertEquals($expected_result, $data);
	}

	public function test_redirect_not_ajax()
	{
		$this->controller_helper->method('route')
			->with('phpbb_index_controller')
			->willReturn('/index.php');

		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->request->method('is_ajax')
			->willReturn(false);
		$this->request->expects($this->atLeast(2))
			->method('variable')
			->willReturnCallback(function() {
				$args = func_get_args();
				return match($args) {
					['keyword', '', true, \phpbb\request\request_interface::REQUEST] => 'admin',
					['topic_id', 0, false, \phpbb\request\request_interface::REQUEST] => 2,
					['forum_id', 0, false, \phpbb\request\request_interface::REQUEST] => 1,
				};
			});

		$this->controller = new \phpbb\mention\controller\mention(
			$this->auth,
			$this->config,
			$this->db,
			$this->form_helper,
			new \phpbb\di\service_collection($this->container),
			$this->request,
			$this->controller_helper,
			'',
			''
		);

		$response = $this->controller->handle();
		$this->assertInstanceOf(RedirectResponse::class, $response);
	}

	public function test_redirect_no_forum_no_topic()
	{
		$this->controller_helper->method('route')
			->with('phpbb_index_controller')
			->willReturn('/index.php');

		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->request->method('is_ajax')
			->willReturn(true);
		$this->request->expects($this->atLeast(2))
			->method('variable')
			->willReturnCallback(function() {
				$args = func_get_args();
				return match($args) {
					['keyword', '', true, \phpbb\request\request_interface::REQUEST] => 'admin',
					['topic_id', 0, false, \phpbb\request\request_interface::REQUEST] => 0,
					['forum_id', 0, false, \phpbb\request\request_interface::REQUEST] => 0,
				};
			});

		$this->controller = new \phpbb\mention\controller\mention(
			$this->auth,
			$this->config,
			$this->db,
			$this->form_helper,
			new \phpbb\di\service_collection($this->container),
			$this->request,
			$this->controller_helper,
			'',
			''
		);

		$response = $this->controller->handle();
		$this->assertInstanceOf(RedirectResponse::class, $response);
	}

	public function test_redirect_invalid_form_token()
	{
		$this->controller_helper->method('route')
			->with('phpbb_index_controller')
			->willReturn('/index.php');

		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->request->method('is_ajax')
			->willReturn(true);
		$this->request->expects($this->atLeast(2))
			->method('variable')
			->willReturnCallback(function() {
				$args = func_get_args();
				return match($args) {
					['keyword', '', true, \phpbb\request\request_interface::REQUEST] => 'admin',
					['topic_id', 0, false, \phpbb\request\request_interface::REQUEST] => 9999,
					['forum_id', 0, false, \phpbb\request\request_interface::REQUEST] => 0,
				};
			});

		$this->form_helper = $this->getMockBuilder('\phpbb\form\form_helper')
			->disableOriginalConstructor()
			->getMock();
		$this->form_helper->method('get_form_tokens')
			->willReturn([
				'creation_time' => time(),
				'form_token' => 'test_token',
			]);
		$this->form_helper->method('check_form_tokens')
			->willReturn(false);

		$this->controller = new \phpbb\mention\controller\mention(
			$this->auth,
			$this->config,
			$this->db,
			$this->form_helper,
			new \phpbb\di\service_collection($this->container),
			$this->request,
			$this->controller_helper,
			'',
			''
		);

		$response = $this->controller->handle();
		$this->assertInstanceOf(RedirectResponse::class, $response);
	}

	public function test_redirect_invalid_topic_id()
	{
		$this->controller_helper->method('route')
			->with('phpbb_index_controller')
			->willReturn('/index.php');

		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->request->method('is_ajax')
			->willReturn(true);
		$this->request->expects($this->atLeast(2))
			->method('variable')
			->willReturnCallback(function() {
				$args = func_get_args();
				return match($args) {
					['keyword', '', true, \phpbb\request\request_interface::REQUEST] => 'admin',
					['topic_id', 0, false, \phpbb\request\request_interface::REQUEST] => 9999,
					['forum_id', 0, false, \phpbb\request\request_interface::REQUEST] => 0,
				};
			});

		$this->controller = new \phpbb\mention\controller\mention(
			$this->auth,
			$this->config,
			$this->db,
			$this->form_helper,
			new \phpbb\di\service_collection($this->container),
			$this->request,
			$this->controller_helper,
			'',
			''
		);

		$response = $this->controller->handle();
		$this->assertInstanceOf(RedirectResponse::class, $response);
	}

	public function test_redirect_no_u_mention()
	{
		$this->controller_helper->method('route')
			->with('phpbb_index_controller')
			->willReturn('/index.php');

		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->request->method('is_ajax')
			->willReturn(true);
		$this->request->expects($this->atLeast(2))
			->method('variable')
			->willReturnCallback(function() {
				$args = func_get_args();
				return match($args) {
					['keyword', '', true, \phpbb\request\request_interface::REQUEST] => 'admin',
					['topic_id', 0, false, \phpbb\request\request_interface::REQUEST] => 2,
					['forum_id', 0, false, \phpbb\request\request_interface::REQUEST] => 1,
				};
			});

		$this->auth = $this->createMock('\phpbb\auth\auth');
		$this->auth->expects($this->any())
			->method('acl_gets')
			->with('a_group', 'a_groupadd', 'a_groupdel')
			->willReturn(false)
		;
		$this->auth->expects($this->any())
			->method('acl_get')
			->willReturnMap([
				['u_mention', 0, false],
				['f_mention', 1, true],
			]);

		$this->controller = new \phpbb\mention\controller\mention(
			$this->auth,
			$this->config,
			$this->db,
			$this->form_helper,
			new \phpbb\di\service_collection($this->container),
			$this->request,
			$this->controller_helper,
			'',
			''
		);

		$response = $this->controller->handle();
		$this->assertInstanceOf(RedirectResponse::class, $response);
	}

	public function test_redirect_no_f_mention()
	{
		$this->controller_helper->method('route')
			->with('phpbb_index_controller')
			->willReturn('/index.php');

		$this->request = $this->getMockBuilder('\phpbb\request\request')
			->disableOriginalConstructor()
			->getMock();
		$this->request->method('is_ajax')
			->willReturn(true);
		$this->request->expects($this->atLeast(2))
			->method('variable')
			->willReturnCallback(function() {
				$args = func_get_args();
				return match($args) {
					['keyword', '', true, \phpbb\request\request_interface::REQUEST] => 'admin',
					['topic_id', 0, false, \phpbb\request\request_interface::REQUEST] => 2,
					['forum_id', 0, false, \phpbb\request\request_interface::REQUEST] => 1,
				};
			});

		$this->auth = $this->createMock('\phpbb\auth\auth');
		$this->auth->expects($this->any())
			->method('acl_gets')
			->with('a_group', 'a_groupadd', 'a_groupdel')
			->willReturn(false)
		;
		$this->auth->expects($this->any())
			->method('acl_get')
			->willReturnMap([
				['u_mention', 0, true],
				['f_mention', 1, false],
			]);

		$this->controller = new \phpbb\mention\controller\mention(
			$this->auth,
			$this->config,
			$this->db,
			$this->form_helper,
			new \phpbb\di\service_collection($this->container),
			$this->request,
			$this->controller_helper,
			'',
			''
		);

		$response = $this->controller->handle();
		$this->assertInstanceOf(RedirectResponse::class, $response);
	}
}
