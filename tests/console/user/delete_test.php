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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\user\delete;

class phpbb_console_command_user_delete_test extends phpbb_database_test_case
{
	protected $db;
	protected $user;
	protected $language;
	protected $log;
	protected $command_name;
	protected $question;
	protected $phpbb_root_path;
	protected $php_ext;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	public function setUp()
	{
		global $db, $cache, $config, $user, $phpbb_dispatcher, $phpbb_container, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', new phpbb_mock_cache());
		$phpbb_container->set('notification_manager', new phpbb_mock_notification_manager());

		$cache = $phpbb_container->get('cache.driver');

		$config = new \phpbb\config\config(array());

		$db = $this->db = $this->new_dbal();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('lang')
			->will($this->returnArgument(0));
		$user = $this->user = $this->getMock('\phpbb\user', array(), array(
			$this->language,
			'\phpbb\datetime'
		));

		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();

		$phpbb_container->set('auth.provider.db', new phpbb_mock_auth_provider());
		$provider_collection = new \phpbb\auth\provider_collection($phpbb_container, $config);
		$provider_collection->add('auth.provider.db');
		$phpbb_container->set(
			'auth.provider_collection',
			$provider_collection
		);

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		parent::setUp();
	}

	public function test_delete()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(3, $this->get_user_id('Test'));

		$this->question->setInputStream($this->getInputStream("yes\n"));

		$command_tester->execute(array(
			'command'			=> $this->command_name,
			'username'			=> 'Test',
			'--delete-posts'	=> false,
		));

		$this->assertNull($this->get_user_id('Test'));
		$this->assertContains('USER_DELETED', $command_tester->getDisplay());
	}

	public function test_delete_non_user()
	{
		$command_tester = $this->get_command_tester();

		$this->assertNull($this->get_user_id('Foo'));

		$this->question->setInputStream($this->getInputStream("yes\n"));

		$command_tester->execute(array(
			'command'			=> $this->command_name,
			'username'			=> 'Foo',
			'--delete-posts'	=> false,
		));

		$this->assertContains('NO_USER', $command_tester->getDisplay());
	}

	public function test_delete_cancel()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(3, $this->get_user_id('Test'));

		$this->question->setInputStream($this->getInputStream("no\n"));

		$command_tester->execute(array(
			'command'			=> $this->command_name,
			'username'			=> 'Test',
			'--delete-posts'	=> false,
		));

		$this->assertNotNull($this->get_user_id('Test'));
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new delete(
			$this->user,
			$this->db,
			$this->language,
			$this->log,
			$this->phpbb_root_path,
			$this->php_ext
		));

		$command = $application->find('user:delete');
		$this->command_name = $command->getName();
		$this->question = $command->getHelper('question');

		return new CommandTester($command);
	}

	public function get_user_id($username)
	{
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE ' . 'username = ' . "'" . $username . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row['user_id'];
	}

	public function getInputStream($input)
	{
		$stream = fopen('php://memory', 'r+', false);
		fputs($stream, $input);
		rewind($stream);

		return $stream;
	}
}
