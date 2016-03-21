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
use phpbb\console\command\user\add;

require_once dirname(__FILE__) . '/../../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/../../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../../phpBB/includes/utf/utf_tools.php';

class phpbb_console_command_user_add_test extends phpbb_database_test_case
{
	protected $db;
	protected $config;
	protected $user;
	protected $language;
	protected $passwords_manager;
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

		$config = $this->config = new \phpbb\config\config(array(
			'board_timezone'	=> 'UTC',
			'default_lang'		=> 'en',
			'email_enable'		=> false,
			'min_name_chars'	=> 3,
			'max_name_chars'	=> 10,
			'min_pass_chars'	=> 3,
			'max_pass_chars'	=> 10,
			'pass_complex'		=> 'PASS_TYPE_ANY',
		));

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

		$driver_helper = new \phpbb\passwords\driver\helper($this->config);
		$passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($this->config, $driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($this->config, $driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($this->config, $driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($this->config, $driver_helper),
		);

		$passwords_helper = new \phpbb\passwords\helper;
		$this->passwords_manager = new \phpbb\passwords\manager($this->config, $passwords_drivers, $passwords_helper, array_keys($passwords_drivers));

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		parent::setUp();
	}

	public function test_add_no_dialog()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(2, $this->get_user_id('Admin'));

		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'--username'	=> 'foo',
			'--password'	=> 'bar',
			'--email'		=> 'foo@test.com'
		));

		$this->assertNotEquals(null, $this->get_user_id('foo'));
		$this->assertContains('CLI_USER_ADD_SUCCESS', $command_tester->getDisplay());
	}

	public function test_add_dialog()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(2, $this->get_user_id('Admin'));

		$this->question->setInputStream($this->getInputStream("bar\npassword\npassword\nbar@test.com\n"));

		$command_tester->execute(array(
			'command'		=> $this->command_name,
		));

		$this->assertNotEquals(null, $this->get_user_id('bar'));
		$this->assertContains('CLI_USER_ADD_SUCCESS', $command_tester->getDisplay());

	}

	public function test_add_no_dialog_invalid()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(3, $this->get_user_id('Test'));

		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'--username'	=> 'Test',
			'--password'	=> '1',
			'--email'		=> 'foo'
		));

		$this->assertContains('USERNAME_TAKEN', $command_tester->getDisplay());
		$this->assertContains('TOO_SHORT', $command_tester->getDisplay());
		$this->assertContains('EMAIL_INVALID', $command_tester->getDisplay());
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new add(
			$this->user,
			$this->db,
			$this->config,
			$this->language,
			$this->passwords_manager,
			$this->phpbb_root_path,
			$this->php_ext
		));

		$command = $application->find('user:add');
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
