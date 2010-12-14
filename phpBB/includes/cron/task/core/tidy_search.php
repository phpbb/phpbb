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
* Tidy search cron task.
*
* Will only run when the currently selected search backend supports tidying.
*
* @package phpBB3
*/
class phpbb_cron_task_core_tidy_search extends phpbb_cron_task_base
{
	/**
	* Runs this cron task.
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx, $config, $error;

		// Select the search method
		$search_type = basename($config['search_type']);

		if (!class_exists($search_type))
		{
			include("{$phpbb_root_path}includes/search/$search_type.$phpEx");
		}

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
