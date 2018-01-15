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

class set extends command
{
	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('config:set')
			->setDescription($this->user->lang('CLI_DESCRIPTION_SET_CONFIG'))
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_CONFIG_OPTION_NAME')
			)
			->addArgument(
				'value',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_CONFIG_NEW')
			)
			->addOption(
				'dynamic',
				'd',
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_CONFIG_CANNOT_CACHED')
			)
		;
	}

	/**
	* Executes the command config:set.
	*
	* Sets a configuration option's value.
	*
	* @param InputInterface  $input  An InputInterface instance
	* @param OutputInterface $output An OutputInterface instance
	*
	* @return void
	* @see \phpbb\config\config::set()
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$key = $input->getArgument('key');
		$value = $input->getArgument('value');
		$use_cache = !$input->getOption('dynamic');

		$this->config->set($key, $value, $use_cache);

		$io->success($this->user->lang('CLI_CONFIG_SET_SUCCESS', $key));
	}
}
