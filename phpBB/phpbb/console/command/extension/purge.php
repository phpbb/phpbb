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

class purge extends command
{
	protected function configure()
	{
		$this
			->setName('extension:purge')
			->setDescription($this->user->lang('CLI_DESCRIPTION_PURGE_EXTENSION'))
			->addArgument(
				'extension-name',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_EXTENSION_NAME')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('extension-name');
		$this->manager->purge($name);
		$this->manager->load_extensions();

		if ($this->manager->is_enabled($name))
		{
			$output->writeln('<error>' . $this->user->lang('CLI_EXTENSION_PURGE_FAILURE', $name) . '</error>');
			return 1;
		}
		else
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_EXT_PURGE', time(), array($name));
			$output->writeln('<info>' . $this->user->lang('CLI_EXTENSION_PURGE_SUCCESS', $name) . '</info>');
			return 0;
		}
	}
}
