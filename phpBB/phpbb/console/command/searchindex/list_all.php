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
use phpbb\di\service_collection;
use phpbb\language\language;
use phpbb\user;
use Symfony\Component\Console\Command\Command as symfony_command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class list_all extends command
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var service_collection */
	protected $search_backend_collection;

	/**
	 * Construct method
	 *
	 * @param config					$config
	 * @param language					$language
	 * @param service_collection		$search_backend_collection
	 * @param user						$user
	 */
	public function __construct(config $config, language $language, service_collection $search_backend_collection, user $user)
	{
		$this->config = $config;
		$this->language = $language;
		$this->search_backend_collection = $search_backend_collection;

		parent::__construct($user);
	}

	/**
	 * Sets the command name and description
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this->setName('searchindex:list')
			->setDescription($this->language->lang('CLI_DESCRIPTION_SEARCHINDEX_LIST'));
	}

	/**
	 * Executes the command searchindex:list
	 *
	 * List all search backends.
	 *
	 * @param InputInterface  $input  The input stream used to get the options
	 * @param OutputInterface $output The output stream, used to print messages
	 *
	 * @return int 0 if all is well, 1 if any errors occurred
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$search_backends = [];
		foreach ($this->search_backend_collection as $search_backend)
		{
			$name = $search_backend->get_type();
			$active = ($name === $this->config['search_type']) ? '(<comment>' . $this->language->lang('ACTIVE') . '</comment>) ' : '';
			$search_backends[] = '<info>' . $name . '</info> ' . $active .  $search_backend->get_name();

			if ($name === $this->config['search_type'] && !$search_backend->index_created())
			{
				$io->error($this->language->lang('CLI_SEARCHINDEX_ACTIVE_NOT_INDEXED'));
			}
		}

		$io->listing($search_backends);

		return symfony_command::SUCCESS;
	}
}
