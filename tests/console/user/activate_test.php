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

require_once __DIR__ . '/base.php';

class phpbb_console_user_activate_test extends phpbb_console_user_base
{
	protected $notifications;

	protected function setUp(): void
	{
		parent::setUp();

		$this->notifications = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new activate(
			$this->user,
			$this->config,
			$this->language,
			$this->log,
			$this->notifications,
			$this->user_loader,
			$this->phpbb_root_path,
			$this->php_ext
		));

		$command = $application->find('user:activate');

		return new CommandTester($command);
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
			'username'		=> $username,
			'--deactivate'	=> $deactivate,
		));

		$this->assertStringContainsString($expected, $command_tester->getDisplay());
	}
}
