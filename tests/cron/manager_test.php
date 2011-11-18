<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/extension_manager.php';
require_once dirname(__FILE__) . '/includes/cron/task/core/dummy_task.php';
require_once dirname(__FILE__) . '/includes/cron/task/core/second_dummy_task.php';
require_once dirname(__FILE__) . '/ext/testext/cron/dummy_task.php';
require_once dirname(__FILE__) . '/tasks/simple_ready.php';
require_once dirname(__FILE__) . '/tasks/simple_not_runnable.php';
require_once dirname(__FILE__) . '/tasks/simple_should_not_run.php';

class phpbb_cron_manager_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->manager = new phpbb_cron_manager(array(
			'phpbb_cron_task_core_dummy_task',
			'phpbb_cron_task_core_second_dummy_task',
			'phpbb_ext_testext_cron_dummy_task',
		));
		$this->task_name = 'phpbb_cron_task_core_dummy_task';
	}

	public function test_manager_finds_shipped_task_by_name()
	{
		$task = $this->manager->find_task($this->task_name);
		$this->assertInstanceOf('phpbb_cron_task_wrapper', $task);
		$this->assertEquals($this->task_name, $task->get_name());
	}

	public function test_manager_instantiates_task_by_name()
	{
		$task = $this->manager->instantiate_task($this->task_name, array());
		$this->assertInstanceOf('phpbb_cron_task_wrapper', $task);
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
		$this->assertInstanceOf('phpbb_cron_task_wrapper', $task);
	}

	public function test_manager_finds_only_ready_tasks()
	{
		$manager = new phpbb_cron_manager(array(
			'phpbb_cron_task_core_simple_ready',
			'phpbb_cron_task_core_simple_not_runnable',
			'phpbb_cron_task_core_simple_should_not_run',
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
			$names[] = get_class($task->task);
		}
		return $names;
	}
}
