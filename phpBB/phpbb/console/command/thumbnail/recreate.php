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
namespace phpbb\console\command\thumbnail;

use Symfony\Component\Console\Command\Command as symfony_command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class recreate extends \phpbb\console\command\command
{
	/**
	* Sets the command name and description
	*
	* @return void
	*/
	protected function configure()
	{
		$this
			->setName('thumbnail:recreate')
			->setDescription($this->user->lang('CLI_DESCRIPTION_THUMBNAIL_RECREATE'))
		;
	}

	/**
	* Executes the command thumbnail:recreate.
	*
	* This command is a "macro" to execute thumbnail:delete and then thumbnail:generate.
	*
	* @param InputInterface $input The input stream used to get the argument and verboe option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	*
	* @return int 0 if all is ok, 1 if a thumbnail couldn't be deleted.
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$command = $this->getApplication()->find('thumbnail:delete');

		$parameters = [];

		if ($input->getOption('verbose'))
		{
			$parameters['-' . str_repeat('v', $output->getVerbosity() - 1)] = true;
		}

		$this->getApplication()->setAutoExit(false);

		$input_delete = new ArrayInput($parameters);
		$return = $command->run($input_delete, $output);

		if ($return === symfony_command::SUCCESS)
		{
			$command = $this->getApplication()->find('thumbnail:generate');

			$input_create = new ArrayInput([]);
			$return = $command->run($input_create, $output);
		}

		$this->getApplication()->setAutoExit(true);

		return $return;
	}
}
