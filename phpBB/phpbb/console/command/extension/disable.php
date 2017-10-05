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

class disable extends command
{
	protected function configure()
	{
		$this
			->setName('extension:disable')
			->setDescription($this->user->lang('CLI_DESCRIPTION_DISABLE_EXTENSION'))
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

		if (!$this->manager->is_enabled($name))
		{
			$io->error($this->user->lang('CLI_EXTENSION_DISABLED', $name));
			return 2;
		}

		$this->manager->disable($name);
		$this->manager->load_extensions();

		if ($this->manager->is_enabled($name))
		{
			$io->error($this->user->lang('CLI_EXTENSION_DISABLE_FAILURE', $name));
			return 1;
		}
		else
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_EXT_DISABLE', time(), array($name));
			$io->success($this->user->lang('CLI_EXTENSION_DISABLE_SUCCESS', $name));
			return 0;
		}
	}
}
