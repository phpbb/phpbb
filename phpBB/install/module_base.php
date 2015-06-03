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

/**
 * Base class for installer module
 */
abstract class module_base implements module_interface
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
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
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
	 * Installer module constructor
	 *
	 * @param array	$tasks		array of installer tasks for installer module
	 * @param bool	$essential	flag that indicates if module is essential or not
	 */
	public function __construct(array $tasks, $essential = true)
	{
		$this->task_collection	= $tasks;
		$this->is_essential		= $essential;
	}

	/**
	 * Dependency getter
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface	$container
	 * @param \phpbb\install\helper\config								$config
	 * @param \phpbb\install\helper\iohandler\iohandler_interface		$iohandler
	 */
	public function setup(\Symfony\Component\DependencyInjection\ContainerInterface $container, \phpbb\install\helper\config $config, \phpbb\install\helper\iohandler\iohandler_interface $iohandler)
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
			/** @var \phpbb\install\task_interface $task */
			$task = $this->container->get($this->task_collection[$task_index]);

			// Send progress information
			$this->iohandler->set_progress(
				$task->get_task_lang_name(),
				$this->install_config->get_current_task_progress()
			);

			// Iterate to the next task
			$task_index++;
			$this->install_config->increment_current_task_progress();

			// Check if we can run the task
			if (!$task->is_essential() && !$task->check_requirements())
			{
				continue;
			}

			$task->run();

			// Send progress info
			$this->iohandler->set_progress(
				$task->get_task_lang_name(),
				$this->install_config->get_current_task_progress()
			);

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
			$task_service_name_parts = explode('.', $task_service_name);

			if ($task_service_name_parts[0] !== 'installer')
			{
				// @todo throw an exception
			}

			$class_name = '\\phpbb\\install\\module\\' . $task_service_name_parts[1] . '\\task\\' . $task_service_name_parts[2];
			if (!class_exists($class_name))
			{
				// @todo throw an exception
			}

			$step_count += $class_name::get_step_count();
		}

		return $step_count;
	}
}
