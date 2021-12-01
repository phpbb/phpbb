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

require_once __DIR__ . '/base.php';

class phpbb_console_user_delete_test extends phpbb_console_user_base
{
	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new delete(
			$this->user,
			$this->language,
			$this->log,
			$this->user_loader,
			$this->phpbb_root_path,
			$this->php_ext
		));

		$command = $application->find('user:delete');
		$this->question = $command->getHelper('question');

		return new CommandTester($command);
	}

	public function test_delete()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(3, $this->get_user_id('Test'));

		$command_tester->setInputs(['yes', '']);

		$command_tester->execute(array(
			'username'			=> 'Test',
			'--delete-posts'	=> false,
		));

		$this->assertNull($this->get_user_id('Test'));
		$this->assertStringContainsString('USER_DELETED', $command_tester->getDisplay());
	}

	public function test_delete_non_user()
	{
		$command_tester = $this->get_command_tester();

		$this->assertNull($this->get_user_id('Foo'));

		$command_tester->setInputs(['yes', '']);

		$command_tester->execute(array(
			'username'			=> 'Foo',
			'--delete-posts'	=> false,
		));

		$this->assertStringContainsString('NO_USER', $command_tester->getDisplay());
	}

	public function test_delete_cancel()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(3, $this->get_user_id('Test'));

		$command_tester->setInputs(['no', '']);

		$command_tester->execute(array(
			'username'			=> 'Test',
			'--delete-posts'	=> false,
		));

		$this->assertNotNull($this->get_user_id('Test'));
	}
}
