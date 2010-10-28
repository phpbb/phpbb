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

/**
* Tidy cache cron task.
*
* @package phpBB3
*/
class phpbb_cron_task_core_tidy_cache extends phpbb_cron_task_base
{
	/**
	* Runs this cron task.
	*/
	public function run()
	{
		global $cache;
		$cache->tidy();
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*/
	public function is_runnable()
	{
		global $cache;
		return method_exists($cache, 'tidy');
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*/
	public function should_run()
	{
		global $config;
		return $config['cache_last_gc'] < time() - $config['cache_gc'];
	}
}
