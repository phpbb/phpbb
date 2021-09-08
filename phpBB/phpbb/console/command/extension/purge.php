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

use Symfony\Component\Console\Command\Command as symfony_command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class purge extends command
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('extension:purge')
			->setDescription($this->user->lang('CLI_DESCRIPTION_PURGE_EXTENSION'))
			->addArgument(
				'extension-name',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_EXTENSION_NAME')
			)
		;
	}

	/**
	 * Executes the command extension:purge.
	 *
	 * Purges the specified extension
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$name = $input->getArgument('extension-name');
		$this->manager->purge($name);
		$this->manager->load_extensions();

		if ($this->manager->is_enabled($name))
		{
			$io->error($this->user->lang('CLI_EXTENSION_PURGE_FAILURE', $name));
			return symfony_command::FAILURE;
		}
		else
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_EXT_PURGE', time(), array($name));
			$this->check_apcu_cache($io);
			$io->success($this->user->lang('CLI_EXTENSION_PURGE_SUCCESS', $name));
			return symfony_command::SUCCESS;
		}
	}
}
