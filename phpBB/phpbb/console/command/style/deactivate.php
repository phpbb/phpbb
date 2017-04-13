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

class deactivate extends command
{
	protected function configure()
	{
		$this
			->setName('style:deactivate')
			->setDescription($this->user->lang('CLI_DESCRIPTION_DEACTIVATE_STYLE'))
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
			$this->manager->deactivate(array($data['style_id']));
			// Log?
			$io->success($this->user->lang('CLI_STYLE_DEACTIVATE_SUCCESS', $name));
		}
		catch (\phpbb\exception\runtime_exception $e)
		{
			$io->error($this->user->lang('CLI_STYLE_DEACTIVATE_FAILURE', $name));
			return 1;
		}

		return 0;
	}
}
