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

	public function setUp()
	{
		$phpbb_root_path = __DIR__ . './../../phpBB/';
		$filesystem = $this->getMock('\phpbb\filesystem\filesystem');
		$php_ini = $this->getMockBuilder('\phpbb\php\ini')
			->getMock();
		$php_ini->method('get_int')
			->willReturn(-1);
		$php_ini->method('get_bytes')
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
		$this->config->set_finished_task('foo');
		$this->config->set_active_module('bar');
		$this->config->set_task_progress_count(10);
		$this->config->increment_current_task_progress();

		$progress_data = $this->config->get_progress_data();
		$this->assertEquals(1, $progress_data['current_task_progress']);

		$this->config->increment_current_task_progress(2);

		$this->assertEquals(array(
				'last_task_module_name'		=> 'bar',
				'last_task_name'			=> 'foo',
				'max_task_progress'			=> 10,
				'current_task_progress'		=> 3,
			),
			$this->config->get_progress_data()
		);
	}
}
