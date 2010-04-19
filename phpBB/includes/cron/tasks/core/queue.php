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

if (!class_exists('cron_task_base'))
{
	include($phpbb_root_path . 'includes/cron/cron_task_base.' . $phpEx);
}

/**
* Queue cron task. Sends email and jabber messages queued by other scripts.
*
* @package phpBB3
*/
class cron_task_core_queue extends cron_task_base
{
	/**
	* Runs this cron task.
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
	*/
	public function is_runnable()
	{
		global $phpbb_root_path, $phpEx;
		return file_exists($phpbb_root_path . 'cache/queue.' . $phpEx);
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*/
	public function should_run()
	{
		global $config;
		return $config['last_queue_run'] < time() - $config['queue_interval_config'];
	}

	/**
	* Returns whether this cron task can be run in shutdown function.
	*/
	public function is_shutdown_function_safe()
	{
		global $config;
		// A user reported using the mail() function while using shutdown does not work. We do not want to risk that.
		return !$config['smtp_delivery'];
	}
}
