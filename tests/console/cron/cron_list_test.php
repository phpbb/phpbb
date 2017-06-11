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

require_once dirname(__FILE__) . '/tasks/simple_ready.php';
require_once dirname(__FILE__) . '/tasks/simple_not_ready.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\cron\cron_list;

class phpbb_console_command_cron_list_test extends phpbb_test_case
{
	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/** @var \phpbb\user */
	protected $user;

	protected $command_name;

	protected $command_tester;

	protected function setUp()
	{
		global $phpbb_root_path, $phpEx;

		$this->user = $this->createMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		));
		$this->user->method('lang')->will($this->returnArgument(0));
	}

	public function test_no_task()
	{
		$this->initiate_test(0, 0);
		$this->assertContains('CRON_NO_TASKS', $this->command_tester->getDisplay());
	}

	public function test_only_ready()
	{
		$this->initiate_test(2, 0);
		$this->assertContains('TASKS_READY command1 command2', preg_replace('/[\s*=]+/', ' ', trim($this->command_tester->getDisplay())));
	}

	public function test_only_not_ready()
	{
		$this->initiate_test(0, 2);
		$this->assertContains('TASKS_NOT_READY command1 command2', preg_replace('/[\s*=]+/', ' ', trim($this->command_tester->getDisplay())));
	}

	public function test_both_ready()
	{
		$this->initiate_test(2, 2);
		$this->assertSame('TASKS_READY command1 command2 TASKS_NOT_READY command3 command4', preg_replace('/[\s*=]+/', ' ', trim($this->command_tester->getDisplay())));
	}

	public function get_cron_manager(array $tasks)
	{
		global $pathEx, $phpbb_root_path;
		$i = 1;
		foreach ($tasks as $task)
		{
			$task->set_name('command' . $i);
			$i++;
		}

		$mock_config = new \phpbb\config\config(array(
			'force_server_vars' => false,
			'enable_mod_rewrite' => '',
		));

		$mock_router = $this->getMockBuilder('\phpbb\routing\router')
			->setMethods(array('setContext', 'generate'))
			->disableOriginalConstructor()
			->getMock();
		$mock_router->method('setContext')
			->willReturn(true);
		$mock_router->method('generate')
			->willReturn('foobar');

		$request = new \phpbb\request\request();
		$request->enable_super_globals();

		$routing_helper = new \phpbb\routing\helper(
			$mock_config,
			$mock_router,
			new \phpbb\symfony_request($request),
			$request,
			new \phpbb\filesystem\filesystem(),
			$phpbb_root_path,
			$pathEx
		);

		$this->cron_manager = new \phpbb\cron\manager($tasks, $routing_helper, $phpbb_root_path, $pathEx);
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new cron_list($this->user, $this->cron_manager));

		$command = $application->find('cron:list');
		$this->command_name = $command->getName();
		return new CommandTester($command);
	}

	public function initiate_test($number_ready, $number_not_ready)
	{
		$tasks = array();

		for ($i = 0; $i < $number_ready; $i++)
		{
			$tasks[] =  new phpbb_cron_task_simple_ready();
		}

		for ($i = 0; $i < $number_not_ready; $i++)
		{
			$tasks[] = new phpbb_cron_task_simple_not_ready();
		}

		$this->get_cron_manager($tasks);
		$this->command_tester = $this->get_command_tester();
		$this->command_tester->execute(array('command' => $this->command_name), array('decorated' => false));
	}
}
