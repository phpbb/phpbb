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

include_once($phpbb_root_path . 'includes/cron/cron_task_base.' . $phpEx);

/**
* Tidy search cron task.
*
* Will only run when the currently selected search backend supports tidying.
*
* @package phpBB3
*/
class cron_task_core_tidy_sessions extends cron_task_base
{
	/**
	* Runs this cron task.
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx, $config, $error;

		// Select the search method
		$search_type = basename($config['search_type']);

		include_once("{$phpbb_root_path}includes/search/$search_type.$phpEx");

		// We do some additional checks in the module to ensure it can actually be utilised
		$error = false;
		$search = new $search_type($error);

		if (!$error)
		{
			$search->tidy();
		}
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*/
	public function is_runnable()
	{
		global $phpbb_root_path, $phpEx, $config;

		// Select the search method
		$search_type = basename($config['search_type']);

		return file_exists($phpbb_root_path . 'includes/search/' . $search_type . '.' . $phpEx);
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*/
	public function should_run()
	{
		global $config;
		return $config['search_last_gc'] < time() - $config['search_gc'];
	}
}
