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

namespace phpbb\console\command\cron;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class run_all extends \phpbb\console\command\command
{
	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/** @var \phpbb\lock\db */
	protected $lock_db;

	/** @var \phpbb\user */
	protected $user;

	/**
	* Construct method
	*
	* @param \phpbb\cron\manager $cron_manager The cron manager containing
	*							the cron tasks to be executed.
	* @param \phpbb\lock\db $lock_db The lock for accessing database.
	* @param \phobb\user $user The user object (used to get language information)
	*/
	public function __construct(\phpbb\cron\manager $cron_manager, \phpbb\lock\db $lock_db, \phpbb\user $user)
	{
		$this->cron_manager = $cron_manager;
		$this->lock_db = $lock_db;
		$this->user = $user;
		parent::__construct();
	}

	/**
	* Sets the command name and description
	*
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('cron:run-all')
			->setDescription($this->user->lang('CLI_DESCR_CRON_RUN_ALL'))
		;
	}

	/**
	* Executes the function.
	*
	* Tries to acquire the cron lock, then runs all ready cron tasks.
	* If the cron lock can not be obtained, an error message is printed
	*		and the exit status is set to 1.
	* If the verbose option is specified, each start of a task is printed.
	*		Otherwise there is no output.
	*
	* @param InputInterface $input The input stream, unused here
	* @param OutputInterface $output The output stream, used for printig verbose-mode
	*							and error information.
	* @return int 0 if all is ok, 1 if a lock error occured
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($this->lock_db->acquire())
		{
			$run_tasks = $this->cron_manager->find_all_ready_tasks();

			foreach ($run_tasks as $task)
			{
				if ($input->getOption('verbose'))
				{
					$output->writeln($this->user->lang('RUNNING_TASK', $task->get_name()));
				}

				$task->run();
			}
			$this->lock_db->release();

			return 0;
		}
		else
		{
			$output->writeln('<error>' . $this->user->lang('CRON_LOCK_ERROR') . '</error>');
			return 1;
		}
	}
}
