<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Cron task interface
* @package phpBB3
*/
interface phpbb_cron_task
{
	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run();

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* For example, a cron task that prunes forums can only run when
	* forum pruning is enabled.
	*
	* @return bool
	*/
	public function is_runnable();

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run();

	/**
	* Returns whether this cron task can be run in shutdown function.
	*
	* @return bool
	*/
	public function is_shutdown_function_safe();
}
