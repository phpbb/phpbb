<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/extension_manager.php';

class phpbb_cron_provider_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'testext' => array(
					'ext_name'      => 'testext',
					'ext_active'    => true,
					'ext_path'      => dirname(__FILE__) . '/ext/testext/'
				),
			));
		$this->provider = new phpbb_cron_provider($this->extension_manager);
	}

	public function test_manager_finds_shipped_tasks()
	{
		$task_iterator = $this->provider->find_cron_task_names();

		$tasks = array();
		foreach ($task_iterator as $task)
		{
			$tasks[] = $task;
		}
		sort($tasks);

		$this->assertEquals(array(
			'phpbb_cron_task_core_dummy_task',
			'phpbb_cron_task_core_second_dummy_task',
			'phpbb_ext_testext_cron_dummy_task',
		), $tasks);
	}
}
