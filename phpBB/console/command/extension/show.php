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
namespace phpbb\console\command\extension;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class show extends command
{
	protected function configure()
	{
		$this
			->setName('extension:show')
			->setDescription($this->user->lang('CLI_DESCRIPTION_LIST_EXTENSIONS'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$this->manager->load_extensions();
		$all = array_keys($this->manager->all_available());

		if (empty($all))
		{
			$io->note($this->user->lang('CLI_EXTENSION_NOT_FOUND'));
			return 3;
		}

		$enabled = array_keys($this->manager->all_enabled());
		$io->section($this->user->lang('CLI_EXTENSIONS_ENABLED'));
		$io->listing($enabled);

		$disabled = array_keys($this->manager->all_disabled());
		$io->section($this->user->lang('CLI_EXTENSIONS_DISABLED'));
		$io->listing($disabled);

		$purged = array_diff($all, $enabled, $disabled);
		$io->section($this->user->lang('CLI_EXTENSIONS_AVAILABLE'));
		$io->listing($purged);
	}
}
