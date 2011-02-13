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
* Cron task base class. Provides sensible defaults for cron tasks
* and partially implements cron task interface, making writing cron tasks easier.
*
* At a minimum, subclasses must override the run() method.
*
* Cron tasks need not inherit from this base class. If desired,
* they may implement cron task interface directly.
*
* @package phpBB3
*/
abstract class phpbb_cron_task_base implements phpbb_cron_task
{
	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* For example, a cron task that prunes forums can only run when
	* forum pruning is enabled.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return true;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		return true;
	}

	/**
	* Returns whether this cron task can be run in shutdown function.
	*
	* By the time shutdown sequence invokes a particular piece of code,
	* resources that that code requires may already be released.
	* If so, a particular cron task may be marked shutdown function-
	* unsafe, and it will be executed in normal program flow.
	*
	* Generally speaking cron tasks should start off as shutdown function-
	* safe, and only be marked shutdown function-unsafe if a problem
	* is discovered.
	*
	* @return bool		Whether the cron task is shutdown function-safe.
	*/
	public function is_shutdown_function_safe()
	{
		return true;
	}
}
