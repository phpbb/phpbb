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

use phpbb\console\command\command;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\posting\post_helper;
use phpbb\search\exception\no_search_backend_found_exception;
use phpbb\search\search_backend_factory;
use phpbb\search\state_helper;
use phpbb\user;
use Symfony\Component\Console\Command\Command as symfony_command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class create extends command
{
	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var post_helper */
	protected $post_helper;

	/** @var search_backend_factory */
	protected $search_backend_factory;

	/** @var state_helper */
	protected $state_helper;

	/**
	 * Construct method
	 *
	 * @param language					$language
	 * @param log						$log
	 * @param post_helper				$post_helper
	 * @param search_backend_factory	$search_backend_factory
	 * @param state_helper				$state_helper
	 * @param user						$user
	 */
	public function __construct(language $language, log $log, post_helper $post_helper, search_backend_factory $search_backend_factory, state_helper $state_helper, user $user)
	{
		$this->language = $language;
		$this->log = $log;
		$this->post_helper = $post_helper;
		$this->search_backend_factory = $search_backend_factory;
		$this->state_helper = $state_helper;

		$this->language->add_lang(array('acp/common', 'acp/search'));

		parent::__construct($user);
	}

	/**
	 * Sets the command name and description
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('searchindex:create')
			->setDescription($this->language->lang('CLI_DESCRIPTION_SEARCHINDEX_CREATE'))
			->addArgument(
				'search-backend',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_SEARCHINDEX_SEARCH_BACKEND_NAME')
			);
	}

	/**
	 * Executes the command searchindex:create
	 *
	 * Create search index
	 *
	 * @param InputInterface	$input  The input stream used to get the options
	 * @param OutputInterface	$output The output stream, used to print messages
	 *
	 * @return int 0 if all is well, 1 if any errors occurred
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
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
			return symfony_command::FAILURE;
		}

		if ($this->state_helper->is_action_in_progress())
		{
			$io->error($this->language->lang('CLI_SEARCHINDEX_ACTION_IN_PROGRESS', $search_backend));
			return symfony_command::FAILURE;
		}

		if (!$search->is_available())
		{
			$io->error($this->language->lang('CLI_SEARCHINDEX_BACKEND_NOT_AVAILABLE', $search_backend));
			return symfony_command::FAILURE;
		}

		try
		{
			$progress = $this->create_progress_bar($this->post_helper->get_max_post_id(), $io, $output, true);
			$progress->setMessage('');
			$progress->start();

			$this->state_helper->init($search->get_type(), 'create');

			$counter = 0;
			while (($status = $search->create_index($counter)) !== null)
			{
				$this->state_helper->update_counter($status['post_counter']);

				$progress->setProgress($status['post_counter']);
				$progress->setMessage($this->language->lang('CLI_ROWS_PER_SECOND', round($status['rows_per_second'], 2)));
			}

			$progress->finish();

			$io->newLine(2);
		}
		catch (\Exception $e)
		{
			$this->state_helper->clear_state(); // Unexpected error, cancel action
			$io->error($e->getMessage()); // Show also exception message like in acp
			$io->error($this->language->lang('CLI_SEARCHINDEX_CREATE_FAILURE', $name));
			return symfony_command::FAILURE;
		}

		$search->tidy();

		$this->state_helper->clear_state();

		$this->log->add('admin', ANONYMOUS, '', 'LOG_SEARCH_INDEX_CREATED', false, array($name));
		$io->success($this->language->lang('CLI_SEARCHINDEX_CREATE_SUCCESS', $name));

		return symfony_command::SUCCESS;
	}
}
