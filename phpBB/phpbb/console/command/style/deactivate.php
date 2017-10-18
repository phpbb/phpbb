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

class deactivate extends command
{
	protected function configure()
	{
		$this
			->setName('style:deactivate')
			->setDescription($this->language->lang('CLI_DESCRIPTION_DEACTIVATE_STYLE'))
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
			$this->manager->deactivate(array($style_id));

			$this->log->add('admin', ANONYMOUS, '', 'LOG_STYLE_DEACTIVATE', time(), array($style_name));

			$io->success($this->language->lang('CLI_STYLE_DEACTIVATE_SUCCESS', $style_name));
		}
		catch (style_exception $e)
		{
			$io->error($this->language->lang('CLI_STYLE_DEACTIVATE_FAILURE', $style_name));

			return 1;
		}

		return 0;
	}
}
