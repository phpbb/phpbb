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

namespace phpbb\console\command\cron;

use phpbb\exception\runtime_exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class run extends \phpbb\console\command\command
{
	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/** @var \phpbb\lock\db */
	protected $lock_db;

	/**
	* Construct method
	*
	* @param \phpbb\user $user The user object (used to get language information)
	* @param \phpbb\cron\manager $cron_manager The cron manager containing
	*		the cron tasks to be executed.
	* @param \phpbb\lock\db $lock_db The lock for accessing database.
	*/
	public function __construct(\phpbb\user $user, \phpbb\cron\manager $cron_manager, \phpbb\lock\db $lock_db)
	{
		$this->cron_manager = $cron_manager;
		$this->lock_db = $lock_db;
		parent::__construct($user);
	}

	/**
	* Sets the command name and description
	*
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('cron:run')
			->setDescription($this->user->lang('CLI_DESCRIPTION_CRON_RUN'))
			->setHelp($this->user->lang('CLI_HELP_CRON_RUN'))
			->addArgument('name', InputArgument::OPTIONAL, $this->user->lang('CLI_DESCRIPTION_CRON_RUN_ARGUMENT_1'))
		;
	}

	/**
	* Executes the command cron:run.
	*
	* Tries to acquire the cron lock, then if no argument has been given runs all ready cron tasks.
	* If the cron lock can not be obtained, an error message is printed
	*		and the exit status is set to 1.
	* If the verbose option is specified, each start of a task is printed.
	*		Otherwise there is no output.
	* If an argument is given to the command, only the task whose name matches the
	*		argument will be started. If verbose option is specified,
	*		an info message containing the name of the task is printed.
	* If no task matches the argument given, an error message is printed
	*		and the exit status is set to 2.
	*
	* @param InputInterface $input The input stream used to get the argument and verboe option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	*
	* @return int 0 if all is ok, 1 if a lock error occured and 2 if no task matching the argument was found.
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($this->lock_db->acquire())
		{
			$task_name = $input->getArgument('name');
			if ($task_name)
			{
				$exit_status = $this->run_one($input, $output, $task_name);
			}
			else
			{
				$exit_status = $this->run_all($input, $output);
			}

			$this->lock_db->release();
			return $exit_status;
		}
		else
		{
			throw new runtime_exception('CRON_LOCK_ERROR', array(), null, 1);
		}
	}

	/**
	* Executes all ready cron tasks.
	*
	* If verbose mode is set, an info message will be printed if there is no task to
	*		be run, or else for each starting task.
	*
	* @see execute
	* @param InputInterface $input The input stream used to get the argument and verbose option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	* @return int 0
	*/
	protected function run_all(InputInterface $input, OutputInterface $output)
	{
		$run_tasks = $this->cron_manager->find_all_ready_tasks();

		if ($run_tasks)
		{
			foreach ($run_tasks as $task)
			{
				if ($input->getOption('verbose'))
				{
					$output->writeln('<info>' . $this->user->lang('RUNNING_TASK', $task->get_name()) . '</info>');
				}

				$task->run();
			}
		}
		else
		{
			if ($input->getOption('verbose'))
			{
				$output->writeln('<info>' . $this->user->lang('CRON_NO_TASK') . '</info>');
			}
		}

		return 0;
	}

	/**
	* Executes a given cron task, if it is ready.
	*
	* If there is a task whose name matches $task_name, it is run and 0 is returned.
	*		and if verbose mode is set, print an info message with the name of the task.
	* If there is no task matching $task_name, the function prints an error message
	*		and returns with status 2.
	*
	* @see execute
	* @param string $task_name The name of the task that should be run.
	* @param InputInterface $input The input stream used to get the argument and verbose option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	* @return int 0 if all is well, 2 if no task matches $task_name.
	*/
	protected function run_one(InputInterface $input, OutputInterface $output, $task_name)
	{
		$task = $this->cron_manager->find_task($task_name);
		if ($task)
		{
			if ($input->getOption('verbose'))
			{
				$output->writeln('<info>' . $this->user->lang('RUNNING_TASK', $task_name) . '</info>');
			}

			$task->run();
			return 0;
		}
		else
		{
			throw new runtime_exception('CRON_NO_SUCH_TASK', array( $task_name), null, 2);
		}
	}
}
