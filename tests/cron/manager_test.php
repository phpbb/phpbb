<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_cron_manager_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->manager = new phpbb_cron_manager(__DIR__ . '/../../phpBB/', 'php');
	}

	public function test_manager_finds_shipped_tasks()
	{
		$tasks = $this->manager->find_cron_task_names();
		$this->assertGreaterThan(1, count($tasks));
	}

	public function test_manager_finds_shipped_task_by_name()
	{
		$task = $this->manager->find_task('phpbb_cron_task_core_queue');
		$this->assertNotNull($task);
	}

	public function test_manager_instantiates_task_by_name()
	{
		$task = $this->manager->instantiate_task('phpbb_cron_task_core_queue', array());
		$this->assertNotNull($task);
	}

	public function test_manager_finds_all_ready_tasks()
	{
		$tasks = $this->manager->find_all_ready_tasks();
		$this->assertGreaterThan(0, count($tasks));
	}

	public function test_manager_finds_one_ready_task()
	{
		$task = $this->manager->find_one_ready_task();
		$this->assertNotNull($task);
	}
}
