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
use Symfony\Component\Console\Style\SymfonyStyle;

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
		$io = new SymfonyStyle($input, $output);

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
			$io->section($this->user->lang('CLI_MIGRATIONS_INSTALLED'));

			if (!empty($installed))
			{
				$io->listing($installed);
			}
			else
			{
				$io->text($this->user->lang('CLI_MIGRATIONS_EMPTY'));
				$io->newLine();
			}
		}

		$io->section($this->user->lang('CLI_MIGRATIONS_AVAILABLE'));
		if (!empty($available))
		{
			$io->listing($available);
		}
		else
		{
			$io->text($this->user->lang('CLI_MIGRATIONS_EMPTY'));
			$io->newLine();
		}
	}
}
