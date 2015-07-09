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
use phpbb\install\exception\invalid_service_name_exception;
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Base class for installer module
 */
abstract class module_base implements module_interface
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
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var bool
	 */
	protected $is_essential;

	/**
	 * Array of tasks for installer module
	 *
	 * @var ordered_service_collection
	 */
	protected $task_collection;

	/**
	 * @var bool
	 */
	protected $allow_progress_bar;

	/**
	 * Installer module constructor
	 *
	 * @param ordered_service_collection	$tasks				array of installer tasks for installer module
	 * @param bool							$essential			flag indicating whether the module is essential or not
	 * @param bool							$allow_progress_bar	flag indicating whether or not to send progress information from within the module
	 */
	public function __construct(ordered_service_collection $tasks, $essential = true, $allow_progress_bar = true)
	{
		$this->task_collection		= $tasks;
		$this->is_essential			= $essential;
		$this->allow_progress_bar	= $allow_progress_bar;
	}

	/**
	 * Dependency getter
	 *
	 * @param ContainerInterface	$container
	 * @param config				$config
	 * @param iohandler_interface	$iohandler
	 */
	public function setup(ContainerInterface $container, config $config, iohandler_interface $iohandler)
	{
		$this->container		= $container;
		$this->install_config	= $config;
		$this->iohandler		= $iohandler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_essential()
	{
		return $this->is_essential;
	}

	/**
	 * {@inheritdoc}
	 *
	 * Overwrite this method if your task is non-essential!
	 */
	public function check_requirements()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Recover install progress
		$task_name = $this->recover_progress();
		$name_found = false;

		/**
		 * @var string							$name	ID of the service
		 * @var \phpbb\install\task_interface	$task	Task object
		 */
		foreach ($this->task_collection as $name => $task)
		{
			// Run until there are available resources
			if ($this->install_config->get_time_remaining() <= 0 && $this->install_config->get_memory_remaining() <= 0)
			{
				throw new resource_limit_reached_exception();
			}

			// Skip forward until the next task is reached
			if (!empty($task_name) && !$name_found)
			{
				if ($name === $task_name)
				{
					$name_found = true;
				}

				continue;
			}

			// Send progress information
			if ($this->allow_progress_bar)
			{
				$this->iohandler->set_progress(
					$task->get_task_lang_name(),
					$this->install_config->get_current_task_progress()
				);
			}

			// Check if we can run the task
			if (!$task->is_essential() && !$task->check_requirements())
			{
				$this->iohandler->add_log_message(array(
					'SKIP_TASK',
					$name,
				));

				$class_name = $this->get_class_from_service_name($name);
				$this->install_config->increment_current_task_progress($class_name::get_step_count());
				continue;
			}

			if ($this->allow_progress_bar)
			{
				$this->install_config->increment_current_task_progress();
			}

			$task->run();

			// Send progress information
			if ($this->allow_progress_bar)
			{
				$this->iohandler->set_progress(
					$task->get_task_lang_name(),
					$this->install_config->get_current_task_progress()
				);
			}

			$this->iohandler->send_response();

			// Log install progress
			$this->install_config->set_finished_task($name);
		}

		// Module finished, so clear task progress
		$this->install_config->set_finished_task('');
	}

	/**
	 * Returns the next task's index
	 *
	 * @return int	index of the array element of the next task
	 */
	protected function recover_progress()
	{
		$progress_array = $this->install_config->get_progress_data();
		return $progress_array['last_task_name'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_step_count()
	{
		$step_count = 0;

		/** @todo:	Fix this
		foreach ($this->task_collection as $task_service_name)
		{
			$class_name = $this->get_class_from_service_name($task_service_name);
			$step_count += $class_name::get_step_count();
		}
		*/

		return $step_count;
	}

	/**
	 * Returns the name of the class form the service name
	 *
	 * @param string	$task_service_name	Name of the service
	 *
	 * @return string	Name of the class
	 *
	 * @throws invalid_service_name_exception	When the service name does not meet the requirements described in task_interface
	 */
	protected function get_class_from_service_name($task_service_name)
	{
		$task_service_name_parts = explode('.', $task_service_name);

		if ($task_service_name_parts[0] !== 'installer')
		{
			throw new invalid_service_name_exception('TASK_SERVICE_INSTALLER_MISSING');
		}

		$class_name = '\\phpbb\\install\\module\\' . $task_service_name_parts[1] . '\\task\\' . $task_service_name_parts[2];
		if (!class_exists($class_name))
		{
			throw new invalid_service_name_exception('TASK_CLASS_NOT_FOUND', array($task_service_name, $class_name));
		}

		return $class_name;
	}
}
