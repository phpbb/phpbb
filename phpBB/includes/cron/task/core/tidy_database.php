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
* Tidy database cron task.
*
* @package phpBB3
*/
class phpbb_cron_task_core_tidy_database extends phpbb_cron_task_base
{
	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx;
		if (!function_exists('tidy_database'))
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}
		tidy_database();
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		global $config;
		return $config['database_last_gc'] < time() - $config['database_gc'];
	}
}
