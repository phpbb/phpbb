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

namespace phpbb\install\module\obtain_data;

class module extends \phpbb\install\module_base
{
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

			// Iterate to the next task
			$task_index++;

			// Check if we can run the task
			if (!$task->is_essential() && !$task->check_requirements())
			{
				continue;
			}

			$task->run();

			// Log install progress
			$current_task_index = $task_index - 1;
			$this->install_config->set_finished_task($this->task_collection[$current_task_index], $current_task_index);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_count()
	{
		return 0;
	}
}
