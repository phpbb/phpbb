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

require_once dirname(__FILE__) . '/base.php';

class phpbb_console_user_add_test extends phpbb_console_user_base
{
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
}
