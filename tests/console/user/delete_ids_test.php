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
use phpbb\console\command\user\delete_id;

require_once __DIR__ . '/base.php';

class phpbb_console_user_delete_ids_test extends phpbb_console_user_base
{
	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new delete_id(
			$this->db,
			$this->language,
			$this->log,
			$this->user,
			$this->user_loader,
			BOTS_TABLE,
			USER_GROUP_TABLE,
			USERS_TABLE,
			$this->phpbb_root_path,
			$this->php_ext
		));

		$command = $application->find('user:delete_id');
		$this->command_name = $command->getName();
		$this->question = $command->getHelper('question');

		return new CommandTester($command);
	}

	public function test_delete()
	{
		$command_tester = $this->get_command_tester();

		$command_tester->setInputs(['yes', '']);

		$command_tester->execute(array(
			'command'			=> $this->command_name,
			'user_ids'			=> [3, 4],
			'--delete-posts'	=> false,
		));

		$this->assertNull($this->get_user_id('Test'));
		$this->assertNull($this->get_user_id('Test 2'));
		$this->assertStringContainsString('CLI_USER_DELETE_ID_SUCCESS', $command_tester->getDisplay());
	}

	public function test_delete_one()
	{
		$command_tester = $this->get_command_tester();

		$command_tester->setInputs(['yes', '']);

		$command_tester->execute(array(
			'command'			=> $this->command_name,
			'user_ids'			=> [3],
			'--delete-posts'	=> false,
		));

		$this->assertNull($this->get_user_id('Test'));
		$this->assertNotNull($this->get_user_id('Test 2'));
		$this->assertStringContainsString('CLI_USER_DELETE_ID_SUCCESS', $command_tester->getDisplay());
	}

	public function test_delete_bot()
	{
		$command_tester = $this->get_command_tester();

		$this->assertNotNull($this->get_user_id('Test Bot'));

		$command_tester->setInputs(['yes', '']);

		$command_tester->execute(array(
			'command'			=> $this->command_name,
			'user_ids'			=> [6],
			'--delete-posts'	=> false,
		));

		$this->assertNull($this->get_user_id('Test Bot'));
		$this->assertStringContainsString('CLI_USER_DELETE_ID_SUCCESS', $command_tester->getDisplay());
	}

	public function test_delete_non_user()
	{
		$command_tester = $this->get_command_tester();

		$command_tester->setInputs(['yes', '']);

		$command_tester->execute(array(
			'command'			=> $this->command_name,
			'user_ids'			=> [999],
			'--delete-posts'	=> false,
		));

		$this->assertStringContainsString('CLI_USER_DELETE_NONE', $command_tester->getDisplay());
	}

	public function test_delete_cancel()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(3, $this->get_user_id('Test'));

		$command_tester->setInputs(['no', '']);

		$command_tester->execute(array(
			'command'			=> $this->command_name,
			'user_ids'			=> [3, 4],
			'--delete-posts'	=> false,
		));

		$this->assertNotNull($this->get_user_id('Test'));
		$this->assertNotNull($this->get_user_id('Test 2'));
	}
}
