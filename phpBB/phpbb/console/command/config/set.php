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

class set extends command
{
	protected function configure()
	{
		$this
			->setName('config:set')
			->setDescription("Sets a configuration option's value")
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				"The configuration option's name"
			)
			->addArgument(
				'value',
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
		$value = $input->getArgument('value');
		$use_cache = !$input->getOption('dynamic');

		$this->config->set($key, $value, $use_cache);

		$output->writeln("<info>Successfully set config $key</info>");
	}
}
