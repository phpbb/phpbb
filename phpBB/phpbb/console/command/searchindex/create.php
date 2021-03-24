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

namespace phpbb\console\command\searchindex;

use phpbb\config\config;
use phpbb\console\command\command;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\search\exception\no_search_backend_found_exception;
use phpbb\search\search_backend_factory;
use phpbb\user;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class create extends command
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var search_backend_factory */
	protected $search_backend_factory;

	/**
	 * Construct method
	 *
	 * @param config					$config
	 * @param language					$language
	 * @param log						$log
	 * @param search_backend_factory	$search_backend_factory
	 * @param user						$user
	 */
	public function __construct(config $config, language $language, log $log, search_backend_factory $search_backend_factory, user $user)
	{
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->search_backend_factory = $search_backend_factory;

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
			->setName('searchindex:create')
			->setDescription($this->language->lang('CLI_DESCRIPTION_SEARCHINDEX_CREATE'))
			->addArgument(
				'search-backend',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_SEARCHINDEX_SEARCH_BACKEND_NAME')
			)
		;
	}

	/**
	 * Executes the command searchindex:create
	 *
	 * Create search index
	 *
	 * @param InputInterface  $input  The input stream used to get the options
	 * @param OutputInterface $output The output stream, used to print messages
	 *
	 * @return int 0 if all is well, 1 if any errors occurred
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$io->section($this->language->lang('CLI_DESCRIPTION_SEARCHINDEX_CREATE'));

		$search_backend = $input->getArgument('search-backend');

		try
		{
			$search = $this->search_backend_factory->get($search_backend);
			$name = $search->get_name();
		}
		catch (no_search_backend_found_exception $e)
		{
			$io->error($this->language->lang('CLI_SEARCHINDEX_BACKEND_NOT_FOUND', $search_backend));
			return command::FAILURE;
		}

		try
		{
			$progress = $this->create_progress_bar(1, $io, $output, true);
			$progress->setMessage('');
			$progress->start();

			$counter = 0;
			while (($status = $search->create_index($counter)) !== null)
			{
				$progress->setMaxSteps($status['max_post_id']);
				$progress->setProgress($status['post_counter']);
				$progress->setMessage(round($status['rows_per_second'], 2) . ' rows/s');
			}

			$progress->finish();

			$io->newLine(2);
		}
		catch (\Exception $e)
		{
			$io->error($this->language->lang('CLI_SEARCHINDEX_CREATE_FAILURE', $name));
			return command::FAILURE;
		}

		$this->log->add('admin', ANONYMOUS, '', 'LOG_SEARCH_INDEX_CREATED', false, array($name));
		$io->success($this->language->lang('CLI_SEARCHINDEX_CREATE_SUCCESS', $name));

		return command::SUCCESS;
	}
}
