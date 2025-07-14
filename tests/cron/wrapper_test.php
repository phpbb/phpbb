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

require_once __DIR__ . '/../template/template_test_case.php';

class phpbb_cron_wrapper_test extends phpbb_template_template_test_case
{
	private $task;
	private $routing_helper;
	private $wrapper;

	protected function setUp(): void
	{
		global $phpbb_root_path;

		$this->setup_engine([], $phpbb_root_path . 'styles/all/template');

		global $phpbb_filesystem;

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();

		$this->task = $this->createMock(\phpbb\cron\task\task::class);
		$this->routing_helper = $this->createMock(\phpbb\routing\helper::class);

		$this->wrapper = new \phpbb\cron\task\wrapper(
			$this->task,
			$this->routing_helper,
			$this->template
		);
	}

	public function test_generate_template_pagination()
	{
		$this->task = $this->createMock(\phpbb\cron\task\parametrized::class);
		$this->task->expects($this->any())
			->method('get_parameters')
			->willReturn(['f' => '5']);
		$this->task->expects($this->any())
			->method('get_name')
			->willReturn('test_task');
		$this->routing_helper = $this->createMock(\phpbb\routing\helper::class);
		$this->routing_helper->expects($this->any())
			->method('route')
			->with('phpbb_cron_run', ['cron_type' => 'test_task', 'f' => '5'])
			->willReturn('app.php/cron/foo?f=5');

		$this->wrapper = new \phpbb\cron\task\wrapper(
			$this->task,
			$this->routing_helper,
			$this->template
		);

		$this->assertEquals('<img class="sr-only" aria-hidden="true" src="app.php&#x2F;cron&#x2F;foo&#x3F;f&#x3D;5" width="1" height="1" alt="">', str_replace(["\n", "\t"], '', $this->wrapper->get_html_tag()));
	}

	public function test_is_parametrized_false()
	{
		$this->assertFalse($this->wrapper->is_parametrized());
	}

	public function test_is_ready()
	{
		$this->task->method('is_runnable')->willReturn(true);
		$this->task->method('should_run')->willReturn(true);

		$this->assertTrue($this->wrapper->is_ready());
	}

	public function test_get_url_non_parametrized()
	{
		$this->task->method('get_name')->willReturn('test_task');
		$this->routing_helper->expects($this->once())
			->method('route')
			->with('phpbb_cron_run', ['cron_type' => 'test_task'])
			->willReturn('/cron/url');

		$this->assertEquals('/cron/url', $this->wrapper->get_url());
	}

	public function test_get_html_tag()
	{
		$this->template = $this->createMock(\phpbb\template\template::class);
		$this->wrapper = new \phpbb\cron\task\wrapper(
			$this->task,
			$this->routing_helper,
			$this->template
		);

		$this->template->expects($this->once())
			->method('set_filenames');
		$this->template->expects($this->once())
			->method('assign_var');
		$this->template->expects($this->once())
			->method('assign_display')
			->willReturn('<img src="cron">');

		$this->assertEquals('<img src="cron">', $this->wrapper->get_html_tag());
	}

	public function test_call_forwards_to_task()
	{
		$this->task = $this->getMockBuilder(\phpbb\cron\task\task::class)
			->disableOriginalConstructor()
			->onlyMethods(['get_name', 'run', 'is_runnable', 'should_run'])
			->addMethods(['some_method'])
			->getMock();
		$this->routing_helper = $this->createMock(\phpbb\routing\helper::class);

		$this->wrapper = new \phpbb\cron\task\wrapper(
			$this->task,
			$this->routing_helper,
			$this->template
		);
		$this->task->expects($this->once())
			->method('some_method')
			->with('arg1', 'arg2')
			->willReturn('result');

		$result = $this->wrapper->some_method('arg1', 'arg2');
		$this->assertEquals('result', $result);
	}
}
