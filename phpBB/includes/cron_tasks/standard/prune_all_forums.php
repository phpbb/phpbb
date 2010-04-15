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
* Prune all forums cron task.
*
* It is intended to be invoked from system cron.
* This task will find all forums for which pruning is enabled, and will
* prune all forums as necessary.
*
* @package phpBB3
*/
class prune_all_forums_cron_task extends cron_task_base
{
	/**
	* Runs this cron task.
	*/
	public function run()
	{
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*/
	public function is_runnable()
	{
		global $config;
		return !!$config['use_system_cron'];
	}
}
