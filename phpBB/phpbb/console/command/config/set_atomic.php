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
			->setDescription($this->user->lang('CLI_DESCRIPTION_SET_ATOMIC_CONFIG'))
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_CONFIG_OPTION_NAME')
			)
			->addArgument(
				'old',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_CONFIG_CURRENT')
			)
			->addArgument(
				'new',
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
			$output->writeln('<info>' . $this->user->lang('CLI_CONFIG_SET_SUCCESS', $key) . '</info>');
			return 0;
		}
		else
		{
			$output->writeln('<error>' . $this->user->lang('CLI_CONFIG_SET_FAILURE', $key) . '</error>');
			return 1;
		}
	}
}
