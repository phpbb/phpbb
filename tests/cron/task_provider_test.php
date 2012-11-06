<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_cron_task_provider_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->tasks = array(
			'phpbb_cron_task_core_dummy_task',
			'phpbb_cron_task_core_second_dummy_task',
			'phpbb_ext_testext_cron_dummy_task',
		);

		$container = $this->getMock('Symfony\Component\DependencyInjection\TaggedContainerInterface');
		$container
			->expects($this->once())
			->method('findTaggedServiceIds')
			->will($this->returnValue(array_flip($this->tasks)));
		$container
			->expects($this->any())
			->method('get')
			->will($this->returnCallback(function ($name) {
				return new $name;
			}));

		$this->provider = new phpbb_cron_task_provider($container);
	}

	public function test_manager_finds_shipped_tasks()
	{
		$task_names = array();
		foreach ($this->provider as $task)
		{
			$task_names[] = $task->get_name();
		}
		sort($task_names);

		$this->assertEquals(array(
			'phpbb_cron_task_core_dummy_task',
			'phpbb_cron_task_core_second_dummy_task',
			'phpbb_ext_testext_cron_dummy_task',
		), $task_names);
	}
}
