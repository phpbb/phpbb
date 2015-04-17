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

class installer
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var array
	 */
	protected $installer_modules;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\config	$config		Installer config handler
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface	$container	Dependency injection container
	 */
	public function __construct(\phpbb\install\helper\config $config, \Symfony\Component\DependencyInjection\ContainerInterface $container)
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
	 * Run phpBB installer
	 *
	 * @return null
	 */
	public function run()
	{
		// Load install progress
		$this->install_config->load_config();

		// Recover install progress
		$module_index = $this->recover_progress();

		$install_finished = false;

		try
		{
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
				/** @var \phpbb\install\module_interface $module */
				$module = $this->container->get($module_service_name);

				$module_index++;

				// Check if module should be executed
				if (!$module->is_essential() && !$module->check_requirements())
				{
					continue;
				}

				$module->run();

				// Clear task progress
				$this->install_config->set_finished_task('', 0);
			}

			if ($install_finished)
			{
				die ("install finished");
			}
			else
			{
				die ("install memory or time limit reached");
			}
		}
		catch (\phpbb\install\exception\user_interaction_required_exception $e)
		{
			// @todo handle exception
		}

		// Save install progress
		$this->install_config->save_config();
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
