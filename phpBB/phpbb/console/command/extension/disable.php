<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace phpbb\console\command\extension;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class disable extends command
{
	protected function configure()
	{
		$this
			->setName('extension:disable')
			->setDescription('Disables the specified extension.')
			->addArgument(
				'extension-name',
				InputArgument::REQUIRED,
				'Name of the extension'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('extension-name');
		$this->manager->disable($name);
		$this->manager->load_extensions();

		if ($this->manager->enabled($name))
		{
			$output->writeln("<error>Could not disable extension $name</error>");
			return 1;
		}
		else
		{
			$output->writeln("<info>Successfully disabled extension $name</info>");
			return 0;
		}
	}
}
