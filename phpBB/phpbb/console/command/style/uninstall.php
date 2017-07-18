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

use phpbb\style\exception as style_exception;
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
			->setDescription($this->uiser->lang('CLI_DESCRIPTION_UNINSTALL_STYLE'))
			->addArgument(
				'style-path',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_STYLE_PATH')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$style = $this->manager->get_style_data('style_path', $input->getArgument('style-path'));

		$style_id = (int) $style['style_id'];
		$style_name = $style['style_name'];

		try
		{
			$this->manager->uninstall($style_id);
			$this->log->add('admin', ANONYMOUS, '', 'LOG_STYLE_DELETE', time(), [$style_name]);

			$io->success($this->language->lang('STYLE_UNINSTALLED', $style_name));
		}
		catch (style_exception $e)
		{
			$msg = $this->language->lang($e->getMessage());
			$io->error($this->language->lang($msg, $style_name));
			return 1;
		}

		return 0;
	}
}
