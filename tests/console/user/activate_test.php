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
use phpbb\console\command\user\activate;

class phpbb_console_command_user_activate_test extends phpbb_database_test_case
{
	protected $db;
	protected $config;
	protected $user;
	protected $language;
	protected $log;
	protected $notifications;
	protected $command_name;
	protected $phpbb_root_path;
	protected $php_ext;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	public function setUp()
	{
		global $config, $db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$auth = $this->getMock('\phpbb\auth\auth');

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$config = $this->config = new \phpbb\config\config(array());

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

		$this->notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		parent::setUp();
	}

	public function activate_test_data()
	{
		return array(
			// Test an inactive user
			array('Test', false, 'USER_ADMIN_ACTIVATED'),
			array('Test', true, 'CLI_DESCRIPTION_USER_ACTIVATE_INACTIVE'),

			// Test an active user
			array('Test 2', false, 'CLI_DESCRIPTION_USER_ACTIVATE_ACTIVE'),
			array('Test 2', true, 'USER_ADMIN_DEACTIVED'),

			// Test a non existent user
			array('Foo', false, 'NO_USER'),
			array('Foo', true, 'NO_USER'),
		);
	}

	/**
	 * @dataProvider activate_test_data
	 */
	public function test_activate($username, $deactivate, $expected)
	{
		$command_tester = $this->get_command_tester();

		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'username'		=> $username,
			'--deactivate'	=> $deactivate,
		));

		$this->assertContains($expected, $command_tester->getDisplay());
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new activate(
			$this->user,
			$this->db,
			$this->config,
			$this->language,
			$this->log,
			$this->notifications,
			$this->phpbb_root_path,
			$this->php_ext
		));

		$command = $application->find('user:activate');
		$this->command_name = $command->getName();

		return new CommandTester($command);
	}
}
