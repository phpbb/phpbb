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

use phpbb\config\config;
use phpbb\cron\task\core\version_check;

require_once __DIR__ . '/../../phpBB/includes/functions.php';

class phpbb_cron_task_core_version_check_test extends phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject|config */
	protected $config;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\version_helper */
	protected $version_helper;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\log\log_interface */
	protected $log;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\user */
	protected $user;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\notification\manager */
	protected $notification_manager;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new config(array(
			'version'	=> '3.1.0',
			'version_check_interval' => 86400,
			'version_check_last_cron' => 0,
		));

		$this->version_helper = $this->getMockBuilder('\phpbb\version_helper')
			->disableOriginalConstructor()
			->getMock();

		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();

		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()
			->getMock();

		$this->notification_manager = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();
	}

	protected function get_task(): version_check
	{
		return new version_check(
			$this->config,
			$this->version_helper,
			$this->log,
			$this->user,
			$this->notification_manager
		);
	}

	public function test_get_name()
	{
		$task = $this->get_task();
		$this->assertNull($task->get_name());

		$task->set_name('cron.task.core.version_check');
		$this->assertSame('cron.task.core.version_check', $task->get_name());
	}

	public function test_is_runnable()
	{
		$task = $this->get_task();
		$this->assertTrue($task->is_runnable());
	}

	public function should_run_data()
	{
		return [
			'never_run_before' => [0, 1440, true],
			'just_run' => [time(), 1440, false],
			'interval_elapsed' => [time() - 86401, 1440, true],
			'between_intervals' => [time() - (1440 / 2 * 60), 1440, false],
			'custom_interval' => [time() - 121, 2, true],
		];
	}

	/**
	 * @dataProvider should_run_data
	 */
	public function test_should_run($last_cron, $interval, $expected)
	{
		$this->config['version_check_last_cron'] = $last_cron;
		$this->config['version_check_interval'] = $interval;

		$task = $this->get_task();
		$this->assertSame($expected, $task->should_run());
	}

	public function test_run_with_updates()
	{
		$update_data = array(
			'current' => '3.1.1',
			'announcement' => 'https://www.phpbb.com/announcement',
			'download' => 'https://www.phpbb.com/download',
		);

		$this->version_helper->expects($this->once())
			->method('get_update_on_branch')
			->with($this->identicalTo(true))
			->willReturn($update_data);

		$this->notification_manager->expects($this->once())
			->method('add_notifications')
			->with(
				$this->equalTo('notification.type.update_maintenance'),
				$this->callback(function ($type_data) use ($update_data) {
					$this->assertEquals(16144929, $type_data['item_id']);
					$this->assertEquals('update_maintenance', $type_data['template']);
					$this->assertEquals('3.1.0', $type_data['current_version']);
					$this->assertEquals($update_data['current'], $type_data['new_version']);
					$this->assertEquals($update_data['announcement'], $type_data['announcement']);
					$this->assertEquals($update_data['download'], $type_data['download']);
					return true;
				})
			);

		$task = $this->get_task();
		$task->run();

		$this->assertGreaterThan(0, $this->config['version_check_last_cron']);
	}

	public function test_run_without_updates()
	{
		$this->version_helper->expects($this->once())
			->method('get_update_on_branch')
			->with($this->identicalTo(true))
			->willReturn([]);

		$this->notification_manager->expects($this->never())
			->method('add_notifications');

		$task = $this->get_task();
		$task->run();

		$this->assertGreaterThan(0, $this->config['version_check_last_cron']);
	}

	public function test_run_with_exception()
	{
		$this->version_helper->expects($this->once())
			->method('get_update_on_branch')
			->with($this->identicalTo(true))
			->willThrowException(new \phpbb\exception\runtime_exception('VERSION_CHECK_FAIL'));

		$this->notification_manager->expects($this->never())
			->method('add_notifications');

		$start_time = time();
		$task = $this->get_task();
		$task->run();

		$this->assertGreaterThanOrEqual($start_time, $this->config['version_check_last_cron']);
	}

	public function test_run_with_critical_update()
	{
		$update_data = array(
			'current' => '3.1.1',
			'critical' => '3.1.2',
			'announcement' => 'https://www.phpbb.com/announcement',
			'download' => 'https://www.phpbb.com/download',
		);

		$this->version_helper->expects($this->once())
			->method('get_update_on_branch')
			->with($this->identicalTo(true))
			->willReturn($update_data);

		$this->version_helper->expects($this->atLeastOnce())
			->method('compare')
			->with(
				$this->equalTo('3.1.2'),
				$this->equalTo('3.1.0'),
				$this->equalTo('>')
			)
			->willReturn(true);

		$this->notification_manager->expects($this->once())
			->method('add_notifications')
			->with(
				$this->equalTo('notification.type.update_maintenance'),
				$this->callback(function ($type_data) {
					$this->assertEquals('update_critical', $type_data['template']);
					return true;
				})
			);

		$task = $this->get_task();
		$task->run();
	}

	public function test_run_with_security_update()
	{
		$update_data = array(
			'current' => '3.1.1',
			'security' => '3.1.1',
			'announcement' => 'https://www.phpbb.com/announcement',
			'download' => 'https://www.phpbb.com/download',
		);

		$this->version_helper->expects($this->once())
			->method('get_update_on_branch')
			->with($this->identicalTo(true))
			->willReturn($update_data);

		$this->version_helper->expects($this->once())
			->method('compare')
			->with(
				$this->equalTo('3.1.1'),
				$this->equalTo('3.1.0'),
				$this->equalTo('>')
			)
			->willReturn(true);

		$this->notification_manager->expects($this->once())
			->method('add_notifications')
			->with(
				$this->equalTo('notification.type.update_maintenance'),
				$this->callback(function ($type_data) {
					$this->assertEquals('update_security', $type_data['template']);
					return true;
				})
			);

		$task = $this->get_task();
		$task->run();
	}

	public function test_run_with_maintenance_update()
	{
		$update_data = array(
			'current' => '3.1.1',
			'announcement' => 'https://www.phpbb.com/announcement',
			'download' => 'https://www.phpbb.com/download',
		);

		$this->version_helper->expects($this->once())
			->method('get_update_on_branch')
			->with($this->identicalTo(true))
			->willReturn($update_data);

		$this->notification_manager->expects($this->once())
			->method('add_notifications')
			->with(
				$this->equalTo('notification.type.update_maintenance'),
				$this->callback(function ($type_data) {
					$this->assertEquals('update_maintenance', $type_data['template']);
					return true;
				})
			);

		$task = $this->get_task();
		$task->run();
	}

	public function test_notify_admins_with_empty_current_version()
	{
		$update_data = array(
			'announcement' => 'https://www.phpbb.com/announcement',
			'download' => 'https://www.phpbb.com/download',
		);

		$this->notification_manager->expects($this->never())
			->method('add_notifications');

		$task = $this->get_task();
		$notify_admins_method = new ReflectionMethod($task, 'notify_admins');
		$notify_admins_method->setAccessible(true);
		$notify_admins_method->invoke($task, $update_data);
	}
}
