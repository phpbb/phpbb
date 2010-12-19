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
* Tidy cache cron task.
*
* @package phpBB3
*/
class phpbb_cron_task_core_tidy_cache extends phpbb_cron_task_base
{
	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		global $cache;
		$cache->tidy();
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* Tidy cache cron task runs if the cache implementation in use
	* supports tidying.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		global $cache;
		return method_exists($cache, 'tidy');
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between cache tidying is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		global $config;
		return $config['cache_last_gc'] < time() - $config['cache_gc'];
	}
}
