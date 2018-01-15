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

use phpbb\db\output_handler\log_wrapper_migrator_output_handler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class revert extends \phpbb\console\command\db\migrate
{
	protected function configure()
	{
		$this
			->setName('db:revert')
			->setDescription($this->language->lang('CLI_DESCRIPTION_DB_REVERT'))
			->addArgument(
				'name',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_MIGRATION_NAME')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$name = str_replace('/', '\\', $input->getArgument('name'));

		$this->migrator->set_output_handler(new log_wrapper_migrator_output_handler($this->language, new console_migrator_output_handler($this->user, $output), $this->phpbb_root_path . 'store/migrations_' . time() . '.log', $this->filesystem));

		$this->cache->purge();

		if (!in_array($name, $this->load_migrations()))
		{
			$io->error($this->language->lang('MIGRATION_NOT_VALID', $name));
			return 1;
		}
		else if ($this->migrator->migration_state($name) === false)
		{
			$io->error($this->language->lang('MIGRATION_NOT_INSTALLED', $name));
			return 1;
		}

		try
		{
			while ($this->migrator->migration_state($name) !== false)
			{
				$this->migrator->revert($name);
			}
		}
		catch (\phpbb\db\migration\exception $e)
		{
			$io->error($e->getLocalisedMessage($this->user));
			$this->finalise_update();
			return 1;
		}

		$this->finalise_update();
		$io->success($this->language->lang('INLINE_UPDATE_SUCCESSFUL'));
	}
}
