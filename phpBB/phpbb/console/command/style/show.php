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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class show extends command
{
	protected function configure()
	{
		$this
			->setName('style:show')
			->setDescription($this->user->lang('CLI_DESCRIPTION_LIST_STYLES'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		// Get installed styles
		$installed = $this->manager->get_installed_styles();

		// Get styles that are enabled from the installed styles
		$enabled = array_filter($installed, function($v) {
			return $v['style_active'];
		});
		$enabled = array_column($enabled, 'style_name');
		$io->section($this->user->lang('CLI_STYLES_ACTIVATED'));
		$io->listing($enabled);

		// Get styles that are disabled from the installed styles
		$disabled = array_filter($installed, function($v) {
			return !$v['style_active'];
		});
		$disabled = array_column($disabled, 'style_name');
		$io->section($this->user->lang('CLI_STYLES_DEACTIVATED'));
		$io->listing($disabled);

		// Get available styles
		$available = $this->manager->find_available(false);
		$available = array_column($available, 'style_name');
		$io->section($this->user->lang('CLI_STYLES_AVAILABLE'));
		$io->listing($available);
	}
}
