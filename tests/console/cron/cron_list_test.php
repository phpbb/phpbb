<?php
/**
 *
 * @package testing
 * @copyright (c) 2014 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/tasks/simple_ready.php';
require_once dirname(__FILE__) . '/tasks/simple_not_ready.php';
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\cron\cron_list;

class phpbb_console_command_cron_list_test extends phpbb_test_case
{
	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/** @var \phpbb\user */
	protected $user;

	protected $command_name;

	protected function setUp()
	{
		$this->user = $this->getMock('\phpbb\user');
		$this->user->method('lang')->will($this->returnArgument(0));
	}

	public function test_no_task()
	{
		$tasks = array();
		$this->get_cron_manager($tasks);
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true));
		$this->assertContains('NO_TASK', $command_tester->getDisplay());
	}

	public function test_only_ready()
	{
		$tasks = array(
			new phpbb_cron_task_simple_ready(),
			new phpbb_cron_task_simple_ready()
		);
		$this->get_cron_manager($tasks);
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true));
		$this->assertContains('TASKS_READY command1 command2', preg_replace('/\s+/', ' ', trim($command_tester->getDisplay())));
	}

	public function test_only_not_ready()
	{
		$tasks = array(
			new phpbb_cron_task_simple_not_ready(),
			new phpbb_cron_task_simple_not_ready()
		);
		$this->get_cron_manager($tasks);
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true));
		$this->assertContains('TASKS_NOT_READY command1 command2', preg_replace('/\s+/', ' ', trim($command_tester->getDisplay())));
	}

	public function test_both_ready()
	{
		$tasks = array(
			new phpbb_cron_task_simple_ready(),
			new phpbb_cron_task_simple_ready(),
			new phpbb_cron_task_simple_not_ready(),
			new phpbb_cron_task_simple_not_ready()
		);
		$this->get_cron_manager($tasks);
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true));
		$this->assertSame('TASKS_READY command1 command2 TASKS_NOT_READY command3 command4', preg_replace('/\s+/', ' ', trim($command_tester->getDisplay())));
	}

	public function get_cron_manager(array $tasks)
	{
		global $pathEx, $phpbb_root_path;
		$i = 1;
		foreach ($tasks as $task)
		{
			$task->set_name('command' . $i);
			$i++;
		}
		$this->cron_manager = new \phpbb\cron\manager($tasks, $phpbb_root_path, $pathEx);
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new cron_list($this->cron_manager, $this->user));

		$command = $application->find('cron:list');
		$this->command_name = $command->getName();
		return new CommandTester($command);
	}
}
