<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);

require_once 'test_framework/framework.php';

// need the class loader since cron does not include/require cron task files
require($phpbb_root_path . 'includes/class_loader.' . $phpEx);
// do not use cache
$class_loader = new phpbb_class_loader($phpbb_root_path, '.' . $phpEx);
$class_loader->register();

require_once '../phpBB/includes/cron/manager.php';

class phpbb_cron_manager_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->manager = new phpbb_cron_manager();
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
	
	public function test_manager_finds_one_ready_task() {
		$task = $this->manager->find_one_ready_task();
		$this->assertNotNull($task);
	}
}
