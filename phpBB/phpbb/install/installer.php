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

namespace phpbb\install;

use phpbb\cache\driver\driver_interface;
use phpbb\di\ordered_service_collection;
use phpbb\install\exception\cannot_build_container_exception;
use phpbb\install\exception\installer_config_not_writable_exception;
use phpbb\install\exception\jump_to_restart_point_exception;
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\iohandler\ajax_iohandler;
use phpbb\install\helper\iohandler\cli_iohandler;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\path_helper;

class installer
{
	/**
	 * @var driver_interface
	 */
	protected $cache;

	/**
	 * @var container_factory
	 */
	protected $container_factory;

	/**
	 * @var config
	 */
	protected $install_config;

	/**
	 * @var ordered_service_collection
	 */
	protected $installer_modules;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var string
	 */
	protected $web_root;

	/**
	 * Stores the number of steps that a given module has
	 *
	 * @var array
	 */
	protected $module_step_count;

	/**
	 * @var bool
	 */
	protected $purge_cache_before;

	/**
	 * Constructor
	 *
	 * @param driver_interface	$cache			Cache service
	 * @param config			$config			Installer config handler
	 * @param path_helper		$path_helper	Path helper
	 * @param container_factory	$container		Container
	 */
	public function __construct(driver_interface $cache, config $config, path_helper $path_helper, container_factory $container)
	{
		$this->cache				= $cache;
		$this->install_config		= $config;
		$this->container_factory	= $container;
		$this->installer_modules	= null;
		$this->web_root				= $path_helper->get_web_root_path();
		$this->purge_cache_before	= false;
	}

	/**
	 * Sets modules to execute
	 *
	 * Note: The installer will run modules in the order they are set in
	 * the array.
	 *
	 * @param ordered_service_collection	$modules	Service collection of module service names
	 */
	public function set_modules(ordered_service_collection $modules)
	{
		$this->installer_modules = $modules;
	}

	/**
	 * Sets input-output handler objects
	 *
	 * @param iohandler_interface	$iohandler
	 */
	public function set_iohandler(iohandler_interface $iohandler)
	{
		$this->iohandler = $iohandler;
	}

	/**
	 * Sets whether to purge cache before the installation process
	 *
	 * @param bool	$purge_cache_before
	 */
	public function set_purge_cache_before($purge_cache_before)
	{
		$this->purge_cache_before = $purge_cache_before;
	}

	/**
	 * Run phpBB installer
	 */
	public function run()
	{
		if ($this->iohandler instanceof ajax_iohandler)
		{
			$this->iohandler->acquire_lock();
		}

		// Load install progress
		$this->install_config->load_config();

		if (!$this->install_config->get('cache_purged_before', false) && $this->purge_cache_before)
		{
			/** @var driver_interface $cache */
			$cache = $this->container_factory->get('cache.driver');
			$cache->purge();
			$this->install_config->set('cache_purged_before', true);
		}

		// Recover install progress
		$module_index = $this->recover_progress();

		// Variable used to check if the install process have been finished
		$install_finished	= false;
		$fail_cleanup		= false;
		$send_refresh		= false;

		// We are installing something, so the introduction stage can go now...
		$this->install_config->set_finished_navigation_stage(array('install', 0, 'introduction'));
		$this->iohandler->set_finished_stage_menu(array('install', 0, 'introduction'));

		if ($this->install_config->get_task_progress_count() === 0)
		{
			// Count all tasks in the current installer modules
			$step_count = 0;

			/** @var \phpbb\install\module_interface $module */
			foreach ($this->installer_modules as $name => $module)
			{
				$module_step_count = $module->get_step_count();
				$step_count += $module_step_count;
				$this->module_step_count[$name] = $module_step_count;
			}

			// Set task count
			$this->install_config->set_task_progress_count($step_count);
		}

		// Set up progress information
		$this->iohandler->set_task_count(
			$this->install_config->get_task_progress_count()
		);

		try
		{
			$iterator = $this->installer_modules->getIterator();

			if ($module_index < $iterator->count())
			{
				$iterator->seek($module_index);
			}
			else
			{
				$iterator->seek($module_index - 1);
				$iterator->next();
			}

			while ($iterator->valid())
			{
				$module	= $iterator->current();
				$name	= $iterator->key();

				// Check if module should be executed
				if (!$module->is_essential() && !$module->check_requirements())
				{
					$this->install_config->set_finished_navigation_stage($module->get_navigation_stage_path());
					$this->iohandler->set_finished_stage_menu($module->get_navigation_stage_path());

					$this->iohandler->add_log_message(array(
						'SKIP_MODULE',
						$name,
					));
					$this->install_config->increment_current_task_progress($this->module_step_count[$name]);
				}
				else
				{
					// Set the correct stage in the navigation bar
					$this->install_config->set_active_navigation_stage($module->get_navigation_stage_path());
					$this->iohandler->set_active_stage_menu($module->get_navigation_stage_path());

					$this->iohandler->send_response();

					$module->run();

					$this->install_config->set_finished_navigation_stage($module->get_navigation_stage_path());
					$this->iohandler->set_finished_stage_menu($module->get_navigation_stage_path());
				}

				$module_index++;
				$iterator->next();

				// Save progress
				$this->install_config->set_active_module($name, $module_index);

				if ($iterator->valid() && ($this->install_config->get_time_remaining() <= 0 || $this->install_config->get_memory_remaining() <= 0))
				{
					throw new resource_limit_reached_exception();
				}
			}

			// Installation finished
			$install_finished = true;

			if ($this->iohandler instanceof cli_iohandler)
			{
				$this->iohandler->add_success_message('INSTALLER_FINISHED');
			}
			else
			{
				// Start session if not installing and get user object
				// to allow redirecting to ACP
				$user = $this->container_factory->get('user');
				if (!isset($module) || !($module instanceof \phpbb\install\module\install_finish\module))
				{
					$auth = $this->container_factory->get('auth');

					$user->session_begin();
					$auth->acl($user->data);
					$user->setup();
				}

				$phpbb_root_path = $this->container_factory->get_parameter('core.root_path');

				$acp_url = append_sid($phpbb_root_path . 'adm/index.php', 'i=acp_help_phpbb&mode=help_phpbb', true, $user->session_id);
				$this->iohandler->add_success_message('INSTALLER_FINISHED', array(
					'ACP_LINK',
					$acp_url,
				));
			}
		}
		catch (user_interaction_required_exception $e)
		{
			$this->iohandler->send_response(true);
		}
		catch (resource_limit_reached_exception $e)
		{
			$send_refresh = true;
		}
		catch (jump_to_restart_point_exception $e)
		{
			$this->install_config->jump_to_restart_point($e->get_restart_point_name());
			$send_refresh = true;
		}
		catch (\Exception $e)
		{
			$this->iohandler->add_error_message($e->getMessage());
			$this->iohandler->send_response(true);
			$fail_cleanup = true;
		}

		if ($this->iohandler instanceof ajax_iohandler)
		{
			$this->iohandler->release_lock();
		}

		if ($install_finished)
		{
			// Send install finished message
			$this->iohandler->set_progress('INSTALLER_FINISHED', $this->install_config->get_task_progress_count());
			$this->iohandler->send_response(true);
		}
		else if ($send_refresh)
		{
			$this->iohandler->request_refresh();
			$this->iohandler->send_response(true);
		}

		// Save install progress
		try
		{
			if ($install_finished || $fail_cleanup)
			{
				$this->install_config->clean_up_config_file();
				$this->cache->purge();

				try
				{
					/** @var driver_interface $cache */
					$cache = $this->container_factory->get('cache.driver');
					$cache->purge();
				}
				catch (cannot_build_container_exception $e)
				{
					// Do not do anything, this just means there is no config.php yet
				}
			}
			else
			{
				$this->install_config->save_config();
			}
		}
		catch (installer_config_not_writable_exception $e)
		{
			// It is allowed to fail this test during requirements testing
			$progress_data = $this->install_config->get_progress_data();

			if ($progress_data['last_task_module_name'] !== 'installer.module.requirements_install')
			{
				$this->iohandler->add_error_message('INSTALLER_CONFIG_NOT_WRITABLE');
			}
		}
	}

	/**
	 * Recover install progress
	 *
	 * @return string	Index of the next installer module to execute
	 */
	protected function recover_progress()
	{
		$progress_array = $this->install_config->get_progress_data();
		return $progress_array['last_task_module_index'];
	}
}
