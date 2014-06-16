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

class get extends command
{
	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('config:get')
			->setDescription("Gets a configuration option's value")
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				"The configuration option's name"
			)
			->addOption(
				'no-newline',
				null,
				InputOption::VALUE_NONE,
				'Set this option if the value should be printed without a new line at the end.'
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
	* @return null
	* @see \phpbb\config\config::offsetGet()
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
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
			$output->writeln("<error>Could not get config $key</error>");
		}
	}
}
