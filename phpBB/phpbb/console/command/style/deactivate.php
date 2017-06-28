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

namespace phpbb\console\command\style;

use phpbb\style\exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class deactivate extends command
{
	protected function configure()
	{
		$this
			->setName('style:deactivate')
			->setDescription($this->user->lang('CLI_DESCRIPTION_DEACTIVATE_STYLE'))
			->addArgument(
				'style-path',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_STYLE_NAME')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$dir = $input->getArgument('style-path');

		try
		{
			$this->manager->deactivate(array($dir));
			$this->log->add('admin', ANONYMOUS, '', 'LOG_STYLE_DEACTIVATE', time(), array($dir));

			$io->success($this->user->lang('CLI_STYLE_DEACTIVATE_SUCCESS', $dir));
		}
		catch (exception $e)
		{
			$io->error($this->user->lang('CLI_STYLE_DEACTIVATE_FAILURE', $dir));

			return 1;
		}

		return 0;
	}
}
