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

use phpbb\di\ordered_service_collection;
use phpbb\install\exception\installer_config_not_writable_exception;
use phpbb\install\exception\invalid_service_name_exception;
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;

class installer
{
	/**
	 * @var config
	 */
	protected $install_config;

	/**
	 * @var array
	 */
	protected $installer_modules;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * Stores the number of steps that a given module has
	 *
	 * @var array
	 */
	protected $module_step_count;

	/**
	 * Constructor
	 *
	 * @param config				$config		Installer config handler
	 */
	public function __construct(config $config)
	{
		$this->install_config		= $config;
		$this->installer_modules	= null;
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
	 * Run phpBB installer
	 */
	public function run()
	{
		// Load install progress
		$this->install_config->load_config();

		// Recover install progress
		$module_name = $this->recover_progress();
		$module_found = false;

		// Variable used to check if the install process have been finished
		$install_finished = false;

		// Flag used by exception handling, whether or not we need to flush output buffer once again
		$flush_messages = false;

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
			foreach ($this->installer_modules as $name => $module)
			{
				// Skip forward until the current task is reached
				if (!$module_found)
				{
					if ($module_name === $name || empty($module_name))
					{
						$module_found = true;
					}
					else
					{
						continue;
					}
				}

				// Log progress
				$this->install_config->set_active_module($name);

				// Run until there are available resources
				if ($this->install_config->get_time_remaining() <= 0 && $this->install_config->get_memory_remaining() <= 0)
				{
					throw new resource_limit_reached_exception();
				}

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
					continue;
				}

				// Set the correct stage in the navigation bar
				$this->install_config->set_active_navigation_stage($module->get_navigation_stage_path());
				$this->iohandler->set_active_stage_menu($module->get_navigation_stage_path());

				$module->run();

				$this->install_config->set_finished_navigation_stage($module->get_navigation_stage_path());
				$this->iohandler->set_finished_stage_menu($module->get_navigation_stage_path());
			}

			// Installation finished
			$install_finished = true;
		}
		catch (user_interaction_required_exception $e)
		{
			// Do nothing
		}
		catch (resource_limit_reached_exception $e)
		{
			// Do nothing
		}
		catch (invalid_service_name_exception $e)
		{
			$params = $e->get_parameters();

			if (!empty($params))
			{
				array_unshift($params, $e->getMessage());
			}
			else
			{
				$params = $e->getMessage();
			}

			$this->iohandler->add_error_message($params);
			$flush_messages = true;
		}

		if ($install_finished)
		{
			// Send install finished message
			$this->iohandler->set_progress('INSTALLER_FINISHED', $this->install_config->get_task_progress_count());
		}
		else
		{
			$this->iohandler->request_refresh();
		}

		if ($flush_messages)
		{
			$this->iohandler->send_response();
		}

		// Save install progress
		try
		{
			$this->install_config->save_config();
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
		return $progress_array['last_task_module_name'];
	}
}
