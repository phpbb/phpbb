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

class set_atomic extends command
{
	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('config:set-atomic')
			->setDescription("Sets a configuration option's value only if the old matches the current value.")
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				"The configuration option's name"
			)
			->addArgument(
				'old',
				InputArgument::REQUIRED,
				'Current configuration value, use 0 and 1 to specify boolean values'
			)
			->addArgument(
				'new',
				InputArgument::REQUIRED,
				'New configuration value, use 0 and 1 to specify boolean values'
			)
			->addOption(
				'dynamic',
				'd',
				InputOption::VALUE_NONE,
				'Set this option if the configuration option changes too frequently to be efficiently cached.'
			)
		;
	}

	/**
	* Executes the command config:set-atomic.
	*
	* Sets a configuration option's value only if the old_value matches the
	* current configuration value or the configuration value does not exist yet.
	*
	* @param InputInterface  $input  An InputInterface instance
	* @param OutputInterface $output An OutputInterface instance
	*
	* @return bool True if the value was changed, false otherwise.
	* @see \phpbb\config\config::set_atomic()
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$key = $input->getArgument('key');
		$old_value = $input->getArgument('old');
		$new_value = $input->getArgument('new');
		$use_cache = !$input->getOption('dynamic');

		if ($this->config->set_atomic($key, $old_value, $new_value, $use_cache))
		{
			$output->writeln("<info>Successfully set config $key</info>");
			return 0;
		}
		else
		{
			$output->writeln("<error>Could not set config $key</error>");
			return 1;
		}
	}
}
