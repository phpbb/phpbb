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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class migrate extends \phpbb\console\command\command
{
	/** @var \phpbb\db\migrator */
	protected $migrator;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\log\log */
	protected $log;

	function __construct(\phpbb\user $user, \phpbb\db\migrator $migrator, \phpbb\extension\manager $extension_manager, \phpbb\config\config $config, \phpbb\cache\service $cache, \phpbb\log\log $log)
	{
		$this->migrator = $migrator;
		$this->extension_manager = $extension_manager;
		$this->config = $config;
		$this->cache = $cache;
		$this->log = $log;
		parent::__construct($user);
		$this->user->add_lang(array('common', 'install', 'migrator'));
	}

	protected function configure()
	{
		$this
			->setName('db:migrate')
			->setDescription($this->user->lang('CLI_DESCRIPTION_DB_MIGRATE'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->migrator->create_migrations_table();

		$this->load_migrations();
		$orig_version = $this->config['version'];
		while (!$this->migrator->finished())
		{
			$migration_start_time = microtime(true);

			try
			{
				$this->migrator->update();
			}
			catch (\phpbb\db\migration\exception $e)
			{
				$output->writeln('<error>' . $e->getLocalisedMessage($this->user) . '</error>');
				$this->finalise_update();
				return 1;
			}

			$migration_stop_time = microtime(true) - $migration_start_time;

			$state = array_merge(
				array(
					'migration_schema_done' => false,
					'migration_data_done'	=> false,
				),
				$this->migrator->last_run_migration['state']
			);

			if (!empty($this->migrator->last_run_migration['effectively_installed']))
			{
				$msg = $this->user->lang('MIGRATION_EFFECTIVELY_INSTALLED', $this->migrator->last_run_migration['name']);
				$output->writeln("<comment>$msg</comment>");
			}
			else if ($this->migrator->last_run_migration['task'] == 'process_data_step' && $state['migration_data_done'])
			{
				$msg = $this->user->lang('MIGRATION_DATA_DONE', $this->migrator->last_run_migration['name'], $migration_stop_time);
				$output->writeln("<info>$msg</info>");
			}
			else if ($this->migrator->last_run_migration['task'] == 'process_data_step')
			{
				$output->writeln($this->user->lang('MIGRATION_DATA_IN_PROGRESS', $this->migrator->last_run_migration['name'], $migration_stop_time));
			}
			else if ($state['migration_schema_done'])
			{
				$msg = $this->user->lang('MIGRATION_SCHEMA_DONE', $this->migrator->last_run_migration['name'], $migration_stop_time);
				$output->writeln("<info>$msg</info>");
			}
		}

		if ($orig_version != $this->config['version'])
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_UPDATE_DATABASE', time(), array($orig_version, $this->config['version']));
		}

		$this->finalise_update();
		$output->writeln($this->user->lang['DATABASE_UPDATE_COMPLETE']);
	}

	protected function load_migrations()
	{
		$migrations = $this->extension_manager
			->get_finder()
			->core_path('phpbb/db/migration/data/')
			->extension_directory('/migrations')
			->get_classes();
		$this->migrator->set_migrations($migrations);
	}

	protected function finalise_update()
	{
		$this->cache->purge();
		$this->config->increment('assets_version', 1);
	}
}
