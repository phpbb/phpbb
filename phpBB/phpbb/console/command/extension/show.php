<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace phpbb\console\command\extension;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class show extends command
{
	protected function configure()
	{
		$this
			->setName('extension:show')
			->setDescription('Lists all extensions in the database and on the filesystem.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->manager->load_extensions();
		$all = array_keys($this->manager->all_available());

		if (empty($all))
		{
			$output->writeln('<comment>No extensions were found.</comment>');
			return 3;
		}

		$enabled = array_keys($this->manager->all_enabled());
		$this->print_extension_list($output, 'Enabled', $enabled);

		$output->writeln('');

		$disabled = array_keys($this->manager->all_disabled());
		$this->print_extension_list($output, 'Disabled', $disabled);

		$output->writeln('');

		$purged = array_diff($all, $enabled, $disabled);
		$this->print_extension_list($output, 'Available', $purged);
	}

	protected function print_extension_list(OutputInterface $output, $type, array $extensions)
	{
		$output->writeln("<info>$type:</info>");

		foreach ($extensions as $extension)
		{
			$output->writeln(" - $extension");
		}
	}
}
