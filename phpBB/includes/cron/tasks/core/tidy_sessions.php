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
* Tidy sessions cron task.
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
		global $user;
		$user->session_gc();
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*/
	public function should_run()
	{
		global $config;
		return $config['session_last_gc'] < time() - $config['session_gc'];
	}
}
