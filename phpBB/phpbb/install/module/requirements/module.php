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

use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\exception\user_interaction_required_exception;

class module extends \phpbb\install\module_base
{
	public function run()
	{
		$tests_passed = true;

		// Recover install progress
		$task_name = $this->recover_progress();
		$task_found = false;

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
			if (!$task_found)
			{
				if ($name === $task_name || empty($task_name))
				{
					$task_found = true;

					if ($name === $task_name)
					{
						continue;
					}
				}
				else
				{
					continue;
				}
			}

			// Check if we can run the task
			if (!$task->is_essential() && !$task->check_requirements())
			{
				continue;
			}

			if ($this->allow_progress_bar)
			{
				$this->install_config->increment_current_task_progress();
			}

			$test_result = $task->run();
			$tests_passed = ($tests_passed) ? $test_result : false;
		}

		// Module finished, so clear task progress
		$this->install_config->set_finished_task('');

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
