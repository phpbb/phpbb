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
 * Interface for installer tasks
 */
interface task_interface
{
	/**
	 * Returns the number of steps the task contains
	 *
	 * This is a helper method to provide a better progress bar for the front-end.
	 *
	 * @return int	The number of steps that the task contains
	 */
	static public function get_step_count();

	/**
	 * Checks if the task is essential to install phpBB or it can be skipped
	 *
	 * Note: Please note that all the non-essential modules have to implement check_requirements()
	 * method.
	 *
	 * @return	bool	true if the task is essential, false otherwise
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
	 */
	public function run();

	/**
	 * Returns the language key of the name of the task
	 *
	 * @return string
	 */
	public function get_task_lang_name();
}
