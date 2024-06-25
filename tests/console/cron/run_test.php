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
use phpbb\console\command\cron\run;

require_once __DIR__ . '/tasks/simple.php';

class phpbb_console_command_cron_run_test extends phpbb_database_test_case
{
	protected $db;
	protected $config;
	protected $lock;
	protected $user;
	protected $cron_manager;
	protected $task;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	protected function setUp(): void
	{
		global $db, $config, $phpbb_root_path, $phpEx;

		$db = $this->db = $this->new_dbal();
		$config = $this->config = new \phpbb\config\config(array('cron_lock' => '0'));
		$this->lock = new \phpbb\lock\db('cron_lock', $this->config, $this->db);

		$this->user = $this->createMock('\phpbb\user');
		$this->user->method('lang')->will($this->returnArgument(0));

		$this->task = new phpbb_cron_task_simple();
		$tasks = array(
			$this->task,
		);

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
			$phpbb_root_path,
			$phpEx
		);

		$mock_container = new phpbb_mock_container_builder();
		$task_collection = new \phpbb\di\service_collection($mock_container);
		$mock_container->set('cron.task_collection', $task_collection);

		$this->cron_manager = new \phpbb\cron\manager($mock_container, $routing_helper, $phpbb_root_path, $phpEx, null);
		$this->cron_manager->load_tasks($tasks);

		$this->assertSame('0', $config['cron_lock']);
	}

	public function test_normal_use()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute([]);

		$this->assertSame('', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_verbose_mode()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('--verbose' => true));

		$this->assertStringContainsString('RUNNING_TASK', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_error_lock()
	{
		$this->expectException(\phpbb\exception\runtime_exception::class);
		$this->expectExceptionMessage('CRON_LOCK_ERROR');

		$this->lock->acquire();
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute([]);

		$this->assertStringContainsString('CRON_LOCK_ERROR', $command_tester->getDisplay());
		$this->assertSame(false, $this->task->executed);
		$this->assertSame(1, $exit_status);
	}

	public function test_no_task()
	{
		global $phpbb_root_path, $phpEx;

		$tasks = array(
		);

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
			$phpbb_root_path,
			$phpEx
		);

		$mock_container = new phpbb_mock_container_builder();
		$task_collection = new \phpbb\di\service_collection($mock_container);
		$mock_container->set('cron.task_collection', $task_collection);

		$this->cron_manager = new \phpbb\cron\manager($mock_container, $routing_helper, $phpbb_root_path, $phpEx, null);
		$this->cron_manager->load_tasks($tasks);

		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute([]);

		$this->assertSame('', $command_tester->getDisplay());
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_no_task_verbose()
	{
		global $phpbb_root_path, $phpEx;

		$tasks = array(
		);

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
			$phpbb_root_path,
			$phpEx
		);

		$mock_container = new phpbb_mock_container_builder();
		$task_collection = new \phpbb\di\service_collection($mock_container);
		$mock_container->set('cron.task_collection', $task_collection);

		$this->cron_manager = new \phpbb\cron\manager($mock_container, $routing_helper, $phpbb_root_path, $phpEx, null);
		$this->cron_manager->load_tasks($tasks);

		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('--verbose' => true));

		$this->assertStringContainsString('CRON_NO_TASK', $command_tester->getDisplay());
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_arg_valid()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('name' => 'phpbb_cron_task_simple'));

		$this->assertSame('', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_arg_invalid()
	{
		$this->expectException(\phpbb\exception\runtime_exception::class);
		$this->expectExceptionMessage('CRON_NO_SUCH_TASK');

		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('name' => 'foo'));

		$this->assertStringContainsString('CRON_NO_SUCH_TASK', $command_tester->getDisplay());
		$this->assertSame(false, $this->task->executed);
		$this->assertSame(2, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_arg_valid_verbose()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('name' => 'phpbb_cron_task_simple', '--verbose' => true));

		$this->assertStringContainsString('RUNNING_TASK', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new run($this->user, $this->cron_manager, $this->lock));

		$command = $application->find('cron:run');
		return new CommandTester($command);
	}
}
