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

class set extends command
{
	protected function configure()
	{
		$this
			->setName('config:set')
			->setDescription($this->user->lang('CLI_DESCRIPTION_CONFIG_SET'))
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_CONFIG_OPTION_NAME')
			)
			->addArgument(
				'value',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_CONFIG_SET_ARGUMENT_2')
			)
			->addOption(
				'dynamic',
				'd',
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_DESCRIPTION_CONFIG_SET_OPTION')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$key = $input->getArgument('key');
		$value = $input->getArgument('value');
		$use_cache = !$input->getOption('dynamic');

		$this->config->set($key, $value, $use_cache);

		$output->writeln('<info>' . $this->user->lang('CONFIG_SET_SUCCESS', $key) . '</info>');
	}
}
