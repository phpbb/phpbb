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

namespace phpbb\install\module\update_database\task;

use phpbb\db\migration\exception;
use phpbb\db\output_handler\installer_migrator_output_handler;
use phpbb\db\output_handler\log_wrapper_migrator_output_handler;
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\task_base;

/**
 * Database updater task
 */
class update extends task_base
{
	/**
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\extension\manager
	 */
	protected $extension_manager;

	/**
	 * @var \phpbb\filesystem\filesystem
	 */
	protected $filesystem;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $installer_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\log\log
	 */
	protected $log;

	/**
	 * @var \phpbb\db\migrator
	 */
	protected $migrator;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\container_factory				$container
	 * @param \phpbb\filesystem\filesystem							$filesystem
	 * @param \phpbb\install\helper\config							$installer_config
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler
	 * @param \phpbb\language\language								$language
	 * @param string												$phpbb_root_path
	 */
	public function __construct(\phpbb\install\helper\container_factory $container, \phpbb\filesystem\filesystem $filesystem, \phpbb\install\helper\config $installer_config, \phpbb\install\helper\iohandler\iohandler_interface $iohandler, \phpbb\language\language $language, $phpbb_root_path)
	{
		$this->filesystem			= $filesystem;
		$this->installer_config		= $installer_config;
		$this->iohandler			= $iohandler;
		$this->language				= $language;
		$this->phpbb_root_path		= $phpbb_root_path;

		$this->cache				= $container->get('cache.driver');
		$this->config				= $container->get('config');
		$this->extension_manager	= $container->get('ext.manager');
		$this->log					= $container->get('log');
		$this->migrator				= $container->get('migrator');
		$this->user					= $container->get('user');

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->language->add_lang('migrator');

		if (!isset($this->config['version_update_from']))
		{
			$this->config->set('version_update_from', $this->config['version']);
		}

		$original_version = $this->config['version_update_from'];

		$this->migrator->set_output_handler(
			new log_wrapper_migrator_output_handler(
				$this->language,
				new installer_migrator_output_handler($this->iohandler),
				$this->phpbb_root_path . 'store/migrations_' . time() . '.log',
				$this->filesystem
			)
		);

		$this->migrator->create_migrations_table();

		$migrations = $this->extension_manager
			->get_finder()
			->core_path('phpbb/db/migration/data/')
			->extension_directory('/migrations')
			->get_classes();

		$this->migrator->set_migrations($migrations);

		$migration_step_count = $this->installer_config->get('database_update_migration_steps', -1);
		if ($migration_step_count < 0)
		{
			$migration_step_count = count($this->migrator->get_installable_migrations()) * 2;
			$this->installer_config->set('database_update_migration_steps', $migration_step_count);
		}

		$progress_count = $this->installer_config->get('database_update_count', 0);
		$restart_progress_bar = ($progress_count === 0); // Only "restart" when the update runs for the first time
		$this->iohandler->set_task_count($migration_step_count, $restart_progress_bar);
		$this->installer_config->set_task_progress_count($migration_step_count);

		while (!$this->migrator->finished())
		{
			try
			{
				$this->migrator->update();
				$progress_count++;

				$last_run_migration = $this->migrator->get_last_run_migration();
				if (isset($last_run_migration['effectively_installed']) && $last_run_migration['effectively_installed'])
				{
					// We skipped two step, so increment $progress_count by another one
					$progress_count++;
				}
				else if (($last_run_migration['task'] === 'process_schema_step' && !$last_run_migration['state']['migration_schema_done']) ||
					($last_run_migration['task'] === 'process_data_step' && !$last_run_migration['state']['migration_data_done']))
				{
					// We just run a step that wasn't counted yet so make it count
					$migration_step_count++;
				}

				$this->iohandler->set_task_count($migration_step_count);
				$this->installer_config->set_task_progress_count($migration_step_count);
				$this->iohandler->set_progress('STAGE_UPDATE_DATABASE', $progress_count);
			}
			catch (exception $e)
			{
				$msg = $e->getParameters();
				array_unshift($msg, $e->getMessage());

				$this->iohandler->add_error_message($msg);
				throw new user_interaction_required_exception();
			}

			if ($this->installer_config->get_time_remaining() <= 0 || $this->installer_config->get_memory_remaining() <= 0)
			{
				$this->installer_config->set('database_update_count', $progress_count);
				$this->installer_config->set('database_update_migration_steps', $migration_step_count);
				throw new resource_limit_reached_exception();
			}
		}

		if ($original_version !== $this->config['version'])
		{
			$this->log->add(
				'admin',
				(isset($this->user->data['user_id'])) ? $this->user->data['user_id'] : ANONYMOUS,
				$this->user->ip,
				'LOG_UPDATE_DATABASE',
				false,
				array(
					$original_version,
					$this->config['version']
				)
			);
		}

		$this->iohandler->add_success_message('INLINE_UPDATE_SUCCESSFUL');

		$this->cache->purge();

		$this->config->increment('assets_version', 1);
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return '';
	}
}
