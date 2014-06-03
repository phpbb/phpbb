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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class cron_list extends \phpbb\console\command\command
{
	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\cron\manager $cron_manager, \phpbb\user $user)
	{
		$this->cron_manager = $cron_manager;
		$this->user = $user;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('cron:list')
			->setDescription($this->user->lang('CLI_DESCRIPTION_CRON_LIST'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$tasks = $this->cron_manager->get_tasks();

		if (empty($tasks))
		{
			$output->writeln($this->user->lang('NO_TASK'));
			return;
		}

		$ready_tasks = array();
		$not_ready_tasks = array();
		foreach ($tasks as $task)
		{
			if ($task->is_ready())
			{
				$ready_tasks[] = $task;
			}
			else
			{
				$not_ready_tasks[] = $task;
			}
		}

		if (!empty($ready_tasks))
		{
			$output->writeln('<info>' . $this->user->lang('TASKS_READY') . '</info>');
			$this->print_tasks_names($ready_tasks, $output);
		}

		if (!empty($ready_tasks) && !empty($not_ready_tasks))
		{
			$output->writeln('');
		}

		if (!empty($not_ready_tasks))
		{
			$output->writeln('<info>' . $this->user->lang('TASKS_NOT_READY') . '</info>');
			$this->print_tasks_names($not_ready_tasks, $output);
		}
	}

	public function print_tasks_names ($tasks, $output)
	{
		foreach ($tasks as $task)
		{
			$output->writeln($task->get_name());
		}
	}
}

