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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_posting.php';

class phpbb_mention_controller_test extends phpbb_database_test_case
{
	protected $db, $container, $user, $config, $auth, $cache;

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

	public function setUp()
	{
		parent::setUp();

		global $auth, $cache, $config, $db, $phpbb_container, $phpbb_dispatcher, $lang, $user, $request, $phpEx, $phpbb_root_path, $user_loader;

		// Database
		$this->db = $this->new_dbal();
		$db = $this->db;

		// Auth
		$auth = $this->createMock('\phpbb\auth\auth');
		$auth->expects($this->any())
			->method('acl_gets')
			->with('a_group', 'a_groupadd', 'a_groupdel')
			->willReturn(false);

		// Config
		$config = new \phpbb\config\config(array(
			'allow_mentions' => true,
			'mention_names_limit'	=> 3,
		));

		$cache_driver = new \phpbb\cache\driver\dummy();
		$cache = new \phpbb\cache\service(
			$cache_driver,
			$config,
			$db,
			$phpbb_root_path,
			$phpEx
		);

		// Event dispatcher
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		// Language
		$lang = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		// User
		$user = $this->createMock('\phpbb\user', array(), array(
			$lang,
			'\phpbb\datetime'
		));
		$user->ip = '';
		$user->data = array(
			'user_id'		=> 2,
			'username'		=> 'myself',
			'is_registered'	=> true,
			'user_colour'	=> '',
		);

		// Request
		$this->request = $request = $this->createMock('\phpbb\request\request');

		$request->expects($this->any())
			->method('is_ajax')
			->willReturn(true);

		$user_loader = new \phpbb\user_loader($db, $phpbb_root_path, $phpEx, USERS_TABLE);

		// Container
		$phpbb_container = new ContainerBuilder();

		$loader     = new YamlFileLoader($phpbb_container, new FileLocator(__DIR__ . '/fixtures'));
		$loader->load('services_mention.yml');
		$phpbb_container->set('user_loader', $user_loader);
		$phpbb_container->set('user', $user);
		$phpbb_container->set('language', $lang);
		$phpbb_container->set('config', $config);
		$phpbb_container->set('dbal.conn', $db);
		$phpbb_container->set('auth', $auth);
		$phpbb_container->set('cache.driver', $cache_driver);
		$phpbb_container->set('cache', $cache);
		$phpbb_container->set('group_helper', new \phpbb\group\helper($lang));
		$phpbb_container->set('text_formatter.utils', new \phpbb\textformatter\s9e\utils());
		$phpbb_container->set(
			'text_formatter.s9e.mention_helper',
			new \phpbb\textformatter\s9e\mention_helper(
				$this->db,
				$auth,
				$user,
				$phpbb_root_path,
				$phpEx
			)
		);
		$phpbb_container->setParameter('core.root_path', $phpbb_root_path);
		$phpbb_container->setParameter('core.php_ext', $phpEx);
		$phpbb_container->compile();

		// Mention Sources
		$mention_sources = array('friend', 'group', 'team', 'topic', 'user', 'usergroup');
		$mention_sources_array = array();
		foreach ($mention_sources as $source)
		{
			$class = $phpbb_container->get('mention.source.' . $source);
			$mention_sources_array['mention.source.' . $source] = $class;
		}

		$this->controller = new \phpbb\mention\controller\mention($mention_sources_array, $request, $phpbb_root_path, $phpEx);
	}

	public function handle_data()
	{
		/**
		 * NOTE:
		 * 1) in production comparison with 'myself' is being done in JS
		 * 2) mention_names_limit does not limit the number of returned items
		 */
		return [
			['', 0, [
				[
					'name'     => 'friend',
					'type'     => 'u',
					'id'       => 7,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 1,
				],
				[
					'name'     => 'Group we are a member of',
					'type'     => 'g',
					'id'       => 3,
					'avatar'   => [
						'type' => 'group',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'Normal group',
					'type'     => 'g',
					'id'       => 1,
					'avatar'   => [
						'type' => 'group',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'team_member_normal',
					'type'     => 'u',
					'id'       => 5,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 1,
				],
				[
					'name'     => 'myself',
					'type'     => 'u',
					'id'       => 2,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'poster',
					'type'     => 'u',
					'id'       => 3,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'replier',
					'type'     => 'u',
					'id'       => 4,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'team_member_normal',
					'type'     => 'u',
					'id'       => 5,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'team_member_hidden',
					'type'     => 'u',
					'id'       => 6,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'friend',
					'type'     => 'u',
					'id'       => 7,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test',
					'type'     => 'u',
					'id'       => 8,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test1',
					'type'     => 'u',
					'id'       => 9,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test2',
					'type'     => 'u',
					'id'       => 10,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test3',
					'type'     => 'u',
					'id'       => 11,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'Group we are a member of',
					'type'     => 'g',
					'id'       => 3,
					'avatar'   => [
						'type' => 'group',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 1,
				],
			]],
			['', 1, [
				[
					'name'     => 'friend',
					'type'     => 'u',
					'id'       => 7,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 1,
				],
				[
					'name'     => 'Group we are a member of',
					'type'     => 'g',
					'id'       => 3,
					'avatar'   => [
						'type' => 'group',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'Normal group',
					'type'     => 'g',
					'id'       => 1,
					'avatar'   => [
						'type' => 'group',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'team_member_normal',
					'type'     => 'u',
					'id'       => 5,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 1,
				],
				[
					'name'     => 'replier',
					'type'     => 'u',
					'id'       => 4,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 1,
				],
				[
					'name'     => 'poster',
					'type'     => 'u',
					'id'       => 3,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 5,
				],
				[
					'name'     => 'myself',
					'type'     => 'u',
					'id'       => 2,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'poster',
					'type'     => 'u',
					'id'       => 3,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'replier',
					'type'     => 'u',
					'id'       => 4,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'team_member_normal',
					'type'     => 'u',
					'id'       => 5,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'team_member_hidden',
					'type'     => 'u',
					'id'       => 6,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'friend',
					'type'     => 'u',
					'id'       => 7,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test',
					'type'     => 'u',
					'id'       => 8,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test1',
					'type'     => 'u',
					'id'       => 9,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test2',
					'type'     => 'u',
					'id'       => 10,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test3',
					'type'     => 'u',
					'id'       => 11,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'Group we are a member of',
					'type'     => 'g',
					'id'       => 3,
					'avatar'   => [
						'type' => 'group',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 1,
				],
			]],
			['t', 1, [
				[
					'name'     => 'team_member_normal',
					'type'     => 'u',
					'id'       => 5,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 1,
				],
				[
					'name'     => 'team_member_normal',
					'type'     => 'u',
					'id'       => 5,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'team_member_hidden',
					'type'     => 'u',
					'id'       => 6,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test',
					'type'     => 'u',
					'id'       => 8,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test1',
					'type'     => 'u',
					'id'       => 9,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test2',
					'type'     => 'u',
					'id'       => 10,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test3',
					'type'     => 'u',
					'id'       => 11,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
			]],
			['test', 1, [
				[
					'name'     => 'test',
					'type'     => 'u',
					'id'       => 8,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test1',
					'type'     => 'u',
					'id'       => 9,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test2',
					'type'     => 'u',
					'id'       => 10,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
				[
					'name'     => 'test3',
					'type'     => 'u',
					'id'       => 11,
					'avatar'   => [
						'type' => 'user',
						'img'  => '',
					],
					'rank'     => '',
					'priority' => 0,
				],
			]],
			['test1', 1, [[
				'name'		=> 'test1',
				'type'		=> 'u',
				'id'		=> 9,
				'avatar'	=> [
					'type'	=> 'user',
					'img'	=> '',
				],
				'rank'		=> '',
				'priority'	=> 0,
			]]],
		];
	}

	/**
	* @dataProvider handle_data
	*/
	public function test_handle($keyword, $topic_id, $expected_result)
	{
		$this->request->expects($this->at(1))
			->method('variable')
			->with('keyword', '', true)
			->willReturn($keyword);
		$this->request->expects($this->at(2))
			->method('variable')
			->with('topic_id', 0)
			->willReturn($topic_id);
		$data = json_decode($this->controller->handle()->getContent(), true);
		$this->assertEquals($expected_result, $data);
	}
}
