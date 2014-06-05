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
namespace phpbb\console\command\extension;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class show extends command
{
	protected function configure()
	{
		$this
			->setName('extension:show')
			->setDescription($this->user->lang('CLI_DESCRIPTION_EXTENSION_SHOW'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->manager->load_extensions();
		$all = array_keys($this->manager->all_available());

		if (empty($all))
		{
			$output->writeln('<comment>' . $this->user->lang('EXTENSION_SHOW_FAIL') . '</comment>');
			return 3;
		}

		$enabled = array_keys($this->manager->all_enabled());
		$this->print_extension_list($output, $this->user->lang('ENABLED'), $enabled);

		$output->writeln('');

		$disabled = array_keys($this->manager->all_disabled());
		$this->print_extension_list($output, $this->user->lang('DISABLED'), $disabled);

		$output->writeln('');

		$purged = array_diff($all, $enabled, $disabled);
		$this->print_extension_list($output, $this->user->lang('AVAILABLE'), $purged);
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
