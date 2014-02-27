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

class get extends command
{
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

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$key = $input->getArgument('key');

		if (isset($this->config[$key]) && $input->getOption('no-newline'))
		{
			$output->write($this->config[$key]);
		}
		elseif (isset($this->config[$key]))
		{
			$output->writeln($this->config[$key]);
		}
		else
		{
			$output->writeln("<error>Could not get config $key</error>");
		}
	}
}
