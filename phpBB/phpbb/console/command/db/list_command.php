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
namespace phpbb\console\command\db;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class list_command extends \phpbb\console\command\db\migration_command
{
	protected function configure()
	{
		$this
			->setName('db:list')
			->setDescription($this->user->lang('CLI_DESCRIPTION_DB_LIST'))
			->addOption(
				'available',
				'u',
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_MIGRATIONS_ONLY_AVAILABLE')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$show_installed = !$input->getOption('available');
		$installed = $available = array();

		foreach ($this->load_migrations() as $name)
		{
			if ($this->migrator->migration_state($name) !== false)
			{
				$installed[] = $name;
			}
			else
			{
				$available[] = $name;
			}
		}

		if ($show_installed)
		{
			$output->writeln('<info>' . $this->user->lang('CLI_MIGRATIONS_INSTALLED') . $this->user->lang('COLON') . '</info>');
			$output->writeln($installed);

			if (empty($installed))
			{
				$output->writeln($this->user->lang('CLI_MIGRATIONS_EMPTY'));
			}

			$output->writeln('');
		}

		$output->writeln('<info>' . $this->user->lang('CLI_MIGRATIONS_AVAILABLE') . $this->user->lang('COLON') . '</info>');
		$output->writeln($available);

		if (empty($available))
		{
			$output->writeln($this->user->lang('CLI_MIGRATIONS_EMPTY'));
		}
	}
}
