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

namespace phpbb\install\module\update_filesystem\task;

use phpbb\exception\runtime_exception;
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\file_updater\factory;
use phpbb\install\helper\file_updater\file_updater_interface;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\helper\update_helper;
use phpbb\install\task_base;

/**
 * File updater task
 */
class update_files extends task_base
{
	/**
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * @var config
	 */
	protected $installer_config;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var factory
	 */
	protected $factory;

	/**
	 * @var file_updater_interface
	 */
	protected $file_updater;

	/**
	 * @var update_helper
	 */
	protected $update_helper;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param container_factory		$container
	 * @param config				$config
	 * @param iohandler_interface	$iohandler
	 * @param factory				$file_updater_factory
	 * @param update_helper			$update_helper
	 * @param string				$phpbb_root_path
	 */
	public function __construct(container_factory $container, config $config, iohandler_interface $iohandler, factory $file_updater_factory, update_helper $update_helper, $phpbb_root_path)
	{
		$this->factory			= $file_updater_factory;
		$this->installer_config	= $config;
		$this->iohandler		= $iohandler;
		$this->update_helper	= $update_helper;
		$this->phpbb_root_path	= $phpbb_root_path;

		$this->cache			= $container->get('cache.driver');
		$this->file_updater		= null;

		parent::__construct(false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_requirements()
	{
		return $this->installer_config->get('do_update_files', false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$new_path = $this->update_helper->get_path_to_new_update_files();

		$file_update_info = $this->installer_config->get('update_files', array());

		$update_type_progress = $this->installer_config->get('file_updater_type_progress', '');
		$update_elem_progress = $this->installer_config->get('file_updater_elem_progress', '');
		$type_progress_found = false;
		$elem_progress_found = false;

		// Progress bar
		$task_count = 0;
		foreach ($file_update_info as $sub_array)
		{
			$task_count += count($sub_array);
		}

		// Everything is up to date, so just continue
		if ($task_count === 0)
		{
			return;
		}

		$progress_count = $this->installer_config->get('file_update_progress_count', 0);
		$this->iohandler->set_task_count($task_count, true);
		$this->iohandler->set_progress('UPDATE_UPDATING_FILES', 0);

		$this->file_updater = $this->get_file_updater();

		// File updater fallback logic
		try
		{
			// Update files
			foreach ($file_update_info as $type => $file_update_vector)
			{
				if (!$type_progress_found)
				{
					if ($type === $update_type_progress || empty($update_elem_progress))
					{
						$type_progress_found = true;
					}
					else
					{
						continue;
					}
				}

				foreach ($file_update_vector as $path)
				{
					if (!$elem_progress_found)
					{
						if ($path === $update_elem_progress || empty($update_elem_progress))
						{
							$elem_progress_found = true;
						}
						else
						{
							continue;
						}
					}

					switch ($type)
					{
						case 'delete':
							$this->file_updater->delete_file($path);
						break;
						case 'new':
							$this->file_updater->create_new_file($path, $new_path . $path);
						break;
						case 'update_without_diff':
							$this->file_updater->update_file($path, $new_path . $path);
						break;
						case 'update_with_diff':
							$this->file_updater->update_file(
								$path,
								base64_decode($this->cache->get('_file_' . md5($path))),
								true
							);
						break;
					}

					// Save progress
					$this->installer_config->set('file_updater_type_progress', $type);
					$this->installer_config->set('file_updater_elem_progress', $path);
					$progress_count++;
					$this->iohandler->set_progress('UPDATE_UPDATING_FILES', $progress_count);

					if ($this->installer_config->get_time_remaining() <= 0 || $this->installer_config->get_memory_remaining() <= 0)
					{
						// Request refresh
						throw new resource_limit_reached_exception();
					}
				}
			}

			$this->iohandler->finish_progress('UPDATE_UPDATING_FILES');
		}
		catch (runtime_exception $e)
		{
			if ($e instanceof resource_limit_reached_exception)
			{
				throw new resource_limit_reached_exception();
			}

			$current_method = $this->installer_config->get('file_update_method', '');

			// File updater failed, try to fallback to download file update mode
			if ($current_method !== 'compression')
			{
				$this->iohandler->add_warning_message(array(
					'UPDATE_FILE_UPDATER_HAS_FAILED',
					$current_method,
					'compression'
				));
				$this->installer_config->set('file_update_method', 'compression');

				// We only want a simple refresh here
				throw new resource_limit_reached_exception();
			}
			else
			{
				// Nowhere to fallback to :(
				// Due to the way the installer handles fatal errors, we need to throw a low level exception
				throw new runtime_exception('UPDATE_FILE_UPDATERS_HAVE_FAILED');
			}
		}

		$file_updater_method = $this->installer_config->get('file_update_method', '');
		if ($file_updater_method === 'compression' || $file_updater_method === 'ftp')
		{
			$this->file_updater->close();
		}
	}

	/**
	 * Get file updater
	 *
	 * @param null|string	$file_updater_method	Name of the file updater to use
	 *
	 * @return file_updater_interface	File updater
	 */
	protected function get_file_updater($file_updater_method = null)
	{
		$file_updater_method = ($file_updater_method === null) ? $this->installer_config->get('file_update_method', '') : $file_updater_method;

		if ($file_updater_method === 'compression')
		{
			$compression_method = $this->installer_config->get('file_update_compression', '');

			/** @var \phpbb\install\helper\file_updater\compression_file_updater $file_updater */
			$file_updater = $this->factory->get('compression');
			$archive_path = $file_updater->init($compression_method);
			$this->installer_config->set('update_file_archive', $archive_path);
		}
		else if ($file_updater_method === 'ftp')
		{
			/** @var \phpbb\install\helper\file_updater\ftp_file_updater $file_updater */
			$file_updater = $this->factory->get('ftp');
			$file_updater->init(
				$this->installer_config->get('ftp_method', ''),
				$this->installer_config->get('ftp_host', ''),
				$this->installer_config->get('ftp_user', ''),
				$this->installer_config->get('ftp_pass', ''),
				$this->installer_config->get('ftp_path', ''),
				$this->installer_config->get('ftp_port', 0),
				$this->installer_config->get('ftp_timeout', 10)
			);
		}
		else
		{
			/** @var file_updater_interface $file_updater */
			$file_updater = $this->factory->get('direct_file');
		}

		return $file_updater;
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
