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

class install extends command
{
	protected function configure()
	{
		$this
			->setName('style:install')
			->setDescription($this->user->lang('CLI_DESCRIPTION_INSTALL_STYLE'))
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
			$available_styles = $this->manager->find_available(false);
			$style_path = '';

			foreach($available_styles as $style)
			{
				if($style['style_name'] == $name)
				{
					$style_path = $style['style_path'];
					break;
				}
			}

			if(empty($style_path))
			{
				throw new exception(''); // Empty exception, because the error is generic
			}

			$this->manager->install($style_path);
			$this->log->add('admin', ANONYMOUS, '', 'LOG_STYLE_ADD', time(), array($name));
			$io->success($this->user->lang('CLI_STYLE_INSTALL_SUCCESS', $name));
		}
		catch (\phpbb\exception\runtime_exception $e)
		{
			$io->error($this->user->lang('CLI_STYLE_INSTALL_FAILURE', $name));
			return 1;
		}

		return 0;
	}
}
