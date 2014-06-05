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
namespace phpbb\console\command\config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class increment extends command
{
	protected function configure()
	{
		$this
			->setName('config:increment')
			->setDescription($this->user->lang('CLI_DESCRIPTION_CONFIG_INCREMENT'))
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_CONFIG_OPTION_NAME')
			)
			->addArgument(
				'increment',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_CONFIG_INCREMENT_ARGUMENT_2')
			)
			->addOption(
				'dynamic',
				'd',
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_DESCRIPTION_CONFIG_INCREMENT_OPTION')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$key = $input->getArgument('key');
		$increment = $input->getArgument('increment');
		$use_cache = !$input->getOption('dynamic');

		$this->config->increment($key, $increment, $use_cache);

		$output->writeln('<info>' . $this->user->lang('CONFIG_INCREMENT_SUCCESS', $key) . '</info>');
	}
}
