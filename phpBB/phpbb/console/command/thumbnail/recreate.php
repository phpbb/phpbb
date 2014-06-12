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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class recreate  extends \phpbb\console\command\command
{
	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\user $user The user object (used to get language information)
	*/
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
		parent::__construct();
	}

	/**
	* Sets the command name and description
	*
	* @return null
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
	* @param InputInterface $input The input stream used to get the argument and verboe option.
	* @param OutputInterface $output The output stream, used for printing verbose-mode and error information.
	*
	* @return int 0 if all is ok, 1 if a thumbnail couldn't be deleted.
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$parameters = array(
			'command' => 'thumbnail:delete'
		);

		if ($input->getOption('verbose'))
		{
			$parameters['-v'] = true;
		}

		$this->getApplication()->setAutoExit(false);

		$input_delete = new ArrayInput($parameters);
		$return = $this->getApplication()->run($input_delete, $output);

		if ($return == 0)
		{
			$parameters['command'] = 'thumbnail:generate';

			$input_create = new ArrayInput($parameters);
			$return = $this->getApplication()->run($input_create, $output);
		}

		$this->getApplication()->setAutoExit(true);

		return $return;
	}
}
