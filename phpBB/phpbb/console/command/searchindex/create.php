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
				$this->user->lang('CLI_SEARCHINDEX_SEARCH_BACKEND_NAME')
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

		$search_backend = $input->getArgument('search-backend');
		$search = $this->search_backend_factory->get($search_backend);
		$name = $search->get_name();

		try
		{
			$counter = 0;
			while (($status = $search->create_index($counter)) !== null)
			{
				$this->config->set('search_indexing_state', implode(',', $this->state), true);

				$io->success($counter);
				$io->success(print_r($status, 1));
			}

			$search->tidy();
		}
		catch (\Exception $e)
		{
			$io->error($this->user->lang('CLI_SEARCHINDEX_CREATE_FAILURE', $name));
			return 1;
		}

		$this->log->add('admin', ANONYMOUS, '', 'LOG_SEARCH_INDEX_CREATED', false, array($name));
		$io->success($this->user->lang('CLI_SEARCHINDEX_CREATE_SUCCESS', $name));

		return 0;
	}
}
