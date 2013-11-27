<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace phpbb\console\command\config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class set_atomic extends command
{
	protected function configure()
	{
		$this
			->setName('config:set-atomic')
			->setDescription("Sets a configuration option's value")
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
