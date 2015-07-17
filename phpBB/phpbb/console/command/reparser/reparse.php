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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class reparse extends \phpbb\console\command\command
{
	/**
	* @var \phpbb\config\db_text
	*/
	protected $config_text;

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
	* @var \phpbb\di\service_collection
	*/
	protected $reparsers;

	/**
	* @var array Reparser names as keys, and their last $current ID as values
	*/
	protected $resume_data;

	/**
	* Constructor
	*
	* @param \phpbb\user $user
	* @param \phpbb\di\service_collection $reparsers
	* @param \phpbb\config\db_text $config_text
	*/
	public function __construct(\phpbb\user $user, \phpbb\di\service_collection $reparsers, \phpbb\config\db_text $config_text)
	{
		require_once __DIR__ . '/../../../../includes/functions_content.php';

		$this->config_text = $config_text;
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
	* Create a styled progress bar
	*
	* @param  integer $max Max value for the progress bar
	* @return \Symfony\Component\Console\Helper\ProgressBar
	*/
	protected function create_progress_bar($max)
	{
		$progress = $this->io->createProgressBar($max);
		if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE)
		{
			$progress->setFormat('<info>[%percent:3s%%]</info> %message%');
			$progress->setOverwrite(false);
		}
		else if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE)
		{
			$progress->setFormat('<info>[%current:s%/%max:s%]</info><comment>[%elapsed%/%estimated%][%memory%]</comment> %message%');
			$progress->setOverwrite(false);
		}
		else
		{
			$this->io->newLine(2);
			$progress->setFormat(
				"    %current:s%/%max:s% %bar%  %percent:3s%%\n" .
				"        %message% %elapsed:6s%/%estimated:-6s% %memory:6s%\n");
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
		$this->load_resume_data();

		$name = $input->getArgument('reparser-name');
		if (isset($name))
		{
			// Allow "post_text" to be an alias for "text_reparser.post_text"
			if (!isset($this->reparsers[$name]))
			{
				$name = 'text_reparser.' . $name;
			}
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

		return 0;
	}

	/**
	* Get an option value, adjusted for given reparser
	*
	* Will use the last saved value if --resume is set and the option was not specified
	* on the command line
	*
	* @param  string  $reparser_name Reparser name
	* @param  string  $option_name   Option name
	* @return integer
	*/
	protected function get_option($reparser_name, $option_name)
	{
		// Return the option from the resume_data if applicable
		if ($this->input->getOption('resume') && isset($this->resume_data[$reparser_name][$option_name]) && !$this->input->hasParameterOption('--' . $option_name))
		{
			return $this->resume_data[$reparser_name][$option_name];
		}

		$value = $this->input->getOption($option_name);

		// range-max has no default value, it must be computed for each reparser
		if ($option_name === 'range-max' && $value === null)
		{
			$value = $this->reparsers[$reparser_name]->get_max_id();
		}

		return $value;
	}

	/**
	* Load the resume data from the database
	*/
	protected function load_resume_data()
	{
		$resume_data = $this->config_text->get('reparser_resume');
		$this->resume_data = (empty($resume_data)) ? array() : unserialize($resume_data);
	}

	/**
	* Reparse all text handled by given reparser within given range
	*
	* @param string $name Reparser name
	*/
	protected function reparse($name)
	{
		$reparser = $this->reparsers[$name];
		if ($this->input->getOption('dry-run'))
		{
			$reparser->disable_save();
		}
		else
		{
			$reparser->enable_save();
		}

		// Start at range-max if specified or at the highest ID otherwise
		$max  = $this->get_option($name, 'range-max');
		$min  = $this->get_option($name, 'range-min');
		$size = $this->get_option($name, 'range-size');

		if ($max < $min)
		{
			return;
		}

		$this->io->section($this->user->lang('CLI_REPARSER_REPARSE_REPARSING', preg_replace('(^text_reparser\\.)', '', $name), $min, $max));

		$progress = $this->create_progress_bar($max);
		$progress->setMessage($this->user->lang('CLI_REPARSER_REPARSE_REPARSING_START', preg_replace('(^text_reparser\\.)', '', $name)));
		$progress->start();

		// Start from $max and decrement $current by $size until we reach $min
		$current = $max;
		while ($current >= $min)
		{
			$start = max($min, $current + 1 - $size);
			$end   = max($min, $current);

			$progress->setMessage($this->user->lang('CLI_REPARSER_REPARSE_REPARSING', preg_replace('(^text_reparser\\.)', '', $name), $start, $end));
			$reparser->reparse_range($start, $end);

			$current = $start - 1;
			$progress->setProgress($max + 1 - $start);

			$this->update_resume_data($name, $current);
		}
		$progress->finish();

		$this->io->newLine(2);
	}

	/**
	* Save the resume data to the database
	*/
	protected function save_resume_data()
	{
		$this->config_text->set('reparser_resume', serialize($this->resume_data));
	}

	/**
	* Save the resume data to the database
	*
	* @param string $name    Reparser name
	* @param string $current Current ID
	*/
	protected function update_resume_data($name, $current)
	{
		$this->resume_data[$name] = array(
			'range-min'  => $this->get_option($name, 'range-min'),
			'range-max'  => $current,
			'range-size' => $this->get_option($name, 'range-size'),
		);
		$this->save_resume_data();
	}
}
