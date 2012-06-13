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
		global $phpbb_root_path, $phpEx, $db, $config;

		$prune_revision_ids = array();

		// This allows us to split up the IDs so that we don't try to delete a ton all at once
		$iteration = 0;
		// This is how many IDs to have per iteration
		$ids_per_iteration = 20;

		$sql = 'SELECT revision_id
			FROM ' . POST_REVISIONS_TABLE . '
			WHERE revision_time < ' . (time() - $config['post_revisions_max_age']);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if (sizeof($prune_revision_ids[$iteration]) == $ids_per_iteration)
			{
				$iteration++;
			}

			$prune_revision_ids[$iteration][] = $row['revision_id'];
		}
		$db->sql_freeresult($result);

		// Finally, if we have any revisions that meet the criteria, we delete them
		if (!empty($prune_revision_ids))
		{
			for($i = 0; $i < $iteration; $i++)
			{
				$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
					WHERE ' . $db->sql_in_set('revision_id', $prune_revision_ids[$i]);
				$result = $db->sql_query($sql);
			}
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
