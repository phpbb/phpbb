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

class disable extends command
{
	protected function configure()
	{
		$this
			->setName('extension:disable')
			->setDescription($this->user->lang('CLI_DESCRIPTION_EXTENSION_DISABLE'))
			->addArgument(
				'extension-name',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_EXTENSION_NAME_ARGUMENT')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('extension-name');
		$this->manager->disable($name);
		$this->manager->load_extensions();

		if ($this->manager->enabled($name))
		{
			$output->writeln('<error>' . $this->user->lang('EXTENSION_DISABLE_FAIL', $name) . '</error>');
			return 1;
		}
		else
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_EXT_DISABLE', time(), array($name));
			$output->writeln('<info>' . $this->user->lang('EXTENSION_DISABLE_SUCCESS', $name) . '</info>');
			return 0;
		}
	}
}
