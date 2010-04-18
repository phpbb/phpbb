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
* Prune one forum cron task.
*
* It is intended to be used when cron is invoked via web.
* This task can decide whether it should be run using data obtained by viewforum
* code, without making additional database queries.
*
* @package phpBB3
*/
class cron_task_core_prune_forum extends cron_task_base implements parametrized_cron_task
{
	private $forum_data;

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
			$this->forum_data = null;
		}
	}

	/**
	* Runs this cron task.
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx;
		if (!function_exists('auto_prune'))
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}

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
		return !$config['use_system_cron'] && $this->forum_data;
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
	* Returns parameters of this cron task as an array.
	*
	* The array has one key, f, whose value is id of the forum to be pruned.
	*/
	public function get_parameters()
	{
		return array('f' => $this->forum_data['forum_id']);
	}

	/**
	* Parses parameters found in $params, which is an array.
	*
	* $params may contain user input and is not trusted.
	*
	* $params is expected to have a key f whose value is id of the forum to be pruned.
	*/
	public function parse_parameters($params)
	{
		global $db;

		$this->forum_data = null;
		if (isset($params['f']))
		{
			$forum_id = int($params['f']);

			$sql = 'SELECT forum_id, prune_next, enable_prune, prune_days, prune_viewed, forum_flags, prune_freq
				FROM ' . FORUMS_TABLE . "
				WHERE forum_id = $forum_id";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row)
			{
				$this->forum_data = $row;
			}
		}
	}
}
