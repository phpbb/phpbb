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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class execute_all extends \phpbb\console\command\command
{
	/** @var \phpbb\cron\manager */
	protected $cron_manager;

	/** @var \phpbb\lock\db */
	protected $lock_db;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\cron\manager $cron_manager, \phpbb\lock\db $lock_db, \phpbb\user $user)
	{
		$this->cron_manager = $cron_manager;
		$this->lock_db = $lock_db;
		$this->user = $user;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('cron:execute-all')
			->setDescription($this->user->lang('CLI_DESCR_CRON_EXECUTE_ALL'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($this->lock_db->acquire())
		{
			$run_tasks = $this->cron_manager->find_all_ready_tasks();

			foreach ($run_tasks as $task)
			{
				if ($input->getOption('verbose'))
				{
					$output->writeln($this->user->lang('RUNNING_TASK', $task->get_name()) . "\n");
				}

				$task->run();
			}
			$this->lock_db->release();
		}
		else
		{
			$output->writeln('<error>' . $this->user->lang('CRON_LOCK_ERROR') . '</error>');
		}
	}
}
