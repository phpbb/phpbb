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
use Symfony\Component\Console\Style\SymfonyStyle;

class cron_list extends \phpbb\console\command\command
{
	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/**
	* Constructor
	*
	* @param \phpbb\user			$user			User instance
	* @param \phpbb\cron\manager	$cron_manager	Cron manager
	*/
	public function __construct(\phpbb\user $user, \phpbb\cron\manager $cron_manager)
	{
		$this->cron_manager = $cron_manager;
		parent::__construct($user);
	}

	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('cron:list')
			->setDescription($this->user->lang('CLI_DESCRIPTION_CRON_LIST'))
		;
	}

	/**
	* Executes the command cron:list.
	*
	* Prints a list of ready and unready cron jobs.
	*
	* @param InputInterface  $input  An InputInterface instance
	* @param OutputInterface $output An OutputInterface instance
	*
	* @return void
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$tasks = $this->cron_manager->get_tasks();

		if (empty($tasks))
		{
			$io->error($this->user->lang('CRON_NO_TASKS'));
			return;
		}

		$ready_tasks = $not_ready_tasks = array();
		foreach ($tasks as $task)
		{
			if ($task->is_ready())
			{
				$ready_tasks[] = $task->get_name();
			}
			else
			{
				$not_ready_tasks[] = $task->get_name();
			}
		}

		if (!empty($ready_tasks))
		{
			$io->title($this->user->lang('TASKS_READY'));
			$io->listing($ready_tasks);
		}

		if (!empty($not_ready_tasks))
		{
			$io->title($this->user->lang('TASKS_NOT_READY'));
			$io->listing($not_ready_tasks);
		}
	}
}
