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

/**
 * Populates migrations
 */
class populate_migrations extends \phpbb\install\task_base
{
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
	 * @param \phpbb\install\helper\container_factory	$container	phpBB's DI contianer
	 */
	public function __construct(\phpbb\install\helper\container_factory $container)
	{
		$this->extension_manager	= $container->get('ext.manager');
		$this->migrator				= $container->get('migrator');
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$finder = $this->extension_manager->get_finder();

		$migrations = $finder
			->core_path('phpbb/db/migration/data/')
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
