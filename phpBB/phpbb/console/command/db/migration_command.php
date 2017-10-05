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

abstract class migration_command extends \phpbb\console\command\command
{
	/** @var \phpbb\db\migrator */
	protected $migrator;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\cache\service */
	protected $cache;

	public function __construct(\phpbb\user $user, \phpbb\db\migrator $migrator, \phpbb\extension\manager $extension_manager, \phpbb\config\config $config, \phpbb\cache\service $cache)
	{
		$this->migrator = $migrator;
		$this->extension_manager = $extension_manager;
		$this->config = $config;
		$this->cache = $cache;
		parent::__construct($user);
	}

	protected function load_migrations()
	{
		$migrations = $this->extension_manager
			->get_finder()
			->core_path('phpbb/db/migration/data/')
			->extension_directory('/migrations')
			->get_classes();

		$this->migrator->set_migrations($migrations);

		return $this->migrator->get_migrations();
	}

	protected function finalise_update()
	{
		$this->cache->purge();
		$this->config->increment('assets_version', 1);
	}
}
