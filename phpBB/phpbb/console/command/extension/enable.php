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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class enable extends command
{
	protected function configure()
	{
		$this
			->setName('extension:enable')
			->setDescription($this->user->lang('CLI_DESCRIPTION_ENABLE_EXTENSION'))
			->addArgument(
				'extension-name',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_EXTENSION_NAME')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$name = $input->getArgument('extension-name');

		if ($this->manager->is_enabled($name))
		{
			$io->error($this->user->lang('CLI_EXTENSION_ENABLED', $name));
			return 2;
		}

		$this->manager->enable($name);
		$this->manager->load_extensions();

		if ($this->manager->is_enabled($name))
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_EXT_ENABLE', time(), array($name));
			$io->success($this->user->lang('CLI_EXTENSION_ENABLE_SUCCESS', $name));
			return 0;
		}
		else
		{
			$io->error($this->user->lang('CLI_EXTENSION_ENABLE_FAILURE', $name));
			return 1;
		}
	}
}
