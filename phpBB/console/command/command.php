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

namespace phpbb\console\command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class command extends \Symfony\Component\Console\Command\Command
{
	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\user $user User instance (mostly for translation)
	*/
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
		parent::__construct();
	}

	/**
	 * Create a styled progress bar
	 *
	 * @param int             $max     Max value for the progress bar
	 * @param SymfonyStyle    $io      Symfony style output decorator
	 * @param OutputInterface $output  The output stream, used to print messages
	 * @param bool            $message Should we display message output under the progress bar?
	 * @return ProgressBar
	 */
	public function create_progress_bar($max, SymfonyStyle $io, OutputInterface $output, $message = false)
	{
		$progress = $io->createProgressBar($max);
		if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE)
		{
			$progress->setFormat('<info>[%percent:3s%%]</info> %message%');
			$progress->setOverwrite(false);
		}
		else if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE)
		{
			$progress->setFormat('<info>[%current:s%/%max:s%]</info><comment>[%elapsed%/%estimated%][%memory%]</comment> %message%');
			$progress->setOverwrite(false);
		}
		else
		{
			$io->newLine(2);
			$progress->setFormat(
				"    %current:s%/%max:s% %bar%  %percent:3s%%\n" .
				"        " . ($message ? '%message%' : '                ') . " %elapsed:6s%/%estimated:-6s% %memory:6s%\n");
			$progress->setBarWidth(60);
		}

		if (!defined('PHP_WINDOWS_VERSION_BUILD'))
		{
			$progress->setEmptyBarCharacter('░'); // light shade character \u2591
			$progress->setProgressCharacter('');
			$progress->setBarCharacter('▓'); // dark shade character \u2593
		}

		return $progress;
	}
}
