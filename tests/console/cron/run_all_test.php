<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited 
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\cron\run_all;

require_once dirname(__FILE__) . '/tasks/simple.php';

class phpbb_console_command_cron_run_all_test extends phpbb_database_test_case
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
		global $db, $config, $phpbb_root_path, $pathEx;

		$db = $this->db = $this->new_dbal();
		$config = $this->config = new \phpbb\config\config(array('cron_lock' => '0'));
		set_config(null, null, null, $this->config);
		$this->lock = new \phpbb\lock\db('cron_lock', $this->config, $this->db);

		$this->user = $this->getMock('\phpbb\user');
		$this->user->method('lang')->will($this->returnArgument(0));

		$this->task = new phpbb_cron_task_simple();
		$tasks = array(
			$this->task,
		);
		$this->cron_manager = new \phpbb\cron\manager($tasks, $phpbb_root_path, $pathEx);

		$this->assertSame('0', $config['cron_lock']);
	}

	public function test_normal_use()
	{
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $this->command_name));

		$this->assertSame('', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
	}

	public function test_verbose_mode()
	{
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $this->command_name, '--verbose' => true));

		$this->assertContains('RUNNING_TASK', $command_tester->getDisplay());
		$this->assertSame(true, $this->task->executed);
	}

	public function test_error_lock()
	{
		$this->lock->acquire();
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $this->command_name));

		$this->assertContains('CRON_LOCK_ERROR', $command_tester->getDisplay());
		$this->assertSame(false, $this->task->executed);
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new run_all($this->cron_manager, $this->lock, $this->user));

		$command = $application->find('cron:run-all');
		$this->command_name = $command->getName();
		return new CommandTester($command);
	}
}
