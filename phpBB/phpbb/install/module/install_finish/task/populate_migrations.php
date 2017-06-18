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

namespace phpbb\install\module\install_finish\task;

use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;

/**
 * Populates migrations
 */
class populate_migrations extends \phpbb\install\task_base
{
	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var \phpbb\extension\manager
	 */
	protected $extension_manager;

	/**
	 * @var \phpbb\db\migrator
	 */
	protected $migrator;

	/**
	 * Constructor
	 *
	 * @param config			$config		Installer's config
	 * @param container_factory	$container	phpBB's DI contianer
	 */
	public function __construct(config $config, container_factory $container)
	{
		$this->config				= $config;
		$this->extension_manager	= $container->get('ext.manager');
		$this->migrator				= $container->get('migrator');

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		if (!$this->config->get('populate_migration_refresh_before', false))
		{
			if ($this->config->get_time_remaining() < 1)
			{
				$this->config->set('populate_migration_refresh_before', true);
				throw new resource_limit_reached_exception();
			}
		}

		$finder = $this->extension_manager->get_finder();

		$migrations = $finder
			->core_path('phpbb/db/migration/data/')
			->set_extensions(array())
			->get_classes();
		$this->migrator->populate_migrations($migrations);
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return 'TASK_POPULATE_MIGRATIONS';
	}
}
