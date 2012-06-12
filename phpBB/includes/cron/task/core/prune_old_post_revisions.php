<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
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
* Prune old post revisions
*
* This task will find all posts that have old revisions
* remove the revisions that are older than the maximum revision age.
*
* @package phpBB3
*/
class phpbb_cron_task_core_prune_old_post_revisions extends phpbb_cron_task_base
{
	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx, $db;

		$prune_revision_ids = array();

		if ($config['post_revisions_max_age'])
		{
			$sql = 'SELECT revision_id
				FROM ' . POST_REVISIONS_TABLE . '
				WHERE revision_time < ' . (time() - $config['post_revisions_max_age']);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$prune_revision_ids[] = $row['revision_id'];
			}
			$db->sql_freeresult($result);
		}

		// Finally, if we have any revisions that meet the criteria, we delete them
		if (!empty($prune_revision_ids))
		{
			$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
				WHERE ' . $db->sql_in_set('revision_id', $prune_revision_ids);
			$result = $db->sql_query($sql);
		}
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		global $config;
		return $config['track_post_revisions'] && $config['post_revisions_max_age'];
	}
}
