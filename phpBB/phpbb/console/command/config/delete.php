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
namespace phpbb\console\command\config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class delete extends command
{
	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('config:delete')
			->setDescription($this->user->lang('CLI_DESCRIPTION_DELETE_CONFIG'))
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_CONFIG_OPTION_NAME')
			)
		;
	}

	/**
	* Executes the command config:delete.
	*
	* Removes a configuration option
	*
	* @param InputInterface  $input  An InputInterface instance
	* @param OutputInterface $output An OutputInterface instance
	*
	* @return null
	* @see \phpbb\config\config::delete()
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$key = $input->getArgument('key');

		if (isset($this->config[$key]))
		{
			$this->config->delete($key);

			$output->writeln('<info>' . $this->user->lang('CLI_CONFIG_DELETE_SUCCESS', $key) . '</info>');
		}
		else
		{
			$output->writeln('<error>' . $this->user->lang('CLI_CONFIG_NOT_EXISTS', $key) . '</error>');
		}
	}
}
