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

namespace phpbb\console\command\style;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// TODO: Optional argument to also delete files
class uninstall extends command
{
	protected function configure()
	{
		$this
			->setName('style:uninstall')
			->setDescription($this->user->lang('CLI_DESCRIPTION_UNINSTALL_STYLE'))
			->addArgument(
				'style-name',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_STYLE_NAME')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$name = $input->getArgument('style-name');

		try
		{
			$data = $this->manager->get_style_data('style_name', $name);
			$this->manager->uninstall($data['style_path']);
			$this->log->add('admin', ANONYMOUS, '', 'LOG_STYLE_DELETE', time(), array($name));
			$io->success($this->user->lang('STYLE_UNINSTALLED', $name));
		}
		catch (\phpbb\style\exception $e)
		{
			$msg = $this->user->lang($e->getMessage());
			$io->error($this->user->lang($msg, $name));
			return 1;
		}

		return 0;
	}
}
