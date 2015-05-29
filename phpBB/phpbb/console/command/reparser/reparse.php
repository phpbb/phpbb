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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class reparse extends \phpbb\console\command\command
{
	/**
	* @var \phpbb\textreparser\reparser_collection
	*/
	protected $reparser_collection;

	/**
	* Constructor
	*
	* @param \phpbb\user $user
	* @param \phpbb\textreparser\reparser_collection $reparser_collection
	*/
	public function __construct(\phpbb\user $user, \phpbb\textreparser\reparser_collection $reparser_collection)
	{
		require_once __DIR__ . '/../../../../includes/functions_content.php';

		$this->reparser_collection = $reparser_collection;
		parent::__construct($user);
	}

	/**
	* Sets the command name and description
	*
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('reparser:reparse')
			->setDescription($this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE'))
			->addArgument('reparser-name', InputArgument::OPTIONAL, $this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_ARG_1'))
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
		$name = $input->getArgument('reparser-name');
		if (isset($name))
		{
			// Allow "post_text" to be an alias for "text_reparser.post_text"
			if (!isset($this->reparser_collection[$name]))
			{
				$name = 'text_reparser.' . $name;
			}
			$this->reparse($output, $name);
		}
		else
		{
			foreach ($this->reparser_collection as $name => $service)
			{
				$this->reparse($output, $name);
			}
		}

		return 0;
	}

	/**
	* Reparse all text handled by given reparser
	*
	* @param OutputInterface $output
	* @param string $name Reparser name
	* @return null
	*/
	protected function reparse(OutputInterface $output, $name)
	{
		$reparser = $this->reparser_collection[$name];
		$id = $reparser->get_max_id();
		$n = 100;
		while ($id > 0)
		{
			$start = max(0, $id + 1 - $n);
			$end   = $id;
			$output->writeln('<info>' . $this->user->lang('CLI_REPARSER_REPARSE_REPARSING', $name, $start, $end) . '</info>');
			$reparser->reparse_range($start, $end);
			$id -= $n;
		}
	}
}
