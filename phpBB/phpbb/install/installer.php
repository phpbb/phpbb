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

use phpbb\install\exception\installer_config_not_writable_exception;
use phpbb\install\exception\invalid_service_name_exception;
use phpbb\install\exception\module_not_found_exception;
use phpbb\install\exception\task_not_found_exception;
use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class installer
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

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
	 * @param ContainerInterface	$container	Dependency injection container
	 */
	public function __construct(config $config, ContainerInterface $container)
	{
		$this->install_config		= $config;
		$this->container			= $container;
		$this->installer_modules	= array();
	}

	/**
	 * Sets modules to execute
	 *
	 * Note: The installer will run modules in the order they are set in
	 * the array.
	 *
	 * @param array	$modules	Array of module service names
	 */
	public function set_modules($modules)
	{
		$modules = (array) $modules;

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
		$module_index = $this->recover_progress();

		// Variable used to check if the install process have been finished
		$install_finished = false;

		// Flag used by exception handling, whether or not we need to flush output buffer once again
		$flush_messages = false;

		// We are installing something, so the introduction stage can go now...
		$this->install_config->set_finished_navigation_stage(array('install', 0, 'introduction'));
		$this->iohandler->set_finished_stage_menu(array('install', 0, 'introduction'));

		try
		{
			if ($this->install_config->get_task_progress_count() === 0)
			{
				// Count all tasks in the current installer modules
				$step_count = 0;
				foreach ($this->installer_modules as $index => $name)
				{
					try
					{
						/** @var \phpbb\install\module_interface $module */
						$module = $this->container->get($name);
					}
					catch (InvalidArgumentException $e)
					{
						throw new module_not_found_exception($name);
					}

					$module_step_count = $module->get_step_count();
					$step_count += $module_step_count;
					$this->module_step_count[$index] = $module_step_count;
				}

				// Set task count
				$this->install_config->set_task_progress_count($step_count);
			}

			// Set up progress information
			$this->iohandler->set_task_count(
				$this->install_config->get_task_progress_count()
			);

			// Run until there are available resources
			while ($this->install_config->get_time_remaining() > 0 && $this->install_config->get_memory_remaining() > 0)
			{
				// Check if module exists, if not the install is completed
				if (!isset($this->installer_modules[$module_index]))
				{
					$install_finished = true;
					break;
				}

				// Log progress
				$module_service_name = $this->installer_modules[$module_index];
				$this->install_config->set_active_module($module_service_name, $module_index);

				// Get module from container
				try
				{
					/** @var \phpbb\install\module_interface $module */
					$module = $this->container->get($module_service_name);
				}
				catch (InvalidArgumentException $e)
				{
					throw new module_not_found_exception($module_service_name);
				}

				$module_index++;

				// Check if module should be executed
				if (!$module->is_essential() && !$module->check_requirements())
				{
					$this->install_config->set_finished_navigation_stage($module->get_navigation_stage_path());
					$this->iohandler->set_finished_stage_menu($module->get_navigation_stage_path());

					$this->iohandler->add_log_message(array(
						'SKIP_MODULE',
						$module_service_name,
					));
					$this->install_config->increment_current_task_progress($this->module_step_count[$module_index - 1]);
					continue;
				}

				// Set the correct stage in the navigation bar
				$this->install_config->set_active_navigation_stage($module->get_navigation_stage_path());
				$this->iohandler->set_active_stage_menu($module->get_navigation_stage_path());

				$module->run();

				$this->install_config->set_finished_navigation_stage($module->get_navigation_stage_path());
				$this->iohandler->set_finished_stage_menu($module->get_navigation_stage_path());

				// Clear task progress
				$this->install_config->set_finished_task('', 0);
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
		}
		catch (user_interaction_required_exception $e)
		{
			// Do nothing
		}
		catch (module_not_found_exception $e)
		{
			$this->iohandler->add_error_message('MODULE_NOT_FOUND', array(
				'MODULE_NOT_FOUND_DESCRIPTION',
				$e->get_module_service_name(),
			));
			$flush_messages = true;
		}
		catch (task_not_found_exception $e)
		{
			$this->iohandler->add_error_message('TASK_NOT_FOUND', array(
				'TASK_NOT_FOUND_DESCRIPTION',
				$e->get_task_service_name(),
			));
			$flush_messages = true;
		}
		catch (invalid_service_name_exception $e)
		{
			if ($e->has_params())
			{
				$msg = $e->get_params();
				array_unshift($msg, $e->get_error());
			}
			else
			{
				$msg = $e->get_error();
			}

			$this->iohandler->add_error_message($msg);
			$flush_messages = true;
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
	 * @return int	Index of the next installer module to execute
	 */
	protected function recover_progress()
	{
		$progress_array = $this->install_config->get_progress_data();
		$module_service = $progress_array['last_task_module_name'];
		$module_index = $progress_array['last_task_module_index'];

		if ($this->installer_modules[$module_index] === $module_service)
		{
			return $module_index;
		}

		return 0;
	}
}
