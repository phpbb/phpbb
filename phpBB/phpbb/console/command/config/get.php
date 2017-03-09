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
use Symfony\Component\Console\Style\SymfonyStyle;

class get extends command
{
	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('config:get')
			->setDescription($this->user->lang('CLI_DESCRIPTION_GET_CONFIG'))
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_CONFIG_OPTION_NAME')
			)
			->addOption(
				'no-newline',
				null,
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_CONFIG_PRINT_WITHOUT_NEWLINE')
			)
		;
	}

	/**
	* Executes the command config:get.
	*
	* Retrieves a configuration value.
	*
	* @param InputInterface  $input  An InputInterface instance
	* @param OutputInterface $output An OutputInterface instance
	*
	* @return void
	* @see \phpbb\config\config::offsetGet()
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$key = $input->getArgument('key');

		if (isset($this->config[$key]) && $input->getOption('no-newline'))
		{
			$output->write($this->config[$key]);
		}
		else if (isset($this->config[$key]))
		{
			$output->writeln($this->config[$key]);
		}
		else
		{
			$io->error($this->user->lang('CLI_CONFIG_NOT_EXISTS', $key));
		}
	}
}
