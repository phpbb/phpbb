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

use phpbb\exception\runtime_exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class reparse extends \phpbb\console\command\command
{
	/**
	* @var InputInterface
	*/
	protected $input;

	/**
	* @var SymfonyStyle
	*/
	protected $io;

	/**
	* @var OutputInterface
	*/
	protected $output;

	/**
	 * @var \phpbb\lock\db
	 */
	protected $reparse_lock;

	/**
	 * @var \phpbb\textreparser\manager
	 */
	protected $reparser_manager;

	/**
	* @var \phpbb\di\service_collection
	*/
	protected $reparsers;

	/**
	* @var array The reparser's last $current ID as values
	*/
	protected $resume_data;

	/**
	* Constructor
	*
	* @param \phpbb\user $user
	* @param \phpbb\lock\db $reparse_lock
	* @param \phpbb\textreparser\manager $reparser_manager
	* @param \phpbb\di\service_collection $reparsers
	*/
	public function __construct(\phpbb\user $user, \phpbb\lock\db $reparse_lock, \phpbb\textreparser\manager $reparser_manager, \phpbb\di\service_collection $reparsers)
	{
		require_once __DIR__ . '/../../../../includes/functions_content.php';

		$this->reparse_lock = $reparse_lock;
		$this->reparser_manager = $reparser_manager;
		$this->reparsers = $reparsers;
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
			->addOption(
				'dry-run',
				null,
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_OPT_DRY_RUN')
			)
			->addOption(
				'resume',
				null,
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_OPT_RESUME')
			)
			->addOption(
				'range-min',
				null,
				InputOption::VALUE_REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_OPT_RANGE_MIN'),
				1
			)
			->addOption(
				'range-max',
				null,
				InputOption::VALUE_REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_OPT_RANGE_MAX')
			)
			->addOption(
				'range-size',
				null,
				InputOption::VALUE_REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_REPARSER_REPARSE_OPT_RANGE_SIZE'),
				100
			);
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
		$this->input = $input;
		$this->output = $output;
		$this->io = new SymfonyStyle($input, $output);

		if (!$this->reparse_lock->acquire())
		{
			throw new runtime_exception('REPARSE_LOCK_ERROR', array(), null, 1);
		}

		$name = $input->getArgument('reparser-name');
		if ($name)
		{
			$name = $this->reparser_manager->find_reparser($name);
			$this->reparse($name);
		}
		else
		{
			foreach ($this->reparsers as $name => $service)
			{
				$this->reparse($name);
			}
		}

		$this->io->success($this->user->lang('CLI_REPARSER_REPARSE_SUCCESS'));

		$this->reparse_lock->release();

		return 0;
	}

	/**
	* Get an option value, adjusted for given reparser
	*
	* Will use the last saved value if --resume is set and the option was not specified
	* on the command line
	*
	* @param  string  $option_name   Option name
	* @return integer
	*/
	protected function get_option($option_name)
	{
		// Return the option from the resume_data if applicable
		if ($this->input->getOption('resume') && isset($this->resume_data[$option_name]) && !$this->input->hasParameterOption('--' . $option_name))
		{
			return $this->resume_data[$option_name];
		}

		return $this->input->getOption($option_name);
	}

	/**
	* Reparse all text handled by given reparser within given range
	*
	* @param string $name Reparser service name
	*/
	protected function reparse($name)
	{
		$reparser = $this->reparsers[$name];
		$this->resume_data = $this->reparser_manager->get_resume_data($name);
		if ($this->input->getOption('dry-run'))
		{
			$reparser->disable_save();
		}
		else
		{
			$reparser->enable_save();
		}

		// Start at range-max if specified or at the highest ID otherwise
		$max  = $this->get_option('range-max');
		$min  = $this->get_option('range-min');
		$size = $this->get_option('range-size');

		// range-max has no default value, it must be computed for each reparser
		if ($max === null)
		{
			$max = $reparser->get_max_id();
		}

		if ($max < $min)
		{
			return;
		}

		$this->io->section($this->user->lang('CLI_REPARSER_REPARSE_REPARSING', $reparser->get_name(), $min, $max));

		$progress = $this->create_progress_bar($max, $this->io, $this->output, true);
		$progress->setMessage($this->user->lang('CLI_REPARSER_REPARSE_REPARSING_START', $reparser->get_name()));
		$progress->start();

		// Start from $max and decrement $current by $size until we reach $min
		$current = $max;
		while ($current >= $min)
		{
			$start = max($min, $current + 1 - $size);
			$end   = max($min, $current);

			$progress->setMessage($this->user->lang('CLI_REPARSER_REPARSE_REPARSING', $reparser->get_name(), $start, $end));
			$reparser->reparse_range($start, $end);

			$current = $start - 1;
			$progress->setProgress($max + 1 - $start);

			$this->reparser_manager->update_resume_data($name, $min, $current, $size, !$this->input->getOption('dry-run'));
		}
		$progress->finish();

		$this->io->newLine(2);
	}
}
