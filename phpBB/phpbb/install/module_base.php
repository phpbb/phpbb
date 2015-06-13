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

use phpbb\install\exception\invalid_service_name_exception;
use phpbb\install\exception\task_not_found_exception;
use phpbb\install\helper\iohandler\iohandler_interface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use phpbb\install\helper\config;

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
	 * @var array
	 */
	protected $task_collection;

	/**
	 * @var bool
	 */
	protected $allow_progress_bar;

	/**
	 * Installer module constructor
	 *
	 * @param array	$tasks				array of installer tasks for installer module
	 * @param bool	$essential			flag indicating whether the module is essential or not
	 * @param bool	$allow_progress_bar	flag indicating whether or not to send progress information from within the module
	 */
	public function __construct(array $tasks, $essential = true, $allow_progress_bar = true)
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
		$task_index = $this->recover_progress();

		// Run until there are available resources
		while ($this->install_config->get_time_remaining() > 0 && $this->install_config->get_memory_remaining() > 0)
		{
			// Check if task exists
			if (!isset($this->task_collection[$task_index]))
			{
				break;
			}

			// Recover task to be executed
			try
			{
				/** @var \phpbb\install\task_interface $task */
				$task = $this->container->get($this->task_collection[$task_index]);
			}
			catch (InvalidArgumentException $e)
			{
				throw new task_not_found_exception($this->task_collection[$task_index]);
			}

			// Send progress information
			if ($this->allow_progress_bar)
			{
				$this->iohandler->set_progress(
					$task->get_task_lang_name(),
					$this->install_config->get_current_task_progress()
				);
			}

			// Iterate to the next task
			$task_index++;

			// Check if we can run the task
			if (!$task->is_essential() && !$task->check_requirements())
			{
				$this->iohandler->add_log_message(array(
					'SKIP_TASK',
					$this->task_collection[$task_index],
				));
				$class_name = $this->get_class_from_service_name($this->task_collection[$task_index - 1]);
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
			$current_task_index = $task_index - 1;
			$this->install_config->set_finished_task($this->task_collection[$current_task_index], $current_task_index);
		}
	}

	/**
	 * Returns the next task's index
	 *
	 * @return int	index of the array element of the next task
	 */
	protected function recover_progress()
	{
		$progress_array = $this->install_config->get_progress_data();
		$last_finished_task_name = $progress_array['last_task_name'];
		$last_finished_task_index = $progress_array['last_task_index'];

		// Check if the data is relevant to this module
		if (isset($this->task_collection[$last_finished_task_index]))
		{
			if ($this->task_collection[$last_finished_task_index] === $last_finished_task_name)
			{
				// Return the task index of the next task
				return $last_finished_task_index + 1;
			}
		}

		// As of now if the progress has not been resolved we assume that it is because
		// the task progress belongs to the previous module,
		// so just default to the first task
		// @todo make module aware of it's service name that way this can be improved
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_step_count()
	{
		$step_count = 0;

		foreach ($this->task_collection as $task_service_name)
		{
			$class_name = $this->get_class_from_service_name($task_service_name);
			$step_count += $class_name::get_step_count();
		}

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
