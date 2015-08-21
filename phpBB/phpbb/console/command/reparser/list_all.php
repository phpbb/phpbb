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

namespace phpbb\console\command\reparser;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class list_all extends \phpbb\console\command\command
{
	/**
	* @var string[] Names of the reparser services
	*/
	protected $reparser_names;

	/**
	* Constructor
	*
	* @param \phpbb\user $user
	* @param \phpbb\di\service_collection $reparsers
	*/
	public function __construct(\phpbb\user $user, \phpbb\di\service_collection $reparsers)
	{
		parent::__construct($user);
		$this->reparser_names = array();
		foreach ($reparsers as $name => $reparser)
		{
			// Store the names without the "text_reparser." prefix
			$this->reparser_names[] = preg_replace('(^text_reparser\\.)', '', $name);
		}
	}

	/**
	* Sets the command name and description
	*
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('reparser:list')
			->setDescription($this->user->lang('CLI_DESCRIPTION_REPARSER_LIST'))
		;
	}

	/**
	* Executes the command reparser:reparse
	*
	* @param InputInterface $input
	* @param OutputInterface $output
	* @return integer
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>' . implode(', ', $this->reparser_names) . '</info>');

		return 0;
	}
}
