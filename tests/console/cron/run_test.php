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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\cron\run;

require_once dirname(__FILE__) . '/tasks/simple.php';
require_once dirname(__FILE__) . '/../../../phpBB/includes/functions.php';

class phpbb_console_command_cron_run_test extends phpbb_database_test_case
{
	protected $db;
	protected $config;
	protected $lock;
	protected $user;
	protected $cron_manager;
	protected $command_name;
	protected $task;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	public function setUp()
	{
		global $db, $config, $phpbb_root_path, $phpEx;

		$db = $this->db = $this->new_dbal();
		$config = $this->config = new \phpbb\config\config(array('cron_lock' => '0'));
		$this->lock = new \phpbb\lock\db('cron_lock', $this->config, $this->db);

		$this->user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		));
		$this->user->method('lang')->will($this->returnArgument(0));

		$this->task = new phpbb_cron_task_simple();
		$tasks = array(
			$this->task,
		);
		$this->cron_manager = new \phpbb\cron\manager($tasks, $phpbb_root_path, $phbEx);

		$this->assertSame('0', $config['cron_lock']);
	}

	public function test_normal_use()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name));

		$this->assertSame('', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_verbose_mode()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name, '--verbose' => true));

		$this->assertContains('RUNNING_TASK', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_error_lock()
	{
		$this->lock->acquire();
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name));

		$this->assertContains('CRON_LOCK_ERROR', $command_tester->getDisplay());
		$this->assertSame(false, $this->task->executed);
		$this->assertSame(1, $exit_status);
	}

	public function test_no_task()
	{
		$tasks = array(
		);
		$this->cron_manager = new \phpbb\cron\manager($tasks, $phpbb_root_path, $phpEx);
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name));

		$this->assertSame('', $command_tester->getDisplay());
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_no_task_verbose()
	{
		$tasks = array(
		);
		$this->cron_manager = new \phpbb\cron\manager($tasks, $phpbb_root_path, $phpEx);
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name, '--verbose' => true));

		$this->assertContains('CRON_NO_TASK', $command_tester->getDisplay());
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_arg_valid()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name, 'name' => 'phpbb_cron_task_simple'));

		$this->assertSame('', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_arg_invalid()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name, 'name' => 'foo'));

		$this->assertContains('CRON_NO_SUCH_TASK', $command_tester->getDisplay());
		$this->assertSame(false, $this->task->executed);
		$this->assertSame(2, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function test_arg_valid_verbose()
	{
		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name, 'name' => 'phpbb_cron_task_simple', '--verbose' => true));

		$this->assertContains('RUNNING_TASK', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
		$this->assertSame(0, $exit_status);
		$this->assertSame(false, $this->lock->owns_lock());
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new run($this->user, $this->cron_manager, $this->lock));

		$command = $application->find('cron:run');
		$this->command_name = $command->getName();
		return new CommandTester($command);
	}
}
