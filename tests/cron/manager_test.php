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

require_once __DIR__ . '/includes/cron/task/core/dummy_task.php';
require_once __DIR__ . '/includes/cron/task/core/second_dummy_task.php';
require_once __DIR__ . '/ext/testext/cron/dummy_task.php';
require_once __DIR__ . '/tasks/simple_ready.php';
require_once __DIR__ . '/tasks/simple_not_runnable.php';
require_once __DIR__ . '/tasks/simple_should_not_run.php';

class phpbb_cron_manager_test extends \phpbb_test_case
{
	protected $manager;
	protected $task_name;

	protected function setUp(): void
	{
		$this->manager = $this->create_cron_manager(array(
			new phpbb_cron_task_core_dummy_task(),
			new phpbb_cron_task_core_second_dummy_task(),
			new phpbb_ext_testext_cron_dummy_task(),
		));
		$this->task_name = 'phpbb_cron_task_core_dummy_task';
	}

	public function test_manager_finds_shipped_task_by_name()
	{
		$task = $this->manager->find_task($this->task_name);
		$this->assertInstanceOf('\phpbb\cron\task\wrapper', $task);
		$this->assertEquals($this->task_name, $task->get_name());
	}

	public function test_manager_finds_all_ready_tasks()
	{
		$tasks = $this->manager->find_all_ready_tasks();
		$this->assertEquals(3, count($tasks));
	}

	public function test_manager_finds_one_ready_task()
	{
		$task = $this->manager->find_one_ready_task();
		$this->assertInstanceOf('\phpbb\cron\task\wrapper', $task);
	}

	public function test_manager_finds_only_ready_tasks()
	{
		$manager = $this->create_cron_manager(array(
			new phpbb_cron_task_core_simple_ready(),
			new phpbb_cron_task_core_simple_not_runnable(),
			new phpbb_cron_task_core_simple_should_not_run(),
		));
		$tasks = $manager->find_all_ready_tasks();
		$task_names = $this->tasks_to_names($tasks);
		$this->assertEquals(array('phpbb_cron_task_core_simple_ready'), $task_names);
	}

	private function tasks_to_names($tasks)
	{
		$names = array();
		foreach ($tasks as $task)
		{
			$names[] = $task->get_name();
		}
		return $names;
	}

	private function create_cron_manager($tasks)
	{
		global $phpbb_root_path, $phpEx;

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

		$cron_manager = new \phpbb\cron\manager($mock_container, $routing_helper, $phpbb_root_path, $phpEx, null);
		$cron_manager->load_tasks($tasks);

		return $cron_manager;
	}
}
