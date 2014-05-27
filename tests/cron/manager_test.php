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

require_once dirname(__FILE__) . '/includes/cron/task/core/dummy_task.php';
require_once dirname(__FILE__) . '/includes/cron/task/core/second_dummy_task.php';
require_once dirname(__FILE__) . '/ext/testext/cron/dummy_task.php';
require_once dirname(__FILE__) . '/tasks/simple_ready.php';
require_once dirname(__FILE__) . '/tasks/simple_not_runnable.php';
require_once dirname(__FILE__) . '/tasks/simple_should_not_run.php';

class phpbb_cron_manager_test extends \phpbb_test_case
{
	public function setUp()
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
		$this->assertEquals(3, sizeof($tasks));
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

		return new \phpbb\cron\manager($tasks, $phpbb_root_path, $phpEx);
	}
}
