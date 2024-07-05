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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DoctrineException;
use phpbb\config\db;
use phpbb\db\driver\driver_interface;
use phpbb\install\database_task;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\sequential_task;

/**
 * Installs extensions that exist in ext folder upon install
 */
class install_extensions extends database_task
{
	use sequential_task;

	/**
	 * @var config
	 */
	protected $install_config;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\log\log_interface
	 */
	protected $log;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/** @var string Extension table */
	protected $extension_table;

	/** @var Connection */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param container_factory		$container
	 * @param config				$install_config
	 * @param database				$db_helper
	 * @param iohandler_interface	$iohandler
	 */
	public function __construct(
		container_factory $container,
		config $install_config,
		database $db_helper,
		iohandler_interface $iohandler)
	{
		$this->install_config	= $install_config;
		$this->iohandler		= $iohandler;
		$this->extension_table	= $container->get_parameter('tables.ext');
		$this->db				= self::get_doctrine_connection($db_helper, $install_config);

		$this->log					= $container->get('log');
		$this->config				= $container->get('config');
		$this->user					= $container->get('user');
		$this->extension_manager 	= $container->get('ext.manager');

		/** @var driver_interface $db */
		$db = $container->get('dbal.conn');

		/** @var \phpbb\cache\driver\driver_interface $cache */
		$cache = $container->get('cache.driver');
		$cache->destroy('config');

		global $config;
		$config = new db(
			$db,
			$cache,
			$container->get_parameter('tables.config')
		);

		// Make sure asset version exists in config. Otherwise we might try to
		// insert the assets_version setting into the database and cause a
		// duplicate entry error.
		if (!$this->config->offsetExists('assets_version'))
		{
			$this->config->offsetSet('assets_version', 0);
		}

		parent::__construct(
			$this->db,
			$this->iohandler,
			true
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->user->session_begin();
		$this->user->setup(array('common', 'acp/common', 'cli'));
		$all_available_extensions = $this->extension_manager->all_available();
		$this->execute($this->install_config, $all_available_extensions);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute_step($key, $value) : void
	{
		$install_extensions = $this->iohandler->get_input('install-extensions', array());

		if (!empty($install_extensions) && $install_extensions !== ['all'] && !in_array($key, $install_extensions))
		{
			return;
		}

		try
		{
			$extension = $this->extension_manager->get_extension($key);

			if (!$extension->is_enableable())
			{
				$this->iohandler->add_log_message(array('CLI_EXTENSION_NOT_ENABLEABLE', $key));
				return;
			}

			$this->extension_manager->enable($key);
			$extensions = $this->get_extensions();

			if (isset($extensions[$key]) && $extensions[$key]['ext_active'])
			{
				// Create log
				$this->log->add('admin', ANONYMOUS, $this->user->ip, 'LOG_EXT_ENABLE', time(), array($key));
				$this->iohandler->add_success_message(array('CLI_EXTENSION_ENABLE_SUCCESS', $key));
			}
			else
			{
				$this->iohandler->add_log_message(array('CLI_EXTENSION_ENABLE_FAILURE', $key));
			}
		}
		catch (\Exception $e)
		{
			// Add fail log and continue
			$this->iohandler->add_log_message(array('CLI_EXTENSION_ENABLE_FAILURE', $key));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_step_count()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return 'TASK_INSTALL_EXTENSIONS';
	}

	/**
	 * Get extensions from database
	 *
	 * @return array List of extensions
	 */
	private function get_extensions() : array
	{
		try
		{
			$extensions_row = $this->db->fetchAllAssociative('SELECT * FROM ' . $this->extension_table);
		}
		catch (DoctrineException $e)
		{
			$this->iohandler->add_error_message('INST_ERR_DB', $e->getMessage());
			return [];
		}

		$extensions = [];
		foreach ($extensions_row as $extension)
		{
			$extensions[$extension['ext_name']] = $extension;
		}

		ksort($extensions);

		return $extensions;
	}
}
