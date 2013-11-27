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

class delete extends command
{
	protected function configure()
	{
		$this
			->setName('config:delete')
			->setDescription("Deletes a configuration option")
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				"The configuration option's name"
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$key = $input->getArgument('key');

		$this->config->delete($key);

		$output->writeln("<info>Successfully deleted config $key</info>");
	}
}
