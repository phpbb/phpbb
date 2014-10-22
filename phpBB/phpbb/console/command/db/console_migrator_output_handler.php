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

namespace phpbb\console\command\db;

use phpbb\user;
use phpbb\db\migrator_output_handler_interface;
use Symfony\Component\Console\Output\OutputInterface;

class console_migrator_output_handler implements migrator_output_handler_interface
{
	/**
	 * User object.
	 *
	 * @var user
	 */
	private $user;

	/**
	 * Console output object.
	 *
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * Constructor
	 *
	 * @param user				$user	User object
	 * @param OutputInterface	$output	Console output object
	 */
	public function __construct(user $user, OutputInterface $output)
	{
		$this->user = $user;
		$this->output = $output;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($message, $verbosity)
	{
		if ($verbosity <= $this->output->getVerbosity())
		{
			$translated_message = call_user_func_array(array($this->user, 'lang'), $message);

			if ($verbosity === migrator_output_handler_interface::VERBOSITY_NORMAL)
			{
				$translated_message = '<info>' . $translated_message . '</info>';
			}
			else if ($verbosity === migrator_output_handler_interface::VERBOSITY_VERBOSE)
			{
				$translated_message = '<comment>' . $translated_message . '</comment>';
			}

			$this->output->writeln($translated_message);
		}
	}
}
