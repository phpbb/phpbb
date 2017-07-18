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

class install extends command
{
	protected function configure()
	{
		$this
			->setName('style:install')
			->setDescription($this->language->lang('CLI_DESCRIPTION_INSTALL_STYLE'))
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

		$style_path = $input->getArgument('style-path');

		$style = $this->manager->read_style_cfg($style_path);
		$style_name = $style ? $style['name'] : 'Uknown';

		try
		{
			$this->manager->install($style_path);

			$this->log->add('admin', ANONYMOUS, '', 'LOG_STYLE_ADD', time(), array($style_name));

			$io->success($this->language->lang('STYLE_INSTALLED', $style_name));
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
