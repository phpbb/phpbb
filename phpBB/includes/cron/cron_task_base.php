<?php
/**
*
* @package phpBB3
* @version $Id$
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

include_once($phpbb_root_path . 'includes/cron/cron_task.' . $phpEx);

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
abstract class cron_task_base implements cron_task
{
	/**
	* Runs this cron task.
	*/
	abstract public function run();

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* For example, a cron task that prunes forums can only run when
	* forum pruning is enabled.
	*/
	public function is_runnable()
	{
		return true;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*/
	public function should_run()
	{
		return true;
	}

	/**
	* Returns whether this cron task can be run in shutdown function.
	*/
	public function is_shutdown_function_safe()
	{
		return true;
	}
}
