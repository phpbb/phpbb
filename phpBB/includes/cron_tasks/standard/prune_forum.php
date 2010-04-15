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
	/**
	* Constructor.
	*
	* If $forum_data is given, it is assumed to contain necessary information
	* about a single forum that is to be pruned.
	*
	* If $forum_data is not given, forum id will be retrieved via request_var
	* and a database query will be performed to load the necessary information
	* about the forum.
	*/
	public function __construct($forum_data=null)
	{
		global $db;
		if ($forum_data)
		{
			$this->forum_data = $forum_data;
		}
		else
		{
			$forum_id = request_var('f', 0);

			$sql = 'SELECT forum_id, prune_next, enable_prune, prune_days, prune_viewed, forum_flags, prune_freq
				FROM ' . FORUMS_TABLE . "
				WHERE forum_id = $forum_id";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				// FIXME what to do?
				break;
			}

			$this->forum_data = $row;
		}
	}

	/**
	* Runs this cron task.
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx;
		include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

		if ($this->forum_data['prune_days'])
		{
			auto_prune($this->forum_data['forum_id'], 'posted', $this->forum_data['forum_flags'], $this->forum_data['prune_days'], $this->forum_data['prune_freq']);
		}

		if ($this->forum_data['prune_viewed'])
		{
			auto_prune($this->forum_data['forum_id'], 'viewed', $this->forum_data['forum_flags'], $this->forum_data['prune_viewed'], $this->forum_data['prune_freq']);
		}
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
