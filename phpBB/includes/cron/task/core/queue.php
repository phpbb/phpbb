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
* Queue cron task. Sends email and jabber messages queued by other scripts.
*
* @package phpBB3
*/
class phpbb_cron_task_core_queue extends phpbb_cron_task_base
{
	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx;
		if (!class_exists('queue'))
		{
			include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		}
		$queue = new queue();
		$queue->process();
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* Queue task is only run if the email queue (file) exists.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		global $phpbb_root_path, $phpEx;
		return file_exists($phpbb_root_path . 'cache/queue.' . $phpEx);
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between queue runs is specified in board configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		global $config;
		return $config['last_queue_run'] < time() - $config['queue_interval_config'];
	}
}
