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

use phpbb\install\helper\config;

class phpbb_installer_config_test extends phpbb_test_case
{
	/**
	 * @var \phpbb\install\helper\config
	 */
	private $config;

	protected function setUp(): void
	{
		$phpbb_root_path = __DIR__ . './../../phpBB/';
		$filesystem = $this->createMock('\phpbb\filesystem\filesystem');
		$php_ini = $this->getMockBuilder('\bantu\IniGetWrapper\IniGetWrapper')
			->setMethods(array('getInt', 'getBytes'))
			->getMock();
		$php_ini->method('getInt')
			->willReturn(-1);
		$php_ini->method('getBytes')
			->willReturn(-1);

		$this->config = new config($filesystem, $php_ini, $phpbb_root_path);
	}

	public function test_set_get_var()
	{
		$this->config->set('foo', 'bar');
		$this->assertEquals('bar', $this->config->get('foo'));
	}

	public function test_get_time_remaining()
	{
		$this->assertGreaterThan(0, $this->config->get_time_remaining());
	}

	public function test_get_memory_remaining()
	{
		$this->assertGreaterThan(0, $this->config->get_memory_remaining());
	}

	public function test_progress_tracking()
	{
		$this->config->set_finished_task(0);
		$this->config->set_active_module('bar', 5);
		$this->config->set_task_progress_count(10);
		$this->config->increment_current_task_progress();

		$progress_data = $this->config->get_progress_data();
		$this->assertEquals(1, $progress_data['current_task_progress']);

		$this->config->increment_current_task_progress(2);

		// We only want to check these values
		$result = $this->config->get_progress_data();
		$expected_result = array(
			'last_task_module_name'		=> 'bar',
			'last_task_module_index'	=> 5,
			'last_task_index'			=> 0,
			'max_task_progress'			=> 10,
			'current_task_progress'		=> 3,
		);

		foreach ($expected_result as $key => $value)
		{
			$this->assertEquals($value, $result[$key]);
		}
	}
}
