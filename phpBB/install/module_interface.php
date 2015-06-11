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
 * Interface for installer modules
 *
 * An installer module is a task collection which executes installer tasks.
 */
interface module_interface
{
	/**
	 * Checks if the execution of the module is essential to install phpBB or it can be skipped
	 *
	 * Note: Please note that all the non-essential modules have to implement check_requirements()
	 * method.
	 *
	 * @return	bool	true if the module is essential, false otherwise
	 */
	public function is_essential();

	/**
	 * Checks requirements for the tasks
	 *
	 * Note: Only need to be implemented for non-essential tasks, as essential tasks
	 * requirements should be checked in the requirements install module.
	 *
	 * @return bool	true if the task's requirements are met
	 */
	public function check_requirements();

	/**
	 * Executes the task
	 *
	 * @return	null
	 */
	public function run();

	/**
	 * Returns the number of tasks in the module
	 *
	 * @return int
	 */
	public function get_step_count();

	/**
	 * Returns an array to the correct navigation stage
	 *
	 * @return array
	 */
	public function get_navigation_stage_path();
}
