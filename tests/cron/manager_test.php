<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once __DIR__ . '/../mock/cache.php';
require_once __DIR__ . '/task/testmod/dummy_task.php';
require_once __DIR__ . '/task/testmod/second_dummy_task.php';

class phpbb_cron_manager_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->manager = new phpbb_cron_manager(__DIR__ . '/task/', 'php');
		$this->task_name = 'phpbb_cron_task_testmod_dummy_task';
	}

	public function test_manager_finds_shipped_tasks()
	{
		$tasks = $this->manager->find_cron_task_names();
		$this->assertEquals(2, sizeof($tasks));
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
		$this->assertEquals(2, sizeof($tasks));
	}

	public function test_manager_finds_one_ready_task()
	{
		$task = $this->manager->find_one_ready_task();
		$this->assertInstanceOf('phpbb_cron_task_wrapper', $task);
	}

	public function test_manager_finds_all_ready_tasks_cached()
	{
		$cache = new phpbb_mock_cache(array('_cron_tasks' => array($this->task_name)));
		$manager = new phpbb_cron_manager(__DIR__ . '/../../phpBB/', 'php', $cache);

		$tasks = $manager->find_all_ready_tasks();
		$this->assertEquals(1, sizeof($tasks));
	}
}
