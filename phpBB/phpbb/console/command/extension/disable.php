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
			->setDescription('Disables the specified extension.')
			->addArgument(
				'extension-name',
				InputArgument::REQUIRED,
				'Name of the extension'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('extension-name');
		$this->manager->disable($name);
		$this->manager->load_extensions();

		if ($this->manager->is_enabled($name))
		{
			$output->writeln("<error>Could not disable extension $name</error>");
			return 1;
		}
		else
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_EXT_DISABLE', time(), array($name));
			$output->writeln("<info>Successfully disabled extension $name</info>");
			return 0;
		}
	}
}
