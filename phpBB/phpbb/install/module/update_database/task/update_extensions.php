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

use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\helper\update_helper;
use phpbb\install\task_base;
use Symfony\Component\Finder\Finder;

/**
 * Installs extensions that exist in ext folder upon install
 */
class update_extensions extends task_base
{
	/**
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * @var config
	 */
	protected $install_config;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/** @var update_helper */
	protected $update_helper;

	/**
	 * @var \phpbb\config\db
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

	/** @var Finder */
	protected $finder;

	/** @var string Extension table */
	protected $extension_table;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	 * @var array	List of default extensions to update, grouped by version
	 *				they were added
	 */
	static public $default_extensions_update = [
		'3.2.0-RC2' => ['phpbb/viglink']
	];

	/**
	 * Constructor
	 *
	 * @param container_factory			$container
	 * @param config					$install_config
	 * @param iohandler_interface		$iohandler
	 * @param $update_helper			$update_helper
	 * @param string					$phpbb_root_path phpBB root path
	 */
	public function __construct(container_factory $container, config $install_config, iohandler_interface $iohandler, update_helper $update_helper, $phpbb_root_path)
	{
		$this->install_config	= $install_config;
		$this->iohandler		= $iohandler;
		$this->extension_table = $container->get_parameter('tables.ext');

		$this->log				= $container->get('log');
		$this->user				= $container->get('user');
		$this->extension_manager = $container->get('ext.manager');
		$this->cache				= $container->get('cache.driver');
		$this->config			= $container->get('config');
		$this->db				= $container->get('dbal.conn');
		$this->update_helper = $update_helper;
		$this->finder = new Finder();
		$this->finder->in($phpbb_root_path . 'ext/')
			->ignoreUnreadableDirs()
			->depth('< 3')
			->files()
			->name('composer.json');

		// Make sure asset version exists in config. Otherwise we might try to
		// insert the assets_version setting into the database and cause a
		// duplicate entry error.
		if (!isset($this->config['assets_version']))
		{
			$this->config['assets_version'] = 0;
		}

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->user->session_begin();
		$this->user->setup(array('common', 'acp/common', 'cli'));

		$update_info = $this->install_config->get('update_info_unprocessed', []);
		$version_from = !empty($update_info) ? $update_info['version']['from'] : $this->config['version_update_from'];

		if (!empty($version_from))
		{
			$update_extensions = $this->iohandler->get_input('update-extensions', []);

			// Create list of default extensions that need to be enabled in update
			$default_update_extensions = [];
			foreach (self::$default_extensions_update as $version => $extensions)
			{
				if ($this->update_helper->phpbb_version_compare($version_from, $version, '<'))
				{
					$default_update_extensions = array_merge($default_update_extensions, $extensions);
				}
			}

			$all_available_extensions = $this->extension_manager->all_available();
			$i = $this->install_config->get('update_extensions_index', 0);
			$available_extensions = array_slice($all_available_extensions, $i);

			// Update available extensions
			foreach ($available_extensions as $ext_name => $ext_path)
			{
				// Update extensions if:
				//	1) Extension is currently enabled
				//	2) Extension was implicitly defined as needing an update
				//	3) Extension was newly added as default phpBB extension in
				//		this update and should be enabled by default.
				if ($this->extension_manager->is_enabled($ext_name) ||
					in_array($ext_name, $update_extensions) ||
					in_array($ext_name, $default_update_extensions)
				)
				{
					try
					{
						$extension_enabled = $this->extension_manager->is_enabled($ext_name);
						if ($extension_enabled)
						{
							$this->extension_manager->disable($ext_name);
						}
						$this->extension_manager->enable($ext_name);
						$extensions = $this->get_extensions();

						if (isset($extensions[$ext_name]) && $extensions[$ext_name]['ext_active'])
						{
							// Create log
							$this->log->add('admin', ANONYMOUS, '', 'LOG_EXT_UPDATE', time(), array($ext_name));
							$this->iohandler->add_success_message(array('CLI_EXTENSION_UPDATE_SUCCESS', $ext_name));
						}
						else
						{
							$this->iohandler->add_log_message('CLI_EXTENSION_UPDATE_FAILURE', array($ext_name));
						}

						// Disable extensions if it was disabled by the admin before
						if (!$extension_enabled && !in_array($ext_name, $default_update_extensions))
						{
							$this->extension_manager->disable($ext_name);
						}
					}
					catch (\Exception $e)
					{
						// Add fail log and continue
						$this->iohandler->add_log_message('CLI_EXTENSION_UPDATE_FAILURE', array($ext_name));
					}
				}

				$i++;

				// Stop execution if resource limit is reached
				if ($this->install_config->get_time_remaining() <= 0 || $this->install_config->get_memory_remaining() <= 0)
				{
					break;
				}
			}

			$this->install_config->set('update_extensions_index', $i);

			if ($i < count($all_available_extensions))
			{
				throw new resource_limit_reached_exception();
			}
		}

		$this->config->delete('version_update_from');

		$this->cache->purge();

		$this->config->increment('assets_version', 1);
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
		return 'TASK_UPDATE_EXTENSIONS';
	}

	/**
	 * Get extensions from database
	 *
	 * @return array List of extensions
	 */
	private function get_extensions()
	{
		$sql = 'SELECT *
			FROM ' . $this->extension_table;

		$result = $this->db->sql_query($sql);
		$extensions_row = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$extensions = array();

		foreach ($extensions_row as $extension)
		{
			$extensions[$extension['ext_name']] = $extension;
		}

		ksort($extensions);

		return $extensions;
	}
}
