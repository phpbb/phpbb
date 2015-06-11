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

namespace phpbb\install\module\requirements;

use phpbb\install\exception\user_interaction_required_exception;

class module extends \phpbb\install\module_base
{
	public function run()
	{
		$tests_passed = true;

		// Recover install progress
		$task_index = 0;

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

			// Iterate to the next task
			$task_index++;

			// Check if we can run the task
			if (!$task->is_essential() && !$task->check_requirements())
			{
				continue;
			}

			$test_result = $task->run();
			$tests_passed = ($tests_passed) ? $test_result : false;
		}

		// Check if tests have failed
		if (!$tests_passed)
		{
			// If requirements are not met, exit form installer
			// Set up UI for retesting
			$this->iohandler->add_user_form_group('', array(
				'install'	=> array(
					'label'	=> 'RETEST_REQUIREMENTS',
					'type'	=> 'submit',
				),
			));

			// Send the response and quit
			$this->iohandler->send_response();
			throw new user_interaction_required_exception();
		}

		// Log install progress
		$current_task_index = $task_index - 1;
		$this->install_config->set_finished_task($this->task_collection[$current_task_index], $current_task_index);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_step_count()
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_navigation_stage_path()
	{
		return array('install', 0, 'requirements');
	}
}
