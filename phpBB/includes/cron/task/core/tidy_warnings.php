<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* Tidy warnings cron task.
*
* Will only run when warnings are configured to expire.
*
* @package phpBB3
*/
class phpbb_cron_task_core_tidy_warnings extends phpbb_cron_task_base
{
	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx;
		if (!function_exists('tidy_warnings'))
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}
		tidy_warnings();
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* If warnings are set to never expire, this cron task will not run.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		global $config;
		return (bool) $config['warnings_expire_days'];
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between warnings tidying is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		global $config;
		return $config['warnings_last_gc'] < time() - $config['warnings_gc'];
	}
}
