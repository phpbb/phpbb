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
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;

/**
 * Base class for installer module
 */
abstract class module_base implements module_interface
{
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
	 * @var array
	 */
	protected $task_step_count;

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
	 * @param config				$config
	 * @param iohandler_interface	$iohandler
	 */
	public function setup(config $config, iohandler_interface $iohandler)
	{
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
		$task_index	= $this->recover_progress();
		$iterator	= $this->task_collection->getIterator();

		if ($task_index < $iterator->count())
		{
			$iterator->seek($task_index);
		}
		else
		{
			$this->install_config->set_finished_task(0);
			return;
		}

		while ($iterator->valid())
		{
			$task = $iterator->current();
			$name = $iterator->key();

			// Check if we can run the task
			if (!$task->is_essential() && !$task->check_requirements())
			{
				$this->iohandler->add_log_message(array(
					'SKIP_TASK',
					$name,
				));

				$this->install_config->increment_current_task_progress($this->task_step_count[$name]);
			}
			else
			{
				// Send progress information
				if ($this->allow_progress_bar)
				{
					$this->iohandler->set_progress(
						$task->get_task_lang_name(),
						$this->install_config->get_current_task_progress()
					);

					$this->iohandler->send_response();
				}

				$task->run();

				if ($this->allow_progress_bar)
				{
					// Only increment progress by one, as if a task has more than one steps
					// then that should be incremented in the task itself
					$this->install_config->increment_current_task_progress();
				}
			}

			$task_index++;
			$this->install_config->set_finished_task($task_index);
			$iterator->next();

			// Send progress information
			if ($this->allow_progress_bar)
			{
				$this->iohandler->set_progress(
					$task->get_task_lang_name(),
					$this->install_config->get_current_task_progress()
				);
			}

			$this->iohandler->send_response();

			// Stop execution if resource limit is reached
			if ($iterator->valid() && ($this->install_config->get_time_remaining() <= 0 || $this->install_config->get_memory_remaining() <= 0))
			{
				throw new resource_limit_reached_exception();
			}
		}

		// Module finished, so clear task progress
		$this->install_config->set_finished_task(0);
	}

	/**
	 * Returns the next task's name
	 *
	 * @return string	Index of the array element of the next task
	 */
	protected function recover_progress()
	{
		$progress_array = $this->install_config->get_progress_data();
		return $progress_array['last_task_index'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_step_count()
	{
		$task_step_count = 0;
		$task_class_names = $this->task_collection->get_service_classes();

		foreach ($task_class_names as $name => $task_class)
		{
			$step_count = $task_class::get_step_count();
			$task_step_count += $step_count;
			$this->task_step_count[$name] = $step_count;
		}

		return $task_step_count;
	}
}
