<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\cron\execute_all;

require_once dirname(__FILE__) . '/tasks/simple_ready.php';

class phpbb_console_command_cron_execute_all_test extends phpbb_database_test_case
{
	protected $db;
	protected $config;
	protected $lock;
	protected $user;
	protected $cron_manager;
	protected $command_name;

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

		$tasks = array(
			new phpbb_cron_task_core_simple_ready(),
		);
		$this->cron_manager = new \phpbb\cron\manager($tasks, $phpbb_root_path, $pathEx);

		$this->assertEquals('0', $config['cron_lock']);
	}

	public function test_normal_use()
	{
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $command_name));

		$this->assertEquals('', $command_tester->getDisplay());
	}

	public function test_verbose_mode()
	{
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $command_name, '--verbose' => true));

		$this->assertContains('RUNNING_TASK', $command_tester->getDisplay());
	}

	public function test_error_lock()
	{
		$this->lock->acquire();
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $command_name));

		$this->assertContains('CRON_LOCK_ERROR', $command_tester->getDisplay());
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new execute_all($this->cron_manager, $this->lock, $this->user));

		$command = $application->find('cron:execute-all');
		$command_name = $command->getName();
		return new CommandTester($command);
	}
}

