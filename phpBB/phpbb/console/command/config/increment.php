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

class increment extends command
{
	protected function configure()
	{
		$this
			->setName('config:increment')
			->setDescription("Increment a configuration option's value")
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				"The configuration option's name"
			)
			->addArgument(
				'increment',
				InputArgument::REQUIRED,
				'Amount to increment by'
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
		$increment = $input->getArgument('increment');
		$use_cache = !$input->getOption('dynamic');

		$this->config->increment($key, $increment, $use_cache);

		$output->writeln("<info>Successfully incremented config $key</info>");
	}
}
