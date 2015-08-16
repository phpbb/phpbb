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

require_once __DIR__ . '/mocks/test_installer_task_mock.php';
require_once __DIR__ . '/mocks/test_installer_module.php';

class module_base_test extends phpbb_test_case
{
	/**
	 * @var \phpbb\install\module_interface
	 */
	protected $module;

	/**
	 * @var phpbb_mock_container_builder
	 */
	protected $container;

	public function setUp()
	{
		// DI container mock
		$this->container = new phpbb_mock_container_builder();
		$this->container->set('task_one', new test_installer_task_mock());
		$this->container->set('task_two', new test_installer_task_mock());

		// the collection
		$module_collection = new \phpbb\di\ordered_service_collection($this->container);
		$module_collection->add('task_one');
		$module_collection->add('task_two');
		$module_collection->add_service_class('task_one', 'test_installer_task_mock');
		$module_collection->add_service_class('task_two', 'test_installer_task_mock');

		$this->module = new test_installer_module($module_collection, true, false);

		$iohandler = $this->getMock('\phpbb\install\helper\iohandler\iohandler_interface');
		$config = new \phpbb\install\helper\config(new \phpbb\filesystem\filesystem(), new \phpbb\php\ini(), '', 'php');
		$this->module->setup($config, $iohandler);
	}

	public function test_run()
	{
		$this->module->run();

		$task = $this->container->get('task_one');
		$this->assertTrue($task->was_task_runned());

		$task = $this->container->get('task_two');
		$this->assertTrue($task->was_task_runned());
	}

	public function test_step_count()
	{
		$this->assertEquals(4, $this->module->get_step_count());
	}
}
