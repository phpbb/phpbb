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
* Prune one forum cron task.
*
* It is intended to be used when cron is invoked via web.
* This task can decide whether it should be run using data obtained by viewforum
* code, without making additional database queries.
*
* @package phpBB3
*/
class prune_forum_cron_task extends cron_task_base implements parametrized_cron_task
{
	public function __construct($forum_data)
	{
		$this->forum_data = $forum_data;
	}

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
		return !$config['use_system_cron'];
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*/
	public function should_run()
	{
		return $this->forum_data['enable_prune'] && $this->forum_data['prune_next'] < time();
	}

	/**
	* Returns parameters of this cron task as a query string.
	*/
	public function get_url_query_string()
	{
		return 'f=' . $this->forum_data['forum_id'];
	}
}
