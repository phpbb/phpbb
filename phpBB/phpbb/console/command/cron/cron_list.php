<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
			->setDescription($this->user->lang('CLI_DESCR_CRON_LIST'))
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
			foreach ($ready_tasks as $task)
			{
				$output->writeln($task->get_name());
			}
			$output->writeln('');
		}

		if (!empty($not_ready_tasks))
		{
			$output->writeln('<info>' . $this->user->lang('TASKS_NOT_READY') . '</info>');
			foreach ($not_ready_tasks as $task)
			{
				$output->writeln($task->get_name());
			}
		}
	}
}
